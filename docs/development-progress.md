# Development Progress — StockWise AI

## Status saat ini

Status terbaru: **Tahap 10 — Production Preparation selesai pada level artefak dan dokumentasi**, tetapi belum live; validasi staging/infrastruktur masih menunggu.

**Tahap 6 — Frontend Implementation: selesai implementasi MVP, menunggu validasi visual pada browser/device nyata.**

Tanggal: 2026-10-20  
Repository: `D:\stockwise_ai`

Audit independen terakhir: Tahap 10 siap untuk validasi staging, belum production-ready/LIVE. Lihat `docs/stage10-audit-report.md`.

## Roadmap

| Tahap | Nama | Status |
|---:|---|---|
| 0 | Master Instruction dan repository baseline | Selesai sebagian; repository Laravel terdeteksi, dokumentasi dasar dilengkapi pada tahap ini |
| 1 | Analisis Kebutuhan | Selesai draft; menunggu validasi requirement, aturan restock, dan dataset |
| 2 | Perancangan Arsitektur | Selesai draft; menunggu validasi arsitektur, ERD, dan aturan inventory |
| 3 | UI/UX Design | Belum dimulai |
| 4 | Laravel Setup | Belum dimulai |
| 5 | Database dan Backend | Selesai implementasi MVP backend; menunggu validasi MySQL development |
| 6 | Frontend | Selesai implementasi MVP; menunggu validasi visual browser/device |
| 7 | Machine Learning | Selesai implementasi teknis; menunggu dataset bisnis tervalidasi |
| 8 | Explainable AI dan Decision Support | Selesai implementasi MVP; menunggu validasi dataset bisnis/MySQL |
| 9 | Testing | Selesai dengan batasan environment; lihat `test-results.md`, `security-review.md`, dan `known-issues.md` |
| 10 | Production Preparation and Deployment | Selesai persiapan deployment; belum live dan menunggu staging/infrastruktur |

## Hasil Tahap 1

- Analisis masalah stockout, overstock, pembelian tidak tepat, kurangnya prediksi, dan kurangnya transparansi.
- Tujuan, manfaat, aktor, persona, dan hak akses.
- Tabel kebutuhan fungsional `FR-001` sampai `FR-021`.
- Tabel kebutuhan nonfungsional `NFR-001` sampai `NFR-012`.
- User stories dengan acceptance criteria.
- Fokus keputusan: kapan reorder dan berapa jumlah rekomendasinya.
- Kebutuhan dataset, aturan kualitas, status ketersediaan data, opsi publik/sintetis, dan batasan.
- Batasan MVP serta kandidat fitur lanjutan.

## Temuan baseline repository

- Aplikasi Laravel 12 dengan PHP `^8.2` terdeteksi dari `composer.json`.
- Dependency Composer sudah tersedia di `vendor/`.
- Repository belum memiliki `.git` pada folder proyek saat pemeriksaan.
- Hanya migration bawaan users, cache, dan jobs yang tersedia; modul inventory belum dibangun.
- `README.md` masih README bawaan Laravel.
- Sebelum Tahap 2, requirement perlu disahkan dan lokasi/dataset historis perlu dikonfirmasi.

## Verifikasi Tahap 1

- Pemeriksaan struktur repository dan file konfigurasi: berhasil.
- Pemeriksaan status Git: tidak tersedia karena `D:\stockwise_ai` bukan Git repository.
- Pengujian aplikasi: tidak dijalankan karena tahap ini tidak mengubah kode aplikasi.
- Validasi markdown dan review isi dokumen: dilakukan secara manual melalui file yang dibuat.

## Keputusan dan risiko terbuka

- Tidak mengarang hasil forecast ketika dataset belum tersedia.
- XAI akan menjadi penjelasan perilaku model, bukan klaim sebab-akibat.
- Ambang minimum data, horizon, safety stock, service level, dan SLA harus diputuskan sebelum implementasi AI.
- Tidak ada implementasi aplikasi atau migrasi database pada tahap ini.

## Hasil Tahap 2

