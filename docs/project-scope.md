# Ruang Lingkup Proyek StockWise AI

## Tujuan MVP

MVP membuktikan alur end-to-end yang dapat diaudit:

`login → master data → transaksi stok → histori → forecast tervalidasi → penjelasan XAI → rekomendasi restock → approval manajer → laporan/audit`.

MVP harus berguna walaupun dataset awal terbatas. Ketika forecast tidak layak, sistem menampilkan status data/model tidak cukup dan tidak mengarang nilai.

## Dalam scope MVP

- Authentication, logout, session protection, dan role Admin/Staff Gudang/Manajer.
- Dashboard ringkas berbasis role.
- CRUD produk, kategori, supplier, dan pengguna sesuai izin.
- Stok masuk/keluar dengan validasi, transaction, locking, histori, dan perlindungan idempotency.
- Riwayat permintaan/penjualan yang menjadi sumber data forecast.
- Pipeline inference terpisah dari training; penyimpanan versi model dan metrik.
- Forecast per produk dengan horizon yang disepakati.
- XAI berbasis SHAP dengan faktor dan batasan interpretasi.
- Perhitungan aturan bisnis restock yang menerima hasil forecast sebagai input.
- Review, approve/reject, komentar, dan audit snapshot oleh Manajer.
- Laporan stok, transaksi, forecast, rekomendasi, dan keputusan dengan filter dasar.
- Pengujian Laravel dan pytest untuk alur yang disepakati.

## Di luar scope MVP

- Otomatis membuat purchase order atau mengirim order ke supplier.
- Integrasi ERP, POS, marketplace, barcode scanner, dan IoT secara langsung.
- Multi-gudang atau multi-perusahaan bila belum dibutuhkan oleh proses awal.
- Optimasi harga, dynamic pricing, computer vision, atau reinforcement learning.
- Forecast kausal dan jaminan bahwa SHAP membuktikan sebab-akibat.
- Otomatis menyetujui rekomendasi tanpa manusia.
- Aplikasi mobile native dan offline-first.

## Fitur pengembangan lanjutan

1. Multi-gudang, transfer antar lokasi, batch, lot, kedaluwarsa, dan serial number.
2. Integrasi ERP/POS dan sinkronisasi terjadwal dengan rekonsiliasi.
3. Purchase order, penerimaan parsial, retur, dan portal supplier.
4. Model hierarkis/multi-produk, deteksi anomali, interval prediksi, dan monitoring drift.
5. Eksperimen model, champion/challenger, approval perubahan model, dan reproducible model registry.
6. Notifikasi email/WhatsApp/in-app dengan eskalasi SLA.
7. Forecast skenario promosi dan simulasi biaya/service level.
8. SSO, granular permission, retention policy, dan dashboard observability produksi.

## Batasan dan risiko

- Kualitas forecast dibatasi kelengkapan, granularitas, dan konsistensi histori.
- Lead time dan safety stock dapat berbeda antar supplier dan perlu governance.
- Perubahan kebijakan restock harus versioned agar keputusan lama tetap dapat direproduksi.
- Volume data dan target availability belum diketahui; target teknis di `requirements.md` adalah target awal yang harus divalidasi.
- AI memberi rekomendasi pendukung, bukan otoritas pembelian; manusia tetap accountable.

## Definition of Ready untuk Tahap Implementasi

Implementasi baru siap dimulai setelah pemilik proses menyetujui aktor, field master/transaksi, aturan saldo, horizon forecast, ambang kualitas model, aturan safety stock, dan kebijakan approval.
