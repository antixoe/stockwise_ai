# Keterbatasan Model dan Risiko

- Belum ada dataset historis bisnis, sehingga belum ada bukti generalisasi.
- Dataset sintetis tidak merepresentasikan seluruh musim, promosi, perubahan harga, supplier, atau perilaku pelanggan.
- Missing date dan stockout dapat membuat demand yang terlihat lebih rendah dari demand sebenarnya; flag tersedia tetapi belum diestimasi dengan metode censored demand.
- Recursive horizon 30 hari dapat mengakumulasi error.
- Harga/promosi masa depan tidak diketahui dalam baseline inference dan menggunakan asumsi konservatif sederhana.
- Produk dengan histori kurang dari 42 hari diberi `insufficient_data`.
- Baseline dan supervised model belum menyediakan interval ketidakpastian yang tervalidasi.
- SHAP dan penjelasan keputusan belum termasuk Tahap 7; akan dikerjakan pada Tahap 8.
- Forecast adalah input keputusan, bukan otorisasi pembelian otomatis. Saldo, lead time, safety stock, dan approval tetap berada di Laravel.
- Service internal harus tetap private, secret tidak boleh dimasukkan ke repository, dan artifact harus memiliki retention/backup policy sebelum deployment.
