# User Stories dan Acceptance Criteria

Format: **Sebagai [aktor], saya ingin [tujuan], agar [manfaat].**

## Authentication dan akses

### US-001 — Login sesuai role
Sebagai pengguna terdaftar, saya ingin login agar dapat mengakses fungsi sesuai role.

Acceptance criteria:

- Kredensial valid membawa pengguna ke dashboard sesuai role.
- Kredensial invalid menampilkan pesan umum tanpa membocorkan akun mana yang valid.
- Pengguna tanpa izin tidak dapat membuka URL modul melalui navigasi langsung.
- Logout mengakhiri session dan halaman terlindungi tidak dapat dibuka kembali tanpa login.

### US-002 — Admin mengelola pengguna
Sebagai Admin, saya ingin mengelola pengguna dan role agar akses operasional terkendali.

Acceptance criteria:

- Admin dapat membuat, mengubah status, dan menetapkan salah satu role yang tersedia.
- Sistem memvalidasi email unik dan data wajib.
- Perubahan role tercatat di audit log.
- Admin tidak dapat menghapus/menonaktifkan akun terakhir yang diperlukan untuk administrasi tanpa proteksi tambahan.

## Master data dan stok

### US-003 — Admin mengelola master data
Sebagai Admin, saya ingin mengelola produk, kategori, dan supplier agar transaksi menggunakan data yang konsisten.

Acceptance criteria:

- SKU produk unik dan data wajib divalidasi.
- Produk dapat dikaitkan ke kategori dan supplier/lead time yang relevan.
- Data yang sudah dipakai transaksi tidak hilang dari histori ketika dinonaktifkan.
- Perubahan penting tercatat di audit log.

### US-004 — Staff mencatat barang masuk
Sebagai Staff Gudang, saya ingin mencatat penerimaan barang agar saldo stok bertambah sesuai dokumen penerimaan.

Acceptance criteria:

- Form meminta produk, kuantitas positif, tanggal, dan referensi transaksi.
- Penyimpanan gagal seluruhnya bila validasi atau proses database gagal.
- Saldo stok dan histori transaksi berubah dalam satu transaction.
- Pengiriman ulang request dengan reference yang sama tidak menggandakan transaksi.

### US-005 — Staff mencatat barang keluar
Sebagai Staff Gudang, saya ingin mencatat barang keluar agar saldo stok mencerminkan pengeluaran aktual.

Acceptance criteria:

- Sistem menolak kuantitas nol/negatif.
- Sistem menolak stok keluar melebihi saldo yang tersedia sesuai kebijakan MVP.
- Dua transaksi bersamaan tidak dapat menghasilkan saldo negatif akibat race condition.
- Histori menyimpan actor, waktu, produk, kuantitas, dan referensi.

## Monitoring dan laporan

### US-006 — Pengguna melihat dashboard
Sebagai pengguna, saya ingin melihat ringkasan yang relevan dengan role agar dapat menentukan tindakan berikutnya.

Acceptance criteria:

- Dashboard menampilkan saldo stok dan indikator yang sumber datanya dapat ditelusuri.
- Admin/Manajer melihat indikator rekomendasi dan approval; Staff melihat indikator operasional stok/transaksi.
- Filter periode dan status, bila tersedia, menghasilkan angka yang konsisten dengan data detail.
- Kondisi kosong/error memiliki pesan yang jelas.

### US-007 — Manajer melihat laporan
Sebagai Manajer, saya ingin memfilter dan mengekspor laporan agar dapat mengevaluasi persediaan dan keputusan.

Acceptance criteria:

- Laporan dapat difilter berdasarkan periode dan produk/kategori bila relevan.
- Nilai agregat dapat ditelusuri ke transaksi sumber.
- Ekspor hanya tersedia untuk role yang berwenang dan tidak membocorkan data sensitif.

## AI, XAI, dan keputusan

### US-008 — Manajer melihat forecast
Sebagai Manajer, saya ingin melihat forecast per produk agar dapat mengantisipasi kebutuhan mendatang.

Acceptance criteria:

- Hasil menampilkan produk, periode forecast, nilai prediksi, waktu pembuatan, dan versi model.
- Sistem menunjukkan status kelayakan serta metrik evaluasi yang digunakan.
- Bila data tidak mencukupi atau model gagal, sistem menampilkan status tidak tersedia, bukan angka buatan.
- Pengguna dapat membedakan data aktual historis dari nilai forecast.

### US-009 — Manajer memahami penjelasan forecast
Sebagai Manajer, saya ingin melihat penjelasan SHAP agar dapat menilai faktor yang memengaruhi forecast.

Acceptance criteria:

- Tampilan memuat faktor input, nilai input, kontribusi SHAP, dan arah pengaruh.
- Tampilan menyebutkan versi model dan periode data yang dianalisis.
- Sistem menampilkan peringatan bahwa SHAP menjelaskan perilaku model dan bukan hubungan sebab-akibat.
- Jika penjelasan gagal, forecast tidak diberi label seolah-olah sudah dapat dijelaskan.

### US-010 — Sistem membuat rekomendasi restock
Sebagai Manajer, saya ingin menerima rekomendasi kapan dan berapa banyak memesan agar keputusan pembelian lebih konsisten.

Acceptance criteria:

- Rekomendasi memuat alasan dan parameter: stok, forecast selama lead time, safety stock, lead time, serta aturan minimum order.
- Aturan bisnis restock dapat ditinjau terpisah dari model forecast.
- Rekomendasi yang datanya tidak lengkap diberi status perlu ditinjau atau tidak tersedia.
- Sistem menyimpan snapshot input sehingga rekomendasi lama tidak berubah diam-diam ketika data master berubah.

### US-011 — Manajer menyetujui/menolak rekomendasi
Sebagai Manajer, saya ingin menyetujui atau menolak rekomendasi dengan catatan agar keputusan memiliki akuntabilitas.

Acceptance criteria:

- Hanya Manajer berwenang yang dapat mengubah status approval.
- Approval/rejection membutuhkan konfirmasi dan catatan saat menolak.
- Sistem menyimpan actor, timestamp, status, komentar, forecast/model, dan snapshot rekomendasi.
- Status keputusan tidak dapat diubah tanpa jejak audit.

### US-012 — Audit keputusan
Sebagai Admin atau auditor berwenang, saya ingin melihat riwayat aktivitas agar dapat menelusuri perubahan dan keputusan.

Acceptance criteria:

- Audit log dapat difilter berdasarkan actor, action, target, dan periode.
- Log tidak dapat diedit melalui alur operasional biasa.
- Detail tidak menampilkan secret atau data yang tidak diperlukan.
