# Module Structure dan Implementation Blueprint

Dokumen ini adalah blueprint untuk implementasi Laravel; belum merupakan daftar file yang sudah dibuat.

## 1. Models dan hubungan utama

| Model | Tabel | Tanggung jawab |
|---|---|---|
| User | users | identity, role, active state |
| Category | categories | master kategori |
| Product | products | SKU, unit, threshold, status |
| Supplier | suppliers | supplier dan default lead time |
| ProductSupplier | product_suppliers | atribut relasi product-supplier |
| Inventory | inventories | saldo on-hand/reserved/incoming |
| StockTransaction | stock_transactions | immutable stock ledger |
| SalesHistory | sales_history | demand/penjualan per periode |
| Forecast | forecasts | output model per product/periode |
| ForecastExplanation | forecast_explanations | SHAP factors dan limitation |
| Recommendation | recommendations | output aturan restock + snapshot |
| RecommendationDecision | recommendation_decisions | keputusan Manajer |
| AuditLog | audit_logs | jejak aktivitas |

Relationship, delete behavior, index, dan field detail ada di `docs/database.md`.

## 2. Controllers

Controller harus tipis dan mendelegasikan workflow ke Service.

- `DashboardController`: ringkasan berdasarkan role.
- `ProductController`: index/create/store/edit/update/archive/show.
- `CategoryController`: CRUD dan archive.
- `SupplierController`: CRUD dan product-supplier configuration.
- `UserController`: user management untuk Admin.
- `InventoryController`: saldo dan detail inventory.
- `StockTransactionController`: form dan posting transaksi masuk/keluar.
- `SalesHistoryController`: import/manual demand history dan history view.
- `ForecastController`: daftar/detail/run request/status.
- `ForecastExplanationController`: detail explanation dan explanation status.
- `RecommendationController`: daftar/detail dan request regeneration sesuai policy.
- `RecommendationDecisionController`: approve/reject dengan Form Request.
- `ReportController`: filter/export report dan dispatch report job.
- `AuditLogController`: filter audit bagi actor berwenang.

Jika seluruh interaksi memakai Livewire, controller dapat hanya menangani route halaman dan download; authorization tetap dipanggil oleh component/service.

## 3. Form Requests

- `LoginRequest` atau request bawaan starter kit.
- `StoreProductRequest`, `UpdateProductRequest`.
- `StoreCategoryRequest`, `UpdateCategoryRequest`.
- `StoreSupplierRequest`, `UpdateSupplierRequest`, `SyncProductSupplierRequest`.
- `StoreUserRequest`, `UpdateUserRequest`.
- `StoreStockInRequest`, `StoreStockOutRequest`, `StoreStockAdjustmentRequest`.
- `StoreSalesHistoryRequest`, `ImportSalesHistoryRequest`.
- `RunForecastRequest`, `ForecastFilterRequest`.
- `RecommendationFilterRequest`, `RecommendationDecisionRequest`.
- `ReportFilterRequest`.

Request melakukan syntax/field validation dan authorization awal; invariant lintas record tetap berada di Service dalam transaction.

## 4. Services

- `InventoryService`: query saldo/read model.
- `StockTransactionService`: lock inventory, validate balance, post ledger, idempotency, reversal.
- `DemandHistoryService`: normalisasi transaksi/demand menjadi sales history.
- `ForecastDatasetService`: snapshot dataset, quality checks, feature contract.
- `ForecastService`: create run, dispatch job, persist idempotent result, freshness.
- `AiClient`: signed HTTP/CLI adapter ke Python, timeout, schema, retry boundary.
- `ExplainabilityService`: request SHAP, validate contribution payload, persist status.
- `RestockPolicyService`: demand during lead time, safety stock, available stock, rounding.
- `RecommendationService`: snapshot parameter, generate/update status tanpa mengubah histori.
- `RecommendationDecisionService`: lock pending recommendation, approve/reject, audit.
- `ReportService`: query/report projection dan export preparation.
- `AuditLogService`: append-only event dengan redaction.
- `AuthorizationService` hanya bila rule lintas Policy memerlukan helper; jangan mengganti Policy.

## 5. Policies dan Gates

Policies minimal:

