from __future__ import annotations

import json
from datetime import datetime, timezone
from pathlib import Path
from uuid import uuid4

import joblib
import numpy as np
import pandas as pd

from .metrics import evaluate
from .models import make_model, non_negative
from .preprocessing import feature_columns, make_features, prepare_daily_demand

VERSION = "rf-xgb-recursive-v1"
MIN_HISTORY = 42


def _forecast_baseline(values, horizon, seasonal=False):
    values = list(values)
    if not values:
        return np.zeros(horizon)
    if seasonal and len(values) >= 7:
        return np.array([values[-7 + (i % 7)] for i in range(horizon)], dtype=float)
    return np.repeat(values[-1], horizon).astype(float)


def build_feature_row(history, date, price=0.0):
    values = list(map(float, history))
    row = {"day_of_week": date.dayofweek, "day_of_month": date.day, "month": date.month, "is_weekend": int(date.dayofweek >= 5), "promotion": 0.0, "price": float(price)}
    for lag in (1, 7, 14, 28): row[f"lag_{lag}"] = values[-lag] if len(values) >= lag else values[-1]
    for window in (7, 14, 28):
        sample = values[-window:] if len(values) >= window else values
        row[f"rolling_mean_{window}"] = float(np.mean(sample))
        row[f"rolling_std_{window}"] = float(np.std(sample))
    return row


def _recursive_model(model, history, horizon, product_id, future_dates, daily):
    history = list(map(float, history))
    rows = []
    for date in future_dates:
        row = build_feature_row(history, date, float(daily.price.dropna().iloc[-1]) if daily.price.notna().any() else 0.0)
        rows.append([row[c] for c in feature_columns()])
        history.append(float(non_negative(model.predict(pd.DataFrame([row])[feature_columns()]))[0]))
    return np.asarray(history[-horizon:], dtype=float)


def train_dataset(input_path: str | Path, artifacts_dir: str | Path, dataset_type: str = "unknown"):
    raw = pd.read_csv(input_path)
    daily = prepare_daily_demand(raw)
    features = make_features(daily)
    artifact_dir = Path(artifacts_dir)
    artifact_dir.mkdir(parents=True, exist_ok=True)
    products = {}
    evaluations = []
    for product_id, part in features.groupby("product_id"):
        part = part.sort_values("date").dropna(subset=feature_columns())
        full = daily[daily.product_id == product_id].sort_values("date")
        if len(full) < MIN_HISTORY or len(part) < 20:
            products[str(product_id)] = {"status": "insufficient_data", "history_days": int(len(full))}
            continue
        test_size = min(30, max(7, len(part) // 5))
        train, test = part.iloc[:-test_size], part.iloc[-test_size:]
        candidates = {"naive": _forecast_baseline(train.demand, len(test)), "seasonal_naive": _forecast_baseline(train.demand, len(test), True)}
        for name in ("random_forest", "xgboost"):
            model = make_model(name)
            if model is not None:
                model.fit(train[feature_columns()], train.demand)
                candidates[name] = non_negative(model.predict(test[feature_columns()]))
        scores = {name: evaluate(test.demand, pred) for name, pred in candidates.items()}
        selected = min(scores, key=lambda name: scores[name]["wape"])
        model = make_model(selected) if selected not in ("naive", "seasonal_naive") else None
        if model is not None:
            model.fit(part[feature_columns()], part.demand)
        products[str(product_id)] = {"status": "eligible", "history_days": int(len(full)), "model_name": selected, "model": model, "history": full.demand.tolist(), "last_date": str(full.date.max().date()), "price": float(full.price.dropna().iloc[-1]) if full.price.notna().any() else 0.0, "evaluation": scores[selected], "all_evaluations": scores}
        evaluations.append({"product_id": str(product_id), "selected_model": selected, **scores[selected]})
    metadata = {"model_version": VERSION + "-" + uuid4().hex[:8], "trained_at": datetime.now(timezone.utc).isoformat(), "dataset_type": dataset_type, "products": evaluations, "feature_columns": feature_columns(), "horizon_strategy": "recursive one-step; known future promotion/price default to last observed/zero"}
    joblib.dump({"metadata": metadata, "products": products}, artifact_dir / "forecast-model.joblib")
    (artifact_dir / "training-metadata.json").write_text(json.dumps(metadata, indent=2), encoding="utf-8")
    return metadata


def infer(artifact_path: str | Path, product_id: str, horizon: int, data_as_of: str | None = None):
    if horizon not in (7, 30): raise ValueError("Horizon harus 7 atau 30")
    bundle = joblib.load(artifact_path)
    product = bundle["products"].get(str(product_id))
    if not product or product.get("status") != "eligible":
        return {"status": "insufficient_data", "product_id": str(product_id), "horizon": horizon, "predictions": []}
    start = pd.Timestamp(data_as_of or product["last_date"]) + pd.Timedelta(1, unit="D")
    dates = pd.date_range(start, periods=horizon, freq="D")
    if product["model_name"] in ("naive", "seasonal_naive"):
        values = _forecast_baseline(product["history"], horizon, product["model_name"] == "seasonal_naive")
    else:
        values = _recursive_model(product["model"], product["history"], horizon, product_id, dates, pd.DataFrame({"price": [product.get("price", 0.0)]}))
    return {"status": "success", "product_id": str(product_id), "horizon": horizon, "model_name": product["model_name"], "model_version": bundle["metadata"]["model_version"], "data_as_of": str(pd.Timestamp(data_as_of or product["last_date"]).date()), "metrics": product["evaluation"], "predictions": [{"date": str(date.date()), "predicted_quantity": float(max(value, 0.0))} for date, value in zip(dates, values)]}
