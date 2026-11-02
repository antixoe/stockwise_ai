# Test Plan Tahap 9

## Tujuan

Memverifikasi alur Laravel, database, AI/XAI, security, UI, dan skenario end-to-end StockWise AI tanpa menyentuh database produksi atau melakukan deployment.

## Lingkup dan metode

- Laravel feature/unit test dengan SQLite in-memory melalui PHPUnit.
- Python `pytest` untuk preprocessing, pipeline, inference, XAI, dan HMAC service.
- `php -l`, `php artisan migrate --pretend --env=testing`, route inspection, dan Vite production build.
- Review statis terhadap routes, policies, Form Requests, migrations, services, Blade/Livewire, konfigurasi, dan secret handling.
- Browser/device automation dan MySQL hanya dijalankan bila tooling/service tersedia.

## Matriks cakupan

| Area | Bukti yang direncanakan | Status |
|---|---|---|
| Authentication | Login, logout, verification, password, inactive user | Dijalankan |
| Authorization | Role, policy, route/component access | Dijalankan |
| CRUD | Product, supplier, category, relationship | Dijalankan |
| Stok | Receipt, issue, negative stock, rollback, reversal, idempotency | Dijalankan |
| Livewire/dashboard/reports | Render halaman, form validation, transaction component | Dijalankan sebagian |
| Approval | Atomic manager decision, duplicate decision, audit | Dijalankan |
| Database | Migration SQL, FK/unique/index review, SQLite constraints | Dijalankan; MySQL blocked |
| ML/XAI | Leakage, empty/sparse data, horizon, model version, SHAP reconstruction | Dijalankan |
| Security | Static review dan authorization tests | Dijalankan; dependency network audit blocked |
| UI/device | Build dan static review | Browser visual blocked |
| E2E penuh | Login sampai history pada browser + AI service nyata | Blocked; komponen diuji terpisah |
