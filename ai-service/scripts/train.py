import argparse, json
from stockwise_ml.pipeline import train_dataset

parser = argparse.ArgumentParser(); parser.add_argument('--input', required=True); parser.add_argument('--artifacts', required=True); parser.add_argument('--dataset-type', default='unknown'); args = parser.parse_args()
print(json.dumps(train_dataset(args.input, args.artifacts, args.dataset_type), indent=2))
