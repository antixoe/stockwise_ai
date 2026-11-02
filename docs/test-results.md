# Test Results Tahap 9

Tanggal eksekusi: 2026-10-20

## Passed

- Laravel: **51 test, 141 assertions** lulus setelah regression test endpoint XAI ditambahkan.
- Python: **10 pytest** lulus. Eksekusi kedua memakai temp directory lokal karena eksekusi awal paralel terkena `PermissionError` pada direktori temp Windows.
- PHP lint: **123 file**, seluruhnya tanpa syntax error.
- `php artisan migrate --pretend --env=testing`: berhasil menghasilkan seluruh SQL migration, termasuk foreign key, unique index, dan migration `global_importance`.
- `php artisan route:list --path=internal`: 22 route terdaftar.
- `npm.cmd run build`: Vite production build berhasil.
- Cakupan test mencakup authentication, role/policy, CRUD catalog, stock transaction, rollback, idempotency, negative stock, Livewire pages/forms, forecast job, SHAP payload, restock formula, dan manager approval.

## Bugs fixed

- `AiForecastClient::explain()` sebelumnya selalu POST ke `config('ai.forecast_url')`, sehingga endpoint explainability tidak pernah dipanggil. Helper sekarang menerima dan memakai URL endpoint yang diberikan; regression test ditambahkan.

## Failed

- Tidak ada test aplikasi yang tetap gagal setelah perbaikan.

## Blocked

- Validasi integrasi nyata MySQL belum dilakukan: environment menyediakan ekstensi `pdo_mysql`, tetapi server/database MySQL dan kredensial development yang dapat diuji tidak tersedia. Tidak ada `migrate:fresh` atau migration destruktif dijalankan.
- `composer audit --locked` dan `npm.cmd audit --omit=dev` tidak dapat menghubungi registry karena network diblokir; hasil vulnerability online belum tersedia.
- Browser/device testing desktop, tablet, mobile, keyboard, dan visual chart belum dilakukan karena tidak ada browser automation tooling aktif.
- E2E penuh dengan queue worker dan AI HTTP service nyata belum dilakukan. Komponen alurnya telah diuji melalui feature/unit tests dan HTTP fake.

## Skipped

- Upload validation: tidak ada fitur file upload di repository.
- True multi-process concurrent MySQL update: test yang ada memverifikasi row locking secara serial pada SQLite test database; concurrency database engine nyata memerlukan MySQL harness/CI.
- Duplicate-data scan pada dataset produksi: dataset bisnis dan database produksi tidak tersedia; unique constraints dan factory/test data direview.

## Risiko tersisa

Validasi production-like harus dilanjutkan pada environment staging dengan MySQL, browser automation, queue worker, AI service private network, dan akses registry dependency yang diizinkan. Hasil ini tidak menyatakan 100% coverage atau readiness production.
