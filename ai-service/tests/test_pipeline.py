import pandas as pd
from stockwise_ml.metrics import evaluate
from stockwise_ml.pipeline import train_dataset


def _dataset(days=100):
    dates = pd.date_range("2025-01-01", periods=days)
    return pd.DataFrame({"product_id": ["A"] * days, "date": dates, "quantity_demanded": [10 + (i % 7) for i in range(days)], "price": 5, "promotion": 0, "stock_available": 20})


def test_training_selects_model_and_serializes_artifact(tmp_path):
    source = tmp_path / "data.csv"
    _dataset().to_csv(source, index=False)
    metadata = train_dataset(source, tmp_path / "artifacts", "synthetic")
    assert (tmp_path / "artifacts" / "forecast-model.joblib").exists()
    assert metadata["dataset_type"] == "synthetic"
    assert metadata["products"][0]["selected_model"] in {"naive", "seasonal_naive", "random_forest", "xgboost"}


def test_metrics_are_finite():
    result = evaluate([1, 2, 3], [1, 3, 2])
    assert set(result) == {"mae", "rmse", "wape", "bias"}
    assert all(value >= 0 for key, value in result.items() if key != "bias")
