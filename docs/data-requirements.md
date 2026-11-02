# Kebutuhan Data dan Dataset AI

## 1. Tujuan data

Data digunakan untuk dua hal yang dipisahkan:

1. **Sistem operasional:** menjaga master data, saldo stok, transaksi, approval, dan audit.
2. **AI decision support:** membentuk fitur historis, melatih/mengevaluasi model forecast, menghasilkan inference, dan menjelaskan hasilnya.

Data operasional tidak boleh diubah hanya demi memenuhi format model; pipeline transformasi harus terdokumentasi dan dapat direproduksi.

## 2. Field minimum dataset historis

| Field | Wajib | Definisi dan catatan kualitas |
|---|---:|---|
| `product_id` | Ya | Identifier stabil produk; harus dapat dipetakan ke master produk dan tidak berubah karena nama produk berubah. |
| `date` | Ya | Tanggal granularitas observasi; timezone dan kalender harus konsisten. |
| `quantity_demanded` / `quantity_sold` | Ya | Kuantitas permintaan/penjualan pada tanggal tersebut; definisi harus dipilih dan konsisten. |
| `price` | Disarankan | Harga pada periode transaksi; jelaskan apakah harga unit bersih atau kotor. |
| `promotion` | Jika tersedia | Flag atau atribut promosi yang aktif pada tanggal/produk; definisi dan sumber harus jelas. |
| `supplier_lead_time` | Ya untuk restock | Lead time dalam satuan hari dan periode berlaku; dapat berasal dari supplier/product-supplier. |
| `stock_availability` | Disarankan | Stok tersedia atau flag out-of-stock untuk membedakan permintaan rendah dari barang tidak tersedia. |

## 3. Field tambahan yang direkomendasikan

- `category_id`, unit, supplier, minimum order quantity, order multiple, safety stock/service level.
- `quantity_received`, `quantity_returned`, adjustment, dan stockout duration.
- Hari libur, musim, event, lokasi/gudang, channel, dan status produk.
- Timestamp ingestion, source system, data quality status, dan data correction reason.

## 4. Aturan kualitas dan persiapan

- Satu baris harus memiliki kombinasi kunci yang jelas, minimal `product_id + date + source/granularity`.
- Duplikasi, tanggal invalid, kuantitas negatif, perubahan SKU, dan missing value harus dilaporkan sebelum training.
- Hari dengan stok habis tidak boleh diperlakukan sebagai permintaan nol tanpa aturan imputasi yang disetujui.
- Produk baru, produk discontinued, retur, transfer, dan outlier promosi perlu diberi label atau aturan penanganan.
- Pisahkan train/validation/test berdasarkan waktu untuk menghindari kebocoran data.
- Fitur yang digunakan saat training harus tersedia dengan definisi yang sama saat inference.
- Dataset dan model yang menghasilkan forecast disimpan dengan versi, periode cakupan, checksum/metadata, serta status kualitas.

## 5. Kebutuhan minimum untuk forecast

Ambang minimum belum ditetapkan karena histori aktual belum diinventarisasi. Sebelum produksi, tetapkan per produk atau kelompok produk:

- jumlah periode historis minimum;
- granularitas (harian/mingguan) dan horizon forecast;
- ambang missingness dan outlier;
- metrik dan baseline pembanding;
- ambang kelayakan model dan fallback yang sah.

Jika ambang tidak terpenuhi, hasil harus berstatus **tidak tersedia/perlu tinjauan**, bukan memakai angka prediksi palsu. Fallback aturan sederhana hanya boleh digunakan bila secara eksplisit disetujui sebagai metode non-ML dan diberi label yang jujur.

## 6. Status dataset historis saat analisis

**Dataset bisnis historis belum tersedia atau belum teridentifikasi di repository saat Tahap 1.** Repository saat ini adalah skeleton Laravel: hanya terdapat migration bawaan users/cache/jobs dan belum ada modul/domain persediaan atau file dataset bisnis.

Konfirmasi yang masih diperlukan dari pemilik data:

- lokasi sumber data (database, POS, spreadsheet, ERP);
- rentang tanggal, jumlah produk, jumlah transaksi, dan granularitas;
- apakah data penjualan mencerminkan demand atau dibatasi stockout;
- ketersediaan harga, promosi, supplier, lead time, dan saldo stok historis;
- aturan privasi, retensi, dan izin penggunaan untuk training.

## 7. Opsi jika data historis belum ada

### Opsi A — Menunggu dan mengumpulkan data internal

Paling relevan untuk produksi. Sistem mencatat transaksi mulai sekarang dan menunggu periode minimum. Keterbatasannya adalah MVP AI belum segera memiliki histori yang cukup.

### Opsi B — Dataset publik

Dataset retail/time-series publik dapat dipakai untuk eksperimen pipeline dan demonstrasi metodologi, dengan pemetaan field yang terdokumentasi. Keterbatasannya: konteks produk, musim, harga, promosi, lead time, dan perilaku pelanggan dapat berbeda dari perusahaan; hasil tidak boleh dianggap valid untuk keputusan produksi.

### Opsi C — Dataset sintetis

Data sintetis dapat membantu menguji skema, pipeline, queue, XAI, dan skenario stockout/overstock. Parameter harus didokumentasikan dan label **sintetis** wajib tampil. Keterbatasannya: pola sintetis bisa terlalu sederhana atau tidak mewakili bias serta kejadian nyata sehingga tidak boleh dipakai untuk mengklaim akurasi bisnis.

Rekomendasi: gunakan data sintetis hanya untuk pengujian teknis awal, dataset publik untuk eksperimen pembanding, dan data internal yang tervalidasi sebagai syarat forecast produksi.

## 8. Kontrak keluaran AI

Setiap hasil inference minimal membawa `product_id`, periode forecast, nilai prediksi, unit, model/version, dataset period, timestamp, metrik evaluasi, status kualitas, dan referensi penjelasan SHAP. Rekomendasi restock juga menyimpan stok saat keputusan, lead time, safety stock, aturan yang dipakai, serta snapshot input.
