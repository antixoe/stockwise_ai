# StockWise AI ML Service

Modul ini hanya menangani audit dataset, preprocessing, training, evaluasi, dan inference demand forecasting. Modul ini tidak memiliki akses ke MySQL dan tidak mengubah stok.

## Menjalankan secara lokal

```powershell
python -m venv .venv
.\.venv\Scripts\Activate.ps1
pip install -r requirements.txt
python scripts/generate_synthetic_dataset.py --output data/synthetic_demand.csv
python scripts/train.py --input data/synthetic_demand.csv --artifacts artifacts
python scripts/audit_dataset.py --input data/synthetic_demand.csv --output artifacts/dataset-audit.json
pytest
```

Dataset yang dibuat generator diberi label `synthetic` pada metadata dan hanya untuk verifikasi teknis.

Service HTTP opsional bind ke localhost saja:

```powershell
$env:STOCKWISE_AI_SECRET = 'secret-di-environment'
python service.py --artifacts artifacts
```

Laravel mengirim payload JSON dengan HMAC timestamp/nonce. Jangan mengekspos service ini ke internet.
