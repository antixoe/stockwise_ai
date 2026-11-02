from __future__ import annotations

import numpy as np
import pandas as pd

REQUIRED = {"date", "product_id"}
DEMAND_COLUMNS = ("quantity_demanded", "quantity_sold", "quantity")


def _demand_column(df):
    for name in DEMAND_COLUMNS:
        if name in df.columns:
            return name
    raise ValueError("Dataset harus memiliki quantity_demanded, quantity_sold, atau quantity")


def prepare_daily_demand(raw: pd.DataFrame) -> pd.DataFrame:
    """Aggregate to a daily SKU series while retaining missing/stockout signals."""
    missing = REQUIRED - set(raw.columns)
    if missing:
        raise ValueError(f"Kolom wajib hilang: {sorted(missing)}")
    df = raw.copy()
    df["date"] = pd.to_datetime(df["date"], errors="coerce").dt.normalize()
    df["product_id"] = df["product_id"].astype(str)
    df = df.dropna(subset=["date", "product_id"])
    demand_col = _demand_column(df)
    df[demand_col] = pd.to_numeric(df[demand_col], errors="coerce")
    df = df[df[demand_col].notna() & (df[demand_col] >= 0)]
    price = df["price"] if "price" in df.columns else pd.Series(0.0, index=df.index)
    df["price"] = pd.to_numeric(price, errors="coerce").fillna(0.0)
    promotion = df["promotion"] if "promotion" in df.columns else (df["promotion_flag"] if "promotion_flag" in df.columns else pd.Series(0, index=df.index))
    df["promotion"] = pd.to_numeric(promotion, errors="coerce").fillna(0)
    df["stock_available"] = pd.to_numeric(df.get("stock_available", np.nan), errors="coerce")
    df["stockout_censored"] = (df["stock_available"].notna() & (df["stock_available"] <= 0) & (df[demand_col] > 0)).astype(int)
    grouped = df.groupby(["product_id", "date"], as_index=False).agg(
        demand=(demand_col, "sum"), price=("price", "mean"), promotion=("promotion", "max"),
        stock_available=("stock_available", "min"), stockout_censored=("stockout_censored", "max"),
    )
    rows = []
    for product_id, part in grouped.groupby("product_id"):
        idx = pd.date_range(part.date.min(), part.date.max(), freq="D")
        part = part.set_index("date").reindex(idx)
        part["product_id"] = product_id
        part["is_missing_observation"] = part["demand"].isna().astype(int)
        # A missing row is not silently asserted to be a sale of zero. It is
        # represented as zero for a regular time series plus an explicit flag.
        part["demand"] = part["demand"].fillna(0.0)
        part["stockout_censored"] = part["stockout_censored"].fillna(0).astype(int)
        rows.append(part.reset_index(names="date"))
    result = pd.concat(rows, ignore_index=True).sort_values(["product_id", "date"])
    return result[["product_id", "date", "demand", "price", "promotion", "stock_available", "stockout_censored", "is_missing_observation"]]


def make_features(daily: pd.DataFrame) -> pd.DataFrame:
    out = daily.copy().sort_values(["product_id", "date"])
    out["day_of_week"] = out.date.dt.dayofweek
    out["day_of_month"] = out.date.dt.day
    out["month"] = out.date.dt.month
    out["is_weekend"] = (out["day_of_week"] >= 5).astype(int)
    grouped = out.groupby("product_id", group_keys=False)
    for lag in (1, 7, 14, 28):
        out[f"lag_{lag}"] = grouped.demand.shift(lag)
    for window in (7, 14, 28):
        out[f"rolling_mean_{window}"] = grouped.demand.transform(lambda s: s.shift(1).rolling(window, min_periods=window).mean())
        out[f"rolling_std_{window}"] = grouped.demand.transform(lambda s: s.shift(1).rolling(window, min_periods=window).std())
    # All demand-derived features are shifted; current/next demand cannot leak.
    return out


def feature_columns():
    return ["day_of_week", "day_of_month", "month", "is_weekend", "promotion", "price", "lag_1", "lag_7", "lag_14", "lag_28", "rolling_mean_7", "rolling_std_7", "rolling_mean_14", "rolling_std_14", "rolling_mean_28", "rolling_std_28"]
