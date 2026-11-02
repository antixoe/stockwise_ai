# Evaluasi Model

## Run development

Run reproduksi:

```powershell
cd ai-service
python scripts/generate_synthetic_dataset.py --output data/synthetic_demand.csv
python scripts/train.py --input data/synthetic_demand.csv --artifacts artifacts --dataset-type synthetic
```

Run ini menggunakan data SIMULASI (4 SKU, 240 hari, seed 42), split temporal dengan test 30 hari, dan memilih model per SKU berdasarkan WAPE. Artifact metadata berada di `ai-service/artifacts/training-metadata.json`.

| SKU | Model terpilih | MAE | RMSE | WAPE | Bias |
|---|---|---:|---:|---:|---:|
| SKU-001 | Random Forest | 1.2374 | 1.4610 | 13.35% | 5.29% |
| SKU-002 | XGBoost | 1.0913 | 1.3342 | 8.20% | 5.87% |
| SKU-003 | Random Forest | 1.4724 | 1.8206 | 8.55% | 4.77% |
| SKU-004 | XGBoost | 1.4198 | 1.7249 | 6.76% | 3.11% |

Nilai di atas hanya hasil validasi teknis pada data sintetis dan tidak boleh dianggap sebagai akurasi pada perusahaan. Belum ada evaluasi data historis bisnis, walk-forward produksi, confidence interval, atau evaluasi censoring yang tervalidasi.

## Acceptance dan retraining

Setiap training menyimpan model version, dataset type, feature list, timestamp, model terpilih, dan metrik. Retraining minimal dijadwalkan berkala setelah data internal cukup, serta dipicu bila drift/performa melewati ambang bisnis yang nanti disepakati. Model tidak boleh dipromosikan hanya karena WAPE rendah pada dataset simulasi.
