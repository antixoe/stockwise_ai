# Analisis Kebutuhan Sistem StockWise AI

**Judul:** Analisis dan Perancangan Sistem Informasi Pengambilan Keputusan Persediaan Berbasis Explainable Artificial Intelligence  
**Tahap:** 1 — Analisis Kebutuhan Sistem  
**Status:** Draft analisis untuk divalidasi bersama pemilik proses bisnis

## 1. Latar belakang dan masalah

Pengelolaan persediaan yang hanya mengandalkan pemeriksaan manual dan intuisi pembelian berisiko menimbulkan:

1. **Stockout:** stok habis sebelum pasokan berikutnya tiba, sehingga penjualan atau operasi tertunda dan tingkat layanan turun.
2. **Overstock:** barang menumpuk melebihi kebutuhan, meningkatkan biaya penyimpanan, risiko rusak/kedaluwarsa, dan modal kerja yang tertahan.
3. **Pembelian yang tidak tepat:** jumlah dan waktu pemesanan tidak konsisten karena keputusan tidak menggunakan tren penjualan, lead time, atau batas stok yang terdokumentasi.
4. **Kurangnya prediksi kebutuhan:** pengguna sulit memperkirakan permintaan mendatang, terutama ketika terdapat tren musiman, promosi, atau pola penjualan yang berubah.
5. **Kurangnya transparansi:** manajer tidak dapat melihat alasan, data pendukung, versi model, atau riwayat keputusan di balik sebuah rekomendasi.

## 2. Tujuan dan manfaat

### Tujuan

- Menyediakan sumber data persediaan yang terpusat dan konsisten.
- Mencatat pergerakan barang masuk dan keluar secara terlacak.
- Menyajikan kondisi stok dan indikator risiko secara cepat.
- Menghasilkan forecast permintaan yang terukur dari data historis yang tersedia.
- Menghasilkan rekomendasi kapan memesan dan jumlah pemesanan dengan aturan bisnis yang jelas.
- Menampilkan penjelasan faktor yang memengaruhi prediksi dan menyimpan jejak keputusan manajer.

### Manfaat yang diharapkan

- Mengurangi kejadian stockout dan overstock.
- Mempercepat pemeriksaan dan persetujuan pembelian.
- Meningkatkan konsistensi keputusan antar pengguna.
- Memudahkan audit, evaluasi model, dan analisis kinerja persediaan.
- Memberikan dasar keputusan yang dapat ditinjau manusia, bukan otomatisasi tanpa kontrol.

## 3. Aktor, persona, dan hak akses

| Aktor | Persona | Kebutuhan utama | Hak akses MVP |
|---|---|---|---|
| Admin | Pengelola master data dan operasional sistem | Data produk, kategori, supplier, pengguna, dan audit | Kelola pengguna/role, CRUD master data, melihat transaksi, laporan, audit log; tidak menyetujui rekomendasi sendiri |
| Staff Gudang | Pelaksana penerimaan dan pengeluaran barang | Input transaksi cepat dan melihat stok aktual | Login, melihat dashboard operasional dan stok, membuat transaksi masuk/keluar, melihat riwayat transaksi; tidak mengelola pengguna atau approve rekomendasi |
| Manajer | Pengambil keputusan persediaan | Ringkasan risiko, forecast, XAI, dan approval | Melihat dashboard, forecast, penjelasan, laporan, dan memberi approval/rejection beserta catatan; tidak mengubah master data kecuali diberi izin tambahan |

Semua akses wajib diperiksa di server menggunakan Policies/Gates; penyembunyian menu saja bukan kontrol keamanan.

## 4. Keputusan bisnis utama

Fokus decision support adalah:

> **Kapan barang perlu dipesan kembali dan berapa jumlah yang direkomendasikan?**

Input keputusan minimal mencakup stok tersedia, stok yang sudah dipesan namun belum diterima bila ada, forecast permintaan selama lead time, safety stock, lead time supplier, dan aturan minimum order. Sistem memberikan rekomendasi dan alasan; keputusan final tetap pada manajer.

Tujuan AI yang dapat diuji:

- menghasilkan forecast untuk horizon yang disepakati (default awal: 7, 14, atau 30 hari per produk);
- menyajikan metrik evaluasi out-of-sample yang dipilih pada Tahap 7 (misalnya MAE/WAPE sesuai karakter data);
- menampilkan faktor input, arah pengaruh, dan nilai kontribusi SHAP untuk setiap forecast yang disajikan;
- tidak menerbitkan forecast produksi bila data minimum, kualitas data, atau evaluasi model tidak memenuhi ambang yang ditetapkan.

## 5. Kebutuhan fungsional

