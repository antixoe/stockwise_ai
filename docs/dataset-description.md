# Dataset Description dan Audit Tahap 7

## Status audit

Pada audit 20 Oktober 2026, tidak ada file CSV/Parquet/XLSX bisnis di repository. `.env` lokal menggunakan SQLite dan migration domain inventory/`sales_history` masih pending; karena itu belum ada data historis perusahaan yang dapat diaudit dari database lokal. MySQL tetap target environment produksi, tetapi koneksi MySQL tidak diubah atau dijalankan pada tahap ini.

Pipeline menerima kontrak kolom berikut:

| Kolom | Wajib | Makna |
|---|---:|---|
| `product_id` | Ya | Identitas SKU |
| `date` | Ya | Tanggal demand/penjualan |
| `quantity_demanded` atau `quantity_sold` | Ya | Target demand; non-negatif |
| `price` | Tidak | Harga unit pada tanggal tersebut |
| `promotion`/`promotion_flag` | Tidak | Indikator promosi |
| `stock_available` | Tidak | Ketersediaan yang dapat mengindikasikan censoring |

Preprocessor mengagregasikan duplikasi per SKU-tanggal, membuat tanggal yang hilang, menyimpan `is_missing_observation`, dan tidak menyamakan observasi quantity nol dengan baris tanggal yang hilang. `stockout_censored` ditandai bila availability nol dan demand positif; ini adalah sinyal censoring, bukan estimasi demand yang hilang.

## Dataset simulasi development

`ai-service/scripts/generate_synthetic_dataset.py` membuat 4 SKU, 240 hari, pola mingguan, promosi, harga, noise, dan ketersediaan stok dengan seed 42. File `ai-service/data/synthetic_demand.csv` dan artifact terkait diberi label `synthetic`. Data ini hanya membuktikan pipeline teknis dapat berjalan; metriknya bukan bukti performa bisnis dan tidak boleh dipakai untuk keputusan pembelian.

Audit generator mencakup jumlah baris/SKU, rentang tanggal, missing values, quantity negatif, baris observasi yang hilang, quantity nol, serta dugaan stockout censoring. Dataset internal tervalidasi harus menggantikan dataset simulasi sebelum produksi.

## Kecukupan data

Ambang implementasi saat ini adalah minimal 42 hari per SKU untuk model fitur lag/rolling. Produk di bawah ambang diberi `insufficient_data` dan tidak menghasilkan forecast palsu. Untuk keputusan produksi, ambang ini harus divalidasi bersama pemilik bisnis; idealnya histori mencakup beberapa siklus musiman dan periode promosi.
