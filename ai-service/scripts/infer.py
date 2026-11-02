import argparse, json
from stockwise_ml.pipeline import infer

parser = argparse.ArgumentParser(); parser.add_argument('--artifact', required=True); parser.add_argument('--product-id', required=True); parser.add_argument('--horizon', type=int, choices=(7,30), required=True); args = parser.parse_args()
print(json.dumps(infer(args.artifact, args.product_id, args.horizon), indent=2))