- Arsitektur Laravel monolith modular dan batas isolasi Python AI.
- Kontrak komunikasi Laravel–Python yang aman serta desain queue AI.
- ERD MySQL untuk users, master data, inventory, transaksi, demand history, forecast, XAI, rekomendasi, keputusan, dan audit.
- Primary key, foreign key, indexes, unique constraints, status, timestamp, dan delete rules.
- Aturan quantity, on-hand/available/reserved/incoming, atomic update, retur, koreksi, reversal, idempotency, dan pembentukan demand history.
- Diagram Mermaid use case, aktivitas, sequence approval, dan sitemap.
- Blueprint Models, Controllers, Form Requests, Services, Policies, Livewire Components, Jobs, dan Scheduled Commands.

## Verifikasi Tahap 2

- Semua dokumen requirement Tahap 1 dan `AGENTS.md` dibaca sebelum desain.
- Lima output desain dibuat: `architecture.md`, `database.md`, `diagrams.md`, `sitemap.md`, `module-structure.md`.
- Konsistensi ERD dan daftar Eloquent relationship direview manual.
- Tidak ada migration, model, service, queue, atau kode aplikasi yang dibuat.
- Tidak ada test aplikasi yang dijalankan karena perubahan hanya dokumentasi.
- Revalidasi ulang pada 2026-10-20: seluruh dokumen input/output tersedia dan baseline Laravel 12/PHP 8.2 tetap konsisten.

## Keputusan dan risiko terbuka Tahap 2

- Laravel tetap menjadi pemilik authentication, authorization, database, inventory, dan workflow approval.
- Python tidak memiliki akses langsung ke MySQL produksi dan tidak menghitung saldo stok.
- Detail horizon forecast, safety stock, service level, unit conversion, SLA, dan ambang kelayakan model masih memerlukan keputusan bisnis.
- Pilihan private HTTP service versus CLI lokal dapat dipentaskan; kontrak AI dibuat sama untuk keduanya.
- Migration database dan implementasi modul ditunda ke tahap berikutnya sesuai instruksi.

## Hasil Tahap 5

- Laravel Breeze + Livewire/Volt dipasang sebagai authentication starter kit resmi; route auth terlindungi middleware `auth` dan `verified`.
- Role `admin`, `warehouse_staff`, dan `manager`, middleware `role`, Policies, Form Requests, dan pembatasan user aktif diterapkan.
- Migration dan Eloquent Models domain inventory tersedia dengan foreign key, index, unique constraint, decimal quantity/money, status, dan timestamp.
- Factory development/test dan `DevelopmentSeeder` terpisah; password demo wajib berasal dari `STOCKWISE_DEMO_PASSWORD` dan tidak disimpan di repository.
- CRUD backend kategori, produk, supplier, relasi product-supplier, inventory summary, stock transaction history, dan audit log tersedia melalui route internal terautentikasi.
- `StockTransactionService` menggunakan DB transaction, row locking, idempotency key, pencegahan stok negatif, reversal, dan audit.
- Tidak ada implementasi Machine Learning pada tahap ini.

## Verifikasi Tahap 5

- PHP syntax check: 92 file berhasil.
- `php artisan test`: 36 test lulus, 101 assertions.
- Test inventory backend mencakup login, user nonaktif, authorization, CRUD/relationship, receipt, issue, stok negatif, idempotency, serialisasi lock, rollback, dan reversal audit.
- `php artisan migrate --pretend --env=testing`: SQL migration berhasil digenerate tanpa menulis database.
- `php artisan route:list --path=internal`: 22 route internal terdaftar.
- Database produksi/development tidak di-reset dan tidak dijalankan `migrate:fresh`.
- Risiko terbuka: pengujian benar-benar paralel lintas proses memerlukan harness database/CI khusus; test saat ini memvalidasi serialisasi lock dan invariant saldo pada test database.

## Hasil Tahap 6

- Layout SaaS responsif dengan sidebar, topbar mobile, breadcrumb context melalui heading halaman, profile dropdown, logout, active navigation, dan role-aware navigation.
- Dashboard Livewire mengambil metrik produk, persediaan, transaksi, audit activity, dan tren 14 hari dari database; Chart.js digunakan untuk grafik.
- Halaman Livewire Produk, Supplier, Persediaan, Transaksi Stok, Laporan, dan Pengguna tersedia dengan search/filter/pagination/form/modal/loading state/empty state sesuai modul.
- Stock transaction form memakai konfirmasi, loading disabled, service inventory yang sudah ada, dan pesan error domain.
- Forecast AI dan Rekomendasi Restock hanya menampilkan placeholder “Fitur dalam pengembangan”; tidak ada prediksi palsu.
- Vite build berhasil dengan Chart.js.

