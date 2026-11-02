# AGENTS.md — StockWise AI

## Tujuan

StockWise AI adalah sistem informasi persediaan berbasis Laravel untuk membantu pencatatan stok, prediksi permintaan, rekomendasi restock, dan pengambilan keputusan yang dapat dijelaskan.

## Stack wajib

- Laravel + PHP sebagai backend utama; MySQL sebagai database produksi.
- Eloquent ORM, Blade + Livewire, Tailwind CSS, dan Flux UI atau komponen Livewire kompatibel.
- Chart.js dan Lucide atau ikon kompatibel.
- Authentication starter kit Livewire, Policies/Gates, dan Laravel Queues.
- Pest/PHPUnit untuk Laravel; Python, scikit-learn/XGBoost, SHAP, dan pytest untuk AI.

React, Next.js, PostgreSQL, dan FastAPI tidak digunakan sebagai backend utama.

## Standar kerja

1. Periksa repository sebelum mengubah file dan jangan menimpa perubahan pengguna.
2. Gunakan MVC, Service Layer, Form Request, relationships, migrations, seeders, dan factories sesuai kebutuhan.
3. Gunakan transaction dan locking ketika memodifikasi stok.
4. Pisahkan aturan restock dari algoritma ML; pisahkan training dari inference.
5. Jangan gunakan prediksi palsu sebagai hasil AI produksi dan jangan simpan secrets di repository.
6. Gunakan authorization server-side, logging aman, dan error handling yang tidak membocorkan data sensitif.
7. UI menggunakan Bahasa Indonesia, responsif, konsisten, dan dapat diakses.
8. Semua perubahan harus diuji secara proporsional.
9. Jangan menghapus data, menjalankan `migrate:fresh` pada database berisi data, atau deploy tanpa izin eksplisit.

## Proses tahap

Kerjakan satu tahap sesuai instruksi pengguna. Pada akhir tahap, perbarui `docs/development-progress.md`, jalankan verifikasi yang relevan, laporkan hasil dan risiko, lalu berhenti menunggu instruksi berikutnya.