| ID | Kebutuhan | Prioritas | Aktor |
|---|---|---:|---|
| FR-001 | Sistem menyediakan login, logout, session timeout, dan pemulihan akses sesuai konfigurasi keamanan. | Must | Semua |
| FR-002 | Sistem menerapkan role Admin, Staff Gudang, dan Manajer serta authorization server-side. | Must | Semua |
| FR-003 | Sistem menampilkan dashboard sesuai role, termasuk ringkasan stok, transaksi, risiko stockout/overstock, dan item yang memerlukan perhatian. | Must | Semua |
| FR-004 | Admin dapat membuat, membaca, mengubah, mengarsipkan, dan mencari data produk beserta SKU, satuan, harga, stok minimum, dan status aktif. | Must | Admin |
| FR-005 | Admin dapat CRUD kategori dan mengaitkannya dengan produk. | Must | Admin |
| FR-006 | Admin dapat CRUD supplier beserta kontak dan lead time. | Must | Admin |
| FR-007 | Admin dapat mengelola pengguna dan role sesuai kebijakan organisasi. | Must | Admin |
| FR-008 | Sistem mencatat transaksi barang masuk dengan produk, kuantitas, tanggal, supplier, referensi, dan catatan. | Must | Admin, Staff |
| FR-009 | Sistem mencatat transaksi barang keluar dengan produk, kuantitas, tanggal, referensi, dan catatan serta mencegah stok negatif kecuali kebijakan resmi mengizinkan. | Must | Admin, Staff |
| FR-010 | Sistem memperbarui saldo stok secara atomik dan menyimpan riwayat perubahan. | Must | Sistem |
| FR-011 | Pengguna berwenang dapat melihat dan memfilter riwayat transaksi serta riwayat permintaan berdasarkan produk dan periode. | Must | Semua sesuai izin |
| FR-012 | Sistem menerima/menyimpan data permintaan atau penjualan historis yang menjadi sumber forecast. | Must | Admin, Staff |
| FR-013 | Sistem menjalankan inference forecast melalui proses yang terpisah dari training dan menyimpan versi model, waktu proses, horizon, status, dan metrik. | Must | Sistem |
| FR-014 | Sistem menampilkan hasil forecast per produk dan periode dengan status kualitas/kelayakan model. | Must | Manajer, Admin |
| FR-015 | Sistem menampilkan penjelasan XAI berbasis SHAP yang memuat faktor, arah pengaruh, nilai kontribusi, dan batasan interpretasi. | Must | Manajer, Admin |
| FR-016 | Sistem menghitung rekomendasi restock berdasarkan forecast, stok, safety stock, lead time, dan aturan bisnis terpisah dari model ML. | Must | Sistem |
| FR-017 | Manajer dapat meninjau detail rekomendasi, forecast, XAI, dan data pendukung. | Must | Manajer |
| FR-018 | Manajer dapat menyetujui atau menolak rekomendasi dengan komentar; sistem menyimpan identitas, waktu, status, dan snapshot keputusan. | Must | Manajer |
| FR-019 | Sistem menampilkan laporan stok, transaksi, forecast, rekomendasi, dan keputusan dengan filter periode serta ekspor sesuai izin. | Should | Admin, Manajer |
| FR-020 | Sistem mencatat audit log untuk login penting, perubahan master data, transaksi stok, inference, dan approval/rejection. | Must | Sistem, Admin |
| FR-021 | Sistem memproses training/inference atau laporan berat melalui queue dan memberi status proses kepada pengguna. | Should | Sistem |

## 6. Kebutuhan nonfungsional

| ID | Kebutuhan | Kriteria awal |
|---|---|---|
| NFR-001 | Security | Password di-hash, CSRF aktif, validasi input, authorization server-side, secret di environment, dan audit aktivitas kritis. |
| NFR-002 | Data protection | Data sensitif diminimalkan, error produksi tidak membocorkan detail internal, dan akses laporan mengikuti role. |
| NFR-003 | Performance | Halaman operasional umum ditargetkan respons server p95 ≤ 2 detik pada beban MVP yang disepakati; forecast/laporan berat boleh asynchronous. |
| NFR-004 | Scalability | Query daftar memakai pagination/filter; training dan pekerjaan berat tidak memblokir request web. |
| NFR-005 | Availability | Aplikasi memiliki health check operasional, penanganan kegagalan job, dan dokumentasi backup/restore; target uptime ditetapkan sebelum produksi. |
| NFR-006 | Maintainability | Mengikuti struktur Laravel resmi, Service Layer untuk aturan kompleks, konfigurasi terversi tanpa secret, dan test otomatis. |
| NFR-007 | Usability | UI Bahasa Indonesia dengan istilah konsisten, validasi dan pesan error yang dapat ditindaklanjuti. |
| NFR-008 | Responsive design | Alur utama dapat digunakan pada desktop, tablet, dan layar ponsel tanpa kehilangan informasi penting. |
| NFR-009 | Data integrity | Transaksi stok memakai database transaction/locking, foreign key, constraint, idempotency/reference transaksi, dan tidak menghapus histori secara fisik tanpa kebijakan. |
| NFR-010 | Explainability | Setiap forecast produksi menampilkan versi model, waktu data, input utama, kontribusi SHAP, dan peringatan bahwa XAI adalah penjelasan model bukan sebab-akibat. |
| NFR-011 | Auditability | Perubahan keputusan dan data operasional memiliki actor, timestamp, action, target, dan metadata yang memadai. |
| NFR-012 | Testability | Business rule, authorization, transaksi stok, API/integration inference, dan pipeline AI memiliki pengujian yang dapat diulang. |

## 7. Asumsi, risiko, dan pertanyaan validasi

- Data historis mungkin belum tersedia atau belum konsisten per produk dan tanggal.
- Forecast tidak menjamin permintaan aktual; keputusan tetap memerlukan validasi manajer.
- Promosi, out-of-stock, retur, perubahan harga, dan lead time yang kosong dapat menyebabkan bias.
- Ambang minimum data, horizon forecast, safety stock, service level, dan aturan pembulatan kuantitas harus disetujui pemilik proses.
- Status dataset aktual belum dapat disimpulkan dari skeleton repository; perlu inventaris data bisnis pada tahap berikutnya.
