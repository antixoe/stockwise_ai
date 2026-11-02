from __future__ import annotations

from pathlib import Path
import joblib
import numpy as np
import pandas as pd
import shap

from .pipeline import build_feature_row
from .preprocessing import feature_columns

EXPLAINER_VERSION = "shap-tree-v1"
FEATURE_LABELS = {
    "day_of_week": "Hari dalam minggu", "day_of_month": "Tanggal dalam bulan", "month": "Bulan",
    "is_weekend": "Akhir pekan", "promotion": "Promosi", "price": "Harga",
    "lag_1": "Demand 1 hari lalu", "lag_7": "Demand 7 hari lalu", "lag_14": "Demand 14 hari lalu", "lag_28": "Demand 28 hari lalu",
    "rolling_mean_7": "Rata-rata demand 7 hari", "rolling_std_7": "Variasi demand 7 hari",
    "rolling_mean_14": "Rata-rata demand 14 hari", "rolling_std_14": "Variasi demand 14 hari",
    "rolling_mean_28": "Rata-rata demand 28 hari", "rolling_std_28": "Variasi demand 28 hari",
}


def _target_row(product, target_date):
    history = list(product["history"])
    last_date = pd.Timestamp(product["last_date"])
    target = pd.Timestamp(target_date)
    if target <= last_date:
        raise ValueError("Tanggal penjelasan harus setelah data_as_of")
    target_row = None
    for date in pd.date_range(last_date + pd.Timedelta(1, unit="D"), target, freq="D"):
        row = build_feature_row(history, date, product.get("price", 0.0))
        target_row = row
        if product["model_name"] not in ("naive", "seasonal_naive"):
            prediction = float(max(0.0, product["model"].predict(pd.DataFrame([row])[feature_columns()])[0]))
        else:
            prediction = history[-1]
        history.append(prediction)
    return target_row


def explain(artifact_path: str | Path, product_id: str, forecast_date: str):
    bundle = joblib.load(artifact_path)
    product = bundle["products"].get(str(product_id))
    if not product or product.get("status") != "eligible":
        return {"status": "insufficient_data", "product_id": str(product_id), "forecast_date": forecast_date}
    if product["model_name"] in ("naive", "seasonal_naive") or product.get("model") is None:
        return {"status": "unsupported_model", "product_id": str(product_id), "forecast_date": forecast_date, "limitation_note": "Model baseline tidak memiliki TreeExplainer."}
    row = _target_row(product, forecast_date)
    frame = pd.DataFrame([row])[feature_columns()]
    model = product["model"]
    prediction = float(model.predict(frame)[0])
    explainer = shap.TreeExplainer(model)
    values = np.asarray(explainer.shap_values(frame)).reshape(-1)
    base = float(np.asarray(explainer.expected_value).reshape(-1)[0])
    reconstructed = base + float(values.sum())
    if not np.isclose(reconstructed, prediction, rtol=1e-4, atol=1e-4):
        raise RuntimeError(f"SHAP reconstruction mismatch: {reconstructed} != {prediction}")
    contributions = [{"feature": name, "label": FEATURE_LABELS.get(name, name), "value": float(frame.iloc[0][name]), "contribution": float(value), "direction": "positive" if value >= 0 else "negative"} for name, value in zip(feature_columns(), values)]
    importance = getattr(model, "feature_importances_", None)
    global_importance = [{"feature": name, "label": FEATURE_LABELS.get(name, name), "importance": float(value)} for name, value in zip(feature_columns(), importance)] if importance is not None else []
    return {"status": "success", "product_id": str(product_id), "forecast_date": str(pd.Timestamp(forecast_date).date()), "predicted_quantity": max(0.0, prediction), "baseline_prediction": base, "reconstructed_prediction": reconstructed, "contributions": contributions, "global_importance": sorted(global_importance, key=lambda item: item["importance"], reverse=True), "model_name": product["model_name"], "model_version": bundle["metadata"]["model_version"], "explainer_name": "shap.TreeExplainer", "explainer_version": EXPLAINER_VERSION, "limitation_note": "SHAP menunjukkan kontribusi fitur terhadap output model pada observasi ini; bukan hubungan sebab-akibat."}