## Verifikasi Tahap 6

- `npm.cmd run build`: berhasil; asset production dibuat.
- `php artisan test`: suite backend/auth tetap lulus dan test frontend ditambahkan.
- `FrontendPagesTest`: 5 test lulus, 23 assertions.
- Responsive browser/device testing manual belum dijalankan karena tidak ada browser automation tooling aktif; layout menggunakan breakpoint Tailwind dan perlu validasi visual sebelum production.
- `npm audit` melaporkan 7 vulnerability dari dependency frontend existing/install; belum dijalankan `npm audit fix` otomatis agar tidak mengubah dependency tanpa review.

## Hasil Tahap 7

- Audit dataset: belum ada file dataset bisnis; database lokal menggunakan SQLite dan migration domain masih pending. Tidak ada klaim evaluasi bisnis.
- `ai-service/` mencakup preprocessing, audit, generator simulasi, training, inference, artifact metadata, dan internal HTTP service localhost.
- Preprocessing mencakup agregasi harian, missing date flag, pembedaan zero demand vs missing, stockout censoring flag, fitur kalender, lag, rolling, dan leakage prevention.
- Baseline naive/seasonal-naive, Random Forest, dan XGBoost opsional dibandingkan per SKU dengan MAE, RMSE, WAPE, dan bias.
- Forecast horizon 7/30 hari memakai recursive inference, non-negative output, model version, data-as-of, serta status insufficient-data.
- Laravel `AiForecastClient` memakai HMAC timestamp/nonce, timeout/retry; `RunForecastInferenceJob` mengambil `sales_history` dan menyimpan hasil valid ke `forecasts` tanpa training pada web request.
- Halaman Forecasting Livewire hanya menampilkan hasil yang benar-benar tersimpan dan tidak menampilkan nilai prediksi palsu.

## Verifikasi Tahap 7

- `ai-service`: 8 pytest lulus untuk preprocessing, missing/zero distinction, leakage, training/artifact, inference horizon 7/30, non-negative output, sparse product, serta HMAC internal HTTP service.
- Smoke training berhasil pada dataset SIMULASI 4 SKU/240 hari; metrik dicatat di `docs/ml-evaluation.md` dan bukan bukti performa bisnis.
- Laravel `MachineLearningForecastTest`: 4 test lulus untuk unauthorized staff, manager scheduling, HTTP inference save, dan insufficient-data tanpa forecast palsu.
- Migration database tidak dijalankan/di-reset; `php artisan migrate:status` menunjukkan migration domain masih pending pada SQLite lokal.
- XAI/SHAP dan rekomendasi restock belum diimplementasikan; tahap berhenti sebelum Tahap 8.

## Risiko dan keputusan Tahap 7

- Dataset sintetis dan artifact development di-ignore dari version control; production wajib menggunakan data internal tervalidasi dan secret dari environment.
- Service default bind localhost; production memerlukan private network, HTTPS/mTLS atau service identity, secret manager, dan observability.
- Walk-forward validation, interval ketidakpastian, censored-demand correction, drift monitoring, dan threshold promosi model masih memerlukan dataset serta keputusan bisnis.
- Karena MySQL belum dikonfigurasi/dijalankan pada environment ini, integrasi database produksi belum diverifikasi terhadap MySQL engine.

## Hasil Tahap 8

- SHAP `TreeExplainer` terintegrasi untuk model tree dan memvalidasi konsistensi baseline + kontribusi dengan output model.
- Explanation menyimpan local contribution, global importance, baseline, reconstructed output, model version, explainer version, dan limitation note.
- Endpoint internal `/v1/explain` dan `GenerateForecastExplanationJob` menyimpan hasil ke `forecast_explanations`; kegagalan service ditandai failed tanpa data explanation palsu.
- Dashboard Explainable AI menyediakan selector produk/periode, chart kontribusi, fitur positif/negatif, baseline, ringkasan bahasa natural, dan keterbatasan model.
- `RestockDecisionService` menerapkan demand lead time, reorder point, safety stock, inventory position, target, MOQ, order multiple, expected receipt, dan risk classification.
- Dashboard rekomendasi menampilkan perhitungan serta rationale dan tidak membuat purchase order otomatis.
- `RecommendationDecisionService` menerapkan approval/rejection manager, penyesuaian quantity, row lock, unique decision, snapshot, dan audit log.
- Dokumentasi metodologi tersedia di `docs/xai-decision-support.md`.

