import argparse, json
import pandas as pd
from stockwise_ml.preprocessing import prepare_daily_demand

parser = argparse.ArgumentParser(); parser.add_argument('--input', required=True); parser.add_argument('--output', required=True); args = parser.parse_args()
raw = pd.read_csv(args.input)
daily = prepare_daily_demand(raw)
report = {'dataset_type': 'synthetic' if 'synthetic' in args.input.lower() else 'unknown', 'rows': int(len(raw)), 'products': int(raw.product_id.nunique()), 'date_min': str(pd.to_datetime(raw.date).min().date()), 'date_max': str(pd.to_datetime(raw.date).max().date()), 'missing_values': {k: int(v) for k,v in raw.isna().sum().items()}, 'negative_quantity_rows': int((pd.to_numeric(raw.get('quantity_demanded', raw.get('quantity_sold')), errors='coerce') < 0).sum()), 'daily_missing_observation_rows': int(daily.is_missing_observation.sum()), 'stockout_censored_rows': int(daily.stockout_censored.sum()), 'zero_demand_rows': int((daily.demand == 0).sum()), 'note': 'Synthetic data is for technical validation only.'}
with open(args.output, 'w', encoding='utf-8') as handle: json.dump(report, handle, indent=2)
print(json.dumps(report, indent=2))
