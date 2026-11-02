# Metodologi Machine Learning Demand Forecasting

## Batas tanggung jawab

Laravel tetap mengelola authentication, authorization, MySQL, inventory, transaksi, queue, dan penyimpanan hasil. `ai-service/` tidak mengakses MySQL serta tidak boleh mengubah saldo stok. Training adalah proses offline/terjadwal; inference dipanggil oleh Laravel Queue melalui service internal localhost/private network.

## Preprocessing dan leakage prevention

1. Parse tanggal dan buang record dengan tanggal/SKU invalid.
2. Agregasi demand harian per SKU.
3. Reindex rentang tanggal per SKU; baris yang tidak ada diberi demand teknis 0 tetapi ditandai `is_missing_observation=1`, sehingga tidak dianggap penjualan nol yang teramati.
4. Tandai `stockout_censored` dari availability.
5. Buat fitur kalender: hari dalam minggu/bulan, bulan, dan weekend.
6. Buat lag 1/7/14/28 hari dan rolling mean/std 7/14/28 hari yang selalu digeser satu hari.
7. Harga/promosi dipakai bila tersedia; nilai yang tidak tersedia diberi default eksplisit dan harus dipantau pada data produksi.

Tidak ada target demand masa depan atau rolling window yang membaca baris saat ini. Split dilakukan kronologis; tidak ada random shuffle.

## Model dan forecast

Baseline adalah naive (nilai terakhir) dan seasonal-naive (nilai tujuh hari sebelumnya). Kandidat supervised adalah Random Forest dan XGBoost bila library tersedia. Pemilihan dilakukan per SKU berdasarkan WAPE pada test temporal; model kompleks tidak otomatis dipilih.

Forecast 7/30 hari memakai recursive one-step forecasting. Feature demand masa depan dibentuk dari prediksi sebelumnya; promosi masa depan belum diketahui sehingga default 0 dan harga default nilai terakhir. Prediksi dibatasi minimum nol. Model version, data-as-of, metrik, status, dan horizon disimpan bersama hasil di tabel `forecasts` Laravel.

## Integrasi service

`AiForecastClient` memakai Laravel HTTP Client, timeout, retry terbatas, request id, timestamp, nonce, dan HMAC atas body mentah. Python service bind ke `127.0.0.1` secara default, memeriksa replay nonce dan umur timestamp, lalu mengembalikan status `success`/`insufficient_data`/`invalid_input`/`model_error`. Secret hanya dari environment (`AI_SERVICE_SECRET`/`STOCKWISE_AI_SECRET`).

`RunForecastInferenceJob` mengambil snapshot `sales_history`, mengirimnya ke service, dan menyimpan baris forecast secara idempotent. Training tidak pernah dilakukan di web request.