## Verifikasi Tahap 8

- Python: 10 pytest lulus, termasuk rekonstruksi SHAP dan sparse/invalid model handling.
- Laravel: 50 test lulus dengan 140 assertions, termasuk formula restock, MOQ/order multiple, invalid lead time, authorization, approval atomic, duplicate approval, audit, dan explanation persistence.
- Blade view cache berhasil dan Vite production build berhasil.
- Migration `global_importance` ditambahkan tetapi tidak dijalankan pada database lokal pengguna; tidak ada `migrate:fresh` atau penghapusan data.

## Risiko dan keputusan Tahap 8

- SHAP menjelaskan perilaku model, bukan sebab-akibat; kontribusi dapat berubah karena korelasi fitur dan data drift.
- Recommendation memakai forecast yang tersedia; jika horizon lebih pendek dari lead time, sisa demand diekstrapolasi dengan rata-rata forecast dan dicatat pada snapshot.
- Approval hanya mengubah status dan audit; integrasi procurement/purchase order sengaja belum dibuat.
- Dataset bisnis dan validasi MySQL produksi belum tersedia; smoke test memakai data sintetis Tahap 7.

## Hasil Tahap 9

- Laravel test suite lulus 51 test dengan 141 assertions setelah memperbaiki routing endpoint explainability pada `AiForecastClient`.
- Python suite lulus 10 pytest; preprocessing, leakage prevention, inference horizon, sparse/empty data, model versioning, HMAC service, dan rekonstruksi SHAP tervalidasi.
- PHP lint 123 file lulus, migration SQL berhasil dipreview tanpa menulis database, 22 internal route terdaftar, dan Vite production build berhasil.
- Review keamanan mencakup injection, XSS, CSRF, authorization/IDOR, session/password, secret handling, AI authentication, dan rate limiting.
- Dokumen test plan, hasil, security review, dan known issues dibuat di `docs/`.

## Batasan Tahap 9

- MySQL, browser/device automation, dependency registry audit, multi-process concurrency, dan E2E browser penuh belum tersedia pada environment ini.
- Tidak ada deployment, `migrate:fresh`, penghapusan data, atau perubahan database produksi dilakukan.

## Hasil Tahap 10

- Template `.env.production.example` dibuat dengan `APP_ENV=production`, `APP_DEBUG=false`, HTTPS/secure session, MySQL, database queue/cache, dan AI secret placeholders tanpa secret nyata.
- `ai-service/requirements.lock` dibuat berdasarkan versi Python yang lulus pada environment Tahap 9; model artifact, model version, dan feature contract tetap wajib diverifikasi pada staging.
- Deployment guide, production checklist, maintenance guide, backup/recovery runbook, user manual, dan admin manual tersedia di `docs/`.
- Script `scripts/backup-mysql.ps1` dibuat untuk logical backup non-destructive menggunakan `mysqldump --single-transaction`; script tidak dijalankan terhadap database produksi.
- `php artisan optimize` awalnya menemukan collision nama route antara halaman Livewire dan API internal. Route group API diperbaiki dengan namespace `internal.*`; route cache kemudian berhasil dibuat.
- Laravel test, Python pytest, PHP lint, Composer validation, Vite build, migration preview, route listing, PowerShell parser, dan AI Python compile validation berhasil.

## Batasan Tahap 10

- Tidak ada akses hosting, MySQL staging/production, HTTPS certificate, secret manager, process manager, atau production URL. Website belum live dan tidak boleh dinyatakan live.
- Backup/restore drill, MySQL migration execution, queue worker nyata, AI service private deployment, health checks lintas host, dan full production smoke test masih menunggu staging.
- Scheduler belum memiliki recurring business command; dokumentasi hanya menyiapkan mekanisme cron/schedule setelah command operasional disetujui.

## Audit follow-up Tahap 10

- Ditemukan bug guest pada route root `/`: route merender view dashboard tanpa middleware auth sehingga navigation memanggil `hasRole()` pada user null.
- Root route dikembalikan ke `welcome`, regression test diperketat, dan suite Laravel penuh lulus ulang: 51 test, 142 assertions.
- `php artisan optimize` lulus setelah perbaikan; audit lengkap tersedia di `docs/stage10-audit-report.md`.

## Update landing page publik

