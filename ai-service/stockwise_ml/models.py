from __future__ import annotations

import numpy as np
from sklearn.ensemble import RandomForestRegressor


def make_model(name: str, seed: int = 42):
    if name == "random_forest":
        return RandomForestRegressor(n_estimators=120, max_depth=12, random_state=seed, n_jobs=1, min_samples_leaf=2)
    if name == "xgboost":
        try:
            from xgboost import XGBRegressor
            return XGBRegressor(n_estimators=180, max_depth=5, learning_rate=0.05, subsample=0.9, colsample_bytree=0.9, objective="reg:squarederror", random_state=seed, n_jobs=1)
        except ImportError:
            return None
    raise ValueError(f"Model tidak dikenal: {name}")


def non_negative(values):
    return np.maximum(np.asarray(values, dtype=float), 0.0)
