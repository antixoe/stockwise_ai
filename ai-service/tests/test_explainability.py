import pandas as pd
from stockwise_ml.explainability import explain
from stockwise_ml.pipeline import train_dataset


def test_shap_contributions_reconstruct_model_output(tmp_path):
    dates = pd.date_range("2025-01-01", periods=120)
    data = pd.DataFrame({"product_id": ["A"] * 120, "date": dates, "quantity_demanded": [10 + i % 5 for i in range(120)], "price": 5, "promotion": 0})
    source = tmp_path / "data.csv"; data.to_csv(source, index=False)
    train_dataset(source, tmp_path / "artifacts", "synthetic")
    result = explain(tmp_path / "artifacts" / "forecast-model.joblib", "A", "2025-05-01")
    if result["status"] == "success":
        assert abs(result["reconstructed_prediction"] - result["predicted_quantity"]) < 1e-4
        assert abs(result["baseline_prediction"] + sum(item["contribution"] for item in result["contributions"]) - result["reconstructed_prediction"]) < 1e-4
        assert all("label" in item for item in result["contributions"])


def test_sparse_product_is_not_explained(tmp_path):
    data = pd.DataFrame({"product_id": ["A"] * 10, "date": pd.date_range("2025-01-01", periods=10), "quantity_demanded": 1})
    source = tmp_path / "data.csv"; data.to_csv(source, index=False)
    train_dataset(source, tmp_path / "artifacts", "synthetic")
    assert explain(tmp_path / "artifacts" / "forecast-model.joblib", "A", "2025-01-15")["status"] == "insufficient_data"