- `ProductPolicy`, `CategoryPolicy`, `SupplierPolicy`, `UserPolicy`.
- `InventoryPolicy`, `StockTransactionPolicy`, `SalesHistoryPolicy`.
- `ForecastPolicy`, `ForecastExplanationPolicy`.
- `RecommendationPolicy`, `RecommendationDecisionPolicy`.
- `ReportPolicy`, `AuditLogPolicy`.

Gate/role check hanya untuk kemampuan lintas resource seperti `view-admin-menu`, `run-forecast`, atau `manage-system-settings`. Resource authorization tetap menggunakan Policy. Semua Livewire mutation memanggil `authorize()`.

## 6. Livewire components

Nama berikut adalah kandidat namespace `App\Livewire`:

- `Dashboard\AdminDashboard`, `Dashboard\WarehouseDashboard`, `Dashboard\ManagerDashboard`.
- `Products\ProductIndex`, `ProductForm`.
- `Categories\CategoryIndex`, `CategoryForm`.
- `Suppliers\SupplierIndex`, `SupplierForm`, `ProductSupplierForm`.
- `Users\UserIndex`, `UserForm`.
- `Inventory\InventoryIndex`, `InventoryDetail`.
- `StockTransactions\StockTransactionCreate`, `StockTransactionIndex`.
- `SalesHistory\SalesHistoryIndex`, `SalesHistoryImport`.
- `Forecasts\ForecastIndex`, `ForecastDetail`, `ForecastRunStatus`.
- `Recommendations\RecommendationIndex`, `RecommendationDetail`, `RecommendationDecisionModal`.
- `Reports\ReportIndex`, `ReportExportStatus`.
- `Audit\AuditLogIndex`.

Komponen hanya menyimpan state presentasi/filter/form. Query dan perubahan penting memakai Service serta Policy.

## 7. Jobs dan events

Jobs utama:

- `PrepareForecastDatasetJob`
- `RunForecastInferenceJob`
- `GenerateForecastExplanationJob`
- `BuildRestockRecommendationsJob`
- `GenerateInventoryReportJob`
- `ImportSalesHistoryChunkJob`
- `PruneExpiredArtifactsJob`

Event kandidat: `StockTransactionPosted`, `ForecastCompleted`, `RecommendationGenerated`, `RecommendationDecided`. Listener audit dan refresh projection harus idempotent.

## 8. Scheduled commands

- `forecast:prepare` — membuat run sesuai produk yang eligible.
- `forecast:dispatch` — mengirim job inference yang belum berjalan.
- `recommendations:generate` — menghitung rekomendasi dari forecast eligible terbaru.
- `inventory:check-integrity` — mendeteksi saldo tidak konsisten, transaksi duplikat, atau stok negatif.
- `reports:cleanup` — membersihkan artifact yang melewati retention.
- `ai:check-health` — memeriksa versi/health AI runtime tanpa mengirim data bisnis.

Jadwal dan timezone harus dikonfigurasi setelah SLA bisnis disepakati. Command tidak boleh mengubah histori secara destruktif.

## 9. Konfigurasi dan error boundary

- `config/ai.php`: endpoint/CLI, timeout, schema version, model alias, retry, dan artifact storage; secret melalui `.env`.
- Domain exceptions: `InsufficientStockException`, `DuplicateTransactionException`, `InsufficientForecastDataException`, `AiServiceException`, `InvalidRecommendationStateException`.
- HTTP/Livewire menampilkan pesan umum berbahasa Indonesia; detail exception masuk structured log dengan correlation ID.
- Semua external AI response divalidasi sebelum masuk model database.

## 10. Batas implementasi tahap berikutnya

Blueprint ini belum mengunci nama route, detail migration, package UI, atau algoritma model. Keputusan tersebut memerlukan Tahap 3/4 dan tidak boleh diasumsikan sebagai implementasi yang sudah tersedia.

## 11. Status frontend Tahap 6

Komponen Livewire MVP yang sudah diimplementasikan: `Dashboard\Overview`, `Catalog\ProductManager`, `Catalog\SupplierManager`, `Inventory\InventoryManager`, `Transactions\StockTransactionManager`, `Reports\ReportOverview`, dan `Users\UserManager`. Layout navigasi menggunakan Blade + Volt logout action, Tailwind CSS, dan Chart.js. Modul Forecast/XAI/Recommendation belum memiliki data atau workflow dan hanya memakai halaman placeholder.
