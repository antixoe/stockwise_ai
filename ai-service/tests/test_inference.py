import pandas as pd
from stockwise_ml.pipeline import infer, train_dataset


def test_inference_supports_both_horizons_and_nonnegative_values(tmp_path):
    dates = pd.date_range("2025-01-01", periods=100)
    data = pd.DataFrame({"product_id": ["A"] * 100, "date": dates, "quantity_demanded": [5 + i % 3 for i in range(100)]})
    source = tmp_path / "data.csv"; data.to_csv(source, index=False)
    train_dataset(source, tmp_path / "artifacts", "synthetic")
    for horizon in (7, 30):
        result = infer(tmp_path / "artifacts" / "forecast-model.joblib", "A", horizon)
        assert result["status"] == "success"
        assert len(result["predictions"]) == horizon
        assert all(row["predicted_quantity"] >= 0 for row in result["predictions"])


def test_sparse_product_returns_insufficient_data(tmp_path):
    data = pd.DataFrame({"product_id": ["A"] * 10, "date": pd.date_range("2025-01-01", periods=10), "quantity_demanded": 1})
    source = tmp_path / "data.csv"; data.to_csv(source, index=False)
    train_dataset(source, tmp_path / "artifacts", "synthetic")
    assert infer(tmp_path / "artifacts" / "forecast-model.joblib", "A", 7)["status"] == "insufficient_data"