- Halaman root `/` diubah dari starter page Laravel menjadi landing page StockWise AI berbahasa Indonesia.
- Landing page menjelaskan alur pencatatan persediaan, forecasting, Explainable AI, dan approval workflow dengan CTA menuju aplikasi.
- Angka pada panel hero diberi label sebagai ilustrasi alur workspace; data operasional aktual tetap ditampilkan setelah pengguna masuk.
- Halaman dibuat responsif untuk desktop, tablet, dan mobile menggunakan Blade/Tailwind tanpa menambah fitur operasional di luar scope.
- Verifikasi setelah perubahan: 51 test Laravel lulus dengan 142 assertions, Vite production build lulus, dan Blade view cache lulus.

## Update halaman login StockWise

- Layout guest bawaan Laravel/Breeze diganti dengan shell StockWise AI yang responsif dan konsisten dengan landing page publik.
- Halaman login sekarang tampil sebagai modal Alpine/JavaScript dengan animasi, state open, `x-cloak`, dan kontrol kembali ke beranda.
- Form login diperbarui ke Bahasa Indonesia, memiliki toggle tampil/sembunyi password, loading state, error state, remember me, serta tautan pemulihan akses.
- Autentikasi tetap menggunakan Livewire `LoginForm`, rate limiting, validasi server-side, regenerasi session, dan redirect intended; perubahan hanya pada presentasi/interaksi.
- Verifikasi perubahan: test autentikasi Laravel, Blade cache, dan Vite production build dijalankan.
- Login juga tersedia sebagai modal langsung dari CTA landing page; klik tautan `/login` pada landing page dicegat Alpine agar tidak meninggalkan halaman, dengan backdrop, close button, Escape, dan Livewire form yang sama.
- Ikon karakter pada login modal diganti dengan Bootstrap Icons; stylesheet ikon dimuat pada halaman guest dan landing page.
- “Lupa password?” kini membuka modal pemulihan password Livewire di atas landing page; kontrol password menggunakan tombol ikon `bi-eye`/`bi-eye-slash`.
- Navigasi publik “Cara kerja”, “Kapabilitas”, dan “Prinsip” diarahkan eksplisit ke section terkait pada root `/`, dengan smooth scrolling.
- Tombol CTA “Pelajari alurnya” pada hero dihapus; section “Cara kerja” tetap dapat dibuka dari navigasi utama.
- Seeder `SuperadminDemoSeeder` ditambahkan untuk membuat akun dummy ber-role `admin` secara idempotent menggunakan password dari `STOCKWISE_SUPERADMIN_PASSWORD`.
- Tombol “Mulai eksplorasi” pada header landing page dihapus; tautan pendaftaran di modal login tetap tersedia.
- Responsivitas mobile diperkuat: tombol “Masuk” tetap tersedia di header layar kecil, section memakai layout breakpoint Tailwind, dan modal login/reset memakai lebar penuh dengan padding aman untuk viewport sempit.
- Toast global bergaya iOS ditambahkan untuk event Livewire `notify` dan flash session; mendukung success, warning, error, auto-dismiss, close manual, login, logout, save, archive, dan action operasional lain yang sudah mengirim event.
- Route `/internal/audit-logs` diperbaiki menjadi halaman Livewire audit log untuk browser; endpoint JSON dipisahkan ke `/internal/audit-logs/data`.
- Seeder `AuditLogDemoSeeder` ditambahkan dengan 10 aktivitas demo idempotent untuk mengisi halaman audit log saat development.
- Menu Forecast AI dan Rekomendasi Restock tidak lagi diberi label “Soon”; keduanya sudah memiliki workflow Livewire untuk inference queue, SHAP explanation, dan aturan restock yang terpisah dari ML.
- Konfigurasi `ai-service/pytest.ini` ditambahkan agar package AI terdeteksi konsisten saat test dijalankan dari service directory atau CI.
- Validasi AI terbaru: 10 pytest lulus menggunakan artifact lokal; Laravel/frontend dan Vite tetap lulus. Hosting production masih memerlukan dataset historis nyata, `STOCKWISE_AI_SECRET`, AI service process, queue worker, MySQL, dan konfigurasi HTTPS/secret manager.
- Seeder `AiDemoDataSeeder` ditambahkan untuk development: 90 baris sales history, 7 forecast, 1 explanation, dan rekomendasi restock dengan metadata `demo`/`demo-baseline`; bukan hasil AI produksi.
