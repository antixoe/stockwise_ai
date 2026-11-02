import argparse
from pathlib import Path
import numpy as np
import pandas as pd

parser = argparse.ArgumentParser()
parser.add_argument('--output', required=True)
parser.add_argument('--days', type=int, default=240)
parser.add_argument('--products', type=int, default=4)
args = parser.parse_args()
rng = np.random.default_rng(42)
dates = pd.date_range('2025-01-01', periods=args.days, freq='D')
rows = []
for index in range(args.products):
    product = f'SKU-{index+1:03d}'
    base = 8 + index * 4
    for date in dates:
        promo = int(date.day in (1, 15) or date.dayofweek == 5)
        price = round(10 + index * 2 - promo * 0.75, 2)
        demand = max(0, base + 2 * np.sin(date.dayofyear / 14) + (3 if date.dayofweek >= 5 else 0) + (5 if promo else 0) + rng.normal(0, 1.5))
        stock = max(0, round(demand + rng.normal(10, 4), 2))
        sold = min(demand, stock)
        rows.append({'product_id': product, 'date': date.date(), 'quantity_demanded': round(demand, 2), 'quantity_sold': round(sold, 2), 'price': price, 'promotion': promo, 'stock_available': stock})
Path(args.output).parent.mkdir(parents=True, exist_ok=True)
pd.DataFrame(rows).to_csv(args.output, index=False)
print(f'Generated SIMULATED dataset: {args.output} ({len(rows)} rows)')
