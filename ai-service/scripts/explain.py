import argparse, json
from stockwise_ml.explainability import explain

parser = argparse.ArgumentParser(); parser.add_argument('--artifact', required=True); parser.add_argument('--product-id', required=True); parser.add_argument('--forecast-date', required=True); args = parser.parse_args()
print(json.dumps(explain(args.artifact, args.product_id, args.forecast_date), indent=2))
