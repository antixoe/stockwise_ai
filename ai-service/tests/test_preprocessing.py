import pandas as pd
import pytest

from stockwise_ml.preprocessing import feature_columns, make_features, prepare_daily_demand


def test_daily_aggregation_marks_missing_dates_and_preserves_zero():
    raw = pd.DataFrame([
        {"product_id": "A", "date": "2025-01-01", "quantity_demanded": 0, "stock_available": 10},
        {"product_id": "A", "date": "2025-01-03", "quantity_demanded": 4, "stock_available": 0},
    ])
    result = prepare_daily_demand(raw)
    assert len(result) == 3
    assert result.loc[result.date == "2025-01-01", "demand"].iloc[0] == 0
    assert result.loc[result.date == "2025-01-02", "is_missing_observation"].iloc[0] == 1
    assert result.loc[result.date == "2025-01-03", "stockout_censored"].iloc[0] == 1


def test_features_do_not_use_current_demand_for_lag_or_rolling():
    raw = pd.DataFrame({"product_id": ["A"] * 40, "date": pd.date_range("2025-01-01", periods=40), "quantity": range(40)})
    features = make_features(prepare_daily_demand(raw))
    row = features.iloc[28]
    assert row["lag_1"] == 27
    assert row["rolling_mean_7"] == sum(range(21, 28)) / 7
    assert "demand" not in feature_columns()


def test_invalid_schema_is_rejected():
    with pytest.raises(ValueError):
        prepare_daily_demand(pd.DataFrame({"date": ["2025-01-01"]}))
