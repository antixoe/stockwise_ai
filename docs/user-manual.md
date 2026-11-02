# Panduan Pengguna

## Masuk

Buka URL aplikasi HTTPS, masukkan email dan password, lalu selesaikan verifikasi email bila diminta. Logout melalui menu pengguna.

## Produk dan supplier

Admin membuka menu Produk/Supplier untuk menambah atau mengubah data. SKU/kode harus unik. Produk dinonaktifkan melalui aksi arsip, bukan penghapusan permanen.

## Transaksi stok

Pilih Barang masuk atau Barang keluar, produk, kuantitas, tanggal, dan referensi. Review lalu konfirmasi. Barang keluar ditolak bila stok tersedia tidak cukup. Riwayat transaksi bersifat auditable.

## Dashboard dan laporan

Dashboard menampilkan ringkasan stok dan tren transaksi. Laporan menampilkan stok rendah, barang masuk, dan barang keluar berdasarkan ledger.

## Forecast, XAI, dan rekomendasi

Manajer dapat menjalankan forecast horizon 7 atau 30 hari. Forecast hanya tampil bila inference berhasil. Explainable AI menunjukkan baseline dan kontribusi fitur SHAP; kontribusi tersebut menjelaskan perilaku model, bukan sebab-akibat. Rekomendasi restock adalah decision support dan tidak membuat purchase order otomatis.

## Approval

Manajer meninjau rekomendasi pending, memilih approve/reject, menambahkan komentar bila perlu, lalu sistem menyimpan keputusan dan audit. Keputusan yang sudah final tidak dapat diulang.

Laporkan error beserta waktu dan nomor referensi/audit tanpa mengirim password, token, atau secret.
