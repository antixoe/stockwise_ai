# Database Design — MySQL

## 1. Prinsip desain

- Engine InnoDB, charset `utf8mb4`, foreign key aktif, timezone aplikasi terdokumentasi.
- Primary key menggunakan `BIGINT UNSIGNED` auto-increment untuk tabel domain; `UUID/ULID` dapat dipakai sebagai public identifier tanpa mengekspos ID internal.
- Semua tabel domain memiliki `created_at` dan `updated_at`; tabel append-only tetap memiliki `created_at` dan `updated_at` nullable hanya jika diperlukan koreksi metadata.
- Histori transaksi, forecast, keputusan, dan audit tidak dihapus secara fisik dalam alur normal.
- Nilai kuantitas disimpan sebagai `DECIMAL(18,3)` agar mendukung unit pecahan; jika bisnis hanya unit bulat, validasi domain membatasi scale menjadi 0.
- Nilai uang memakai `DECIMAL(19,4)` dan kode ISO currency bila transaksi lintas mata uang diperlukan.

## 2. ERD

```mermaid
erDiagram
    USERS ||--o{ STOCK_TRANSACTIONS : records
    USERS ||--o{ RECOMMENDATION_DECISIONS : decides
    USERS ||--o{ AUDIT_LOGS : performs
    CATEGORIES ||--o{ PRODUCTS : contains
    PRODUCTS ||--|| INVENTORIES : has
    PRODUCTS ||--o{ PRODUCT_SUPPLIERS : sourced_by
    SUPPLIERS ||--o{ PRODUCT_SUPPLIERS : supplies
    PRODUCTS ||--o{ STOCK_TRANSACTIONS : moves
    SUPPLIERS ||--o{ STOCK_TRANSACTIONS : receives_from
    PRODUCTS ||--o{ SALES_HISTORY : demanded
    PRODUCTS ||--o{ FORECASTS : forecasted
    FORECASTS ||--o| FORECAST_EXPLANATIONS : explained_by
    PRODUCTS ||--o{ RECOMMENDATIONS : suggested_for
    FORECASTS ||--o{ RECOMMENDATIONS : supports
    RECOMMENDATIONS ||--o| RECOMMENDATION_DECISIONS : decided
    STOCK_TRANSACTIONS ||--o| STOCK_TRANSACTIONS : reverses

    USERS {
        bigint id PK
        string name
        string email UK
        string password
        enum role
        boolean is_active
        datetime email_verified_at nullable
        datetime created_at
        datetime updated_at
    }
    CATEGORIES {
        bigint id PK
        string name UK
        string code UK nullable
        text description nullable
        boolean is_active
        datetime created_at
        datetime updated_at
    }
    PRODUCTS {
        bigint id PK
        bigint category_id FK
        string sku UK
        string name
        string unit
        decimal unit_price
        decimal reorder_point
        decimal safety_stock
        decimal minimum_order_quantity
        decimal order_multiple
        boolean is_active
        datetime created_at
        datetime updated_at
    }
    SUPPLIERS {
        bigint id PK
        string code UK
        string name
        string contact_name nullable
        string email nullable
        string phone nullable
        decimal default_lead_time_days nullable
        boolean is_active
        datetime created_at
        datetime updated_at
    }
    PRODUCT_SUPPLIERS {
        bigint id PK
        bigint product_id FK
        bigint supplier_id FK
        string supplier_sku nullable
        decimal lead_time_days
        decimal minimum_order_quantity nullable
        decimal order_multiple nullable
        decimal last_unit_cost nullable
        boolean is_primary
        datetime created_at
        datetime updated_at
    }
    INVENTORIES {
        bigint id PK
        bigint product_id FK UK
        decimal on_hand_quantity
        decimal reserved_quantity
        decimal incoming_quantity
        bigint version
        datetime created_at
        datetime updated_at
    }
    STOCK_TRANSACTIONS {
        bigint id PK
        bigint product_id FK
        bigint supplier_id FK nullable
        bigint user_id FK
        bigint reversal_of_id FK nullable
        string idempotency_key UK
        enum type
        enum status
        decimal quantity
        decimal unit_cost nullable
        date occurred_at
        string reference nullable
        text notes nullable
        json metadata nullable
        datetime posted_at nullable
        datetime created_at
        datetime updated_at
    }
    SALES_HISTORY {
        bigint id PK
        bigint product_id FK
        date demand_date
        decimal quantity_demanded
        decimal quantity_sold nullable
        decimal unit_price nullable
        boolean promotion_flag nullable
        decimal stock_available nullable
        string source
        json features nullable
        datetime created_at
        datetime updated_at
    }
    FORECASTS {
        bigint id PK
        bigint product_id FK
        string run_uuid
        date forecast_date
        date data_as_of
        decimal predicted_quantity
        decimal lower_bound nullable
        decimal upper_bound nullable
        string unit
        string model_name
        string model_version
        string horizon
        enum status
        decimal mae nullable
        decimal wape nullable
        json input_snapshot nullable
        datetime created_at
        datetime updated_at
    }
    FORECAST_EXPLANATIONS {
        bigint id PK
        bigint forecast_id FK UK
        string explainer_name
        string explainer_version nullable
        json feature_contributions
        json base_value nullable
        enum status
        text limitation_note nullable
        datetime created_at
        datetime updated_at
    }
    RECOMMENDATIONS {
        bigint id PK
        bigint product_id FK
        bigint forecast_id FK nullable
        bigint supplier_id FK nullable
        string recommendation_uuid UK
        enum status
        date recommended_order_date
        date expected_receipt_date nullable
        decimal reorder_quantity
        decimal demand_during_lead_time
        decimal on_hand_at_calculation
        decimal reserved_at_calculation
        decimal incoming_at_calculation
        decimal safety_stock_at_calculation
        decimal lead_time_days
        string rule_version
        json input_snapshot
        text rationale
        datetime generated_at
        datetime created_at
        datetime updated_at
    }
    RECOMMENDATION_DECISIONS {
        bigint id PK
        bigint recommendation_id FK UK
        bigint user_id FK
        enum decision
        text comment nullable
        json decision_snapshot
        datetime decided_at
        datetime created_at
        datetime updated_at
    }
    AUDIT_LOGS {
        bigint id PK
        bigint user_id FK nullable
        string action
        string auditable_type nullable
        bigint auditable_id nullable
        string request_id nullable
        string ip_address nullable
        string user_agent nullable
        json old_values nullable
        json new_values nullable
        datetime created_at
        datetime updated_at
    }
```

## 3. Tabel dan aturan kolom

### `users`

Memakai tabel Laravel bawaan, ditambah `role` (`admin`, `warehouse_staff`, `manager`) dan `is_active`. `email` unique. Deaktivasi lebih aman daripada delete; foreign key transaksi/keputusan memakai `RESTRICT` atau `SET NULL` sesuai histori.

### `categories`, `products`, `suppliers`

Master data memakai status `is_active`, bukan hard delete untuk record yang sudah dipakai. `products.category_id` `RESTRICT` saat kategori dipakai. `products.sku`, category code/name, dan supplier code memiliki unique constraint sesuai kebijakan case-insensitive collation.

### `product_suppliers`

Pivot wajib untuk supplier-product karena lead time, MOQ, order multiple, dan cost dapat berbeda per pasangan. Unique `(product_id, supplier_id)`. Hanya satu `is_primary` per product dijaga oleh service/constraint strategy; jika MySQL partial unique tidak dipakai, validasi dilakukan dalam transaction.

### `inventories`

Satu baris per product (`UNIQUE product_id`). `on_hand_quantity`, `reserved_quantity`, dan `incoming_quantity` tidak boleh negatif. `version` membantu optimistic check; row lock tetap menjadi mekanisme utama saat posting transaksi.

### `stock_transactions`

Append-only ledger. `type`: `receipt`, `issue`, `return_in`, `return_out`, `adjustment_in`, `adjustment_out`, `reservation`, `release`. `status`: `posted`, `reversed`, `cancelled`. `quantity` selalu positif; arah ditentukan oleh type. `idempotency_key` wajib unik untuk mencegah request ganda. `reversal_of_id` self-reference dan `ON DELETE RESTRICT`.

### `sales_history`

Satu baris per product/date/source/granularity; unique `(product_id, demand_date, source)`. `quantity_demanded` adalah target demand yang disepakati, sedangkan `quantity_sold` adalah observasi penjualan. `stock_available` membantu menandai censored demand saat stockout.

### `forecasts` dan `forecast_explanations`

Forecast menyimpan satu baris per product/run/forecast date; unique `(run_uuid, product_id, forecast_date)`. `status`: `queued`, `running`, `eligible`, `insufficient_data`, `failed`, `superseded`. Explanations one-to-one; `status`: `pending`, `available`, `failed`, dan wajib mereferensikan model/explainer version.

### `recommendations` dan `recommendation_decisions`

Recommendation menyimpan snapshot input/rule agar hasil immutable secara audit. `status`: `pending`, `approved`, `rejected`, `expired`, `superseded`, `not_eligible`. Decision one-to-one (`UNIQUE recommendation_id`) pada MVP; `decision`: `approved` atau `rejected`. Jika kelak perlu re-approval, tambahkan decision history terpisah tanpa mengubah record lama.

### `audit_logs`

Append-only. `user_id` nullable untuk event sistem/job. Polymorphic target nullable mendukung login atau event tanpa model. JSON dibatasi agar tidak menyimpan password, token, atau payload rahasia.

## 4. Foreign key, delete, dan index

| Relasi | FK behavior | Index/constraint |
|---|---|---|
| products → categories | `RESTRICT` | `products.category_id`, unique SKU |
| product_suppliers → products/suppliers | `RESTRICT` | unique pair, both FK indexes |
| inventories → products | `CASCADE` hanya bila product belum punya histori; praktik produksi `RESTRICT` | unique product_id |
| stock_transactions → products/users/suppliers | product/user `RESTRICT` atau user `SET NULL` sesuai audit policy; supplier `SET NULL` | product+occurred_at, type+status, idempotency unique |
| sales_history → products | `RESTRICT` | product+date, unique product/date/source |
| forecasts → products | `RESTRICT` | product+forecast_date, run_uuid, status |
| explanations → forecasts | `CASCADE` hanya bila forecast dihapus oleh retention job | unique forecast_id |
| recommendations → products/forecasts/suppliers | `RESTRICT` untuk product; `SET NULL` untuk forecast/supplier bila policy mengizinkan | product+status, order date+status, UUID unique |
| decisions → recommendations/users | `RESTRICT` | unique recommendation_id, user+decided_at |
| audit_logs → users | `SET NULL` | user+created_at, action+created_at, polymorphic target |

Index tambahan dibuat berdasarkan query nyata dan diuji dengan `EXPLAIN`; jangan menambah index lebar pada JSON tanpa kebutuhan.

## 5. Eloquent relationships

- `User`: `hasMany(StockTransaction)`, `hasMany(RecommendationDecision)`, `hasMany(AuditLog)`.
- `Category`: `hasMany(Product)`.
- `Product`: `belongsTo(Category)`, `belongsToMany(Supplier)->withPivot(...)`, `hasOne(Inventory)`, `hasMany(StockTransaction)`, `hasMany(SalesHistory)`, `hasMany(Forecast)`, `hasMany(Recommendation)`.
- `Supplier`: `belongsToMany(Product)->withPivot(...)`, `hasMany(StockTransaction)`, `hasMany(Recommendation)`.
- `Inventory`: `belongsTo(Product)`.
- `StockTransaction`: `belongsTo(Product/User/Supplier)`, `belongsTo(ReversalOf)`.
- `SalesHistory`: `belongsTo(Product)`.
- `Forecast`: `belongsTo(Product)`, `hasOne(ForecastExplanation)`, `hasMany(Recommendation)`.
- `ForecastExplanation`: `belongsTo(Forecast)`.
- `Recommendation`: `belongsTo(Product/Forecast/Supplier)`, `hasOne(RecommendationDecision)`.
- `RecommendationDecision`: `belongsTo(Recommendation/User)`.
- `AuditLog`: `belongsTo(User)`, optional `morphTo(auditable)`.

Field status dan JSON memakai PHP enum/cast pada model; enum database atau varchar terkontrol dipilih konsisten saat implementasi setelah validasi MySQL deployment.

## 6. Inventory rules

### Quantity, unit, dan pembulatan

- Setiap product memiliki satu `unit` resmi (misalnya `pcs`, `box`, `kg`, atau `liter`); transaksi lintas unit memerlukan konversi eksplisit yang belum termasuk MVP.
- Kuantitas input dan saldo menggunakan unit produk, `DECIMAL(18,3)`, dan harus `> 0` pada ledger.
- `reorder_quantity` dihitung sebagai kebutuhan bersih lalu dibulatkan ke atas: minimal `minimum_order_quantity`, kemudian kelipatan `order_multiple`. Untuk unit diskrit, hasil akhir harus bilangan bulat.
- Pembulatan dilakukan hanya pada output order, bukan pada demand history atau saldo transaksi.

### Definisi saldo

- **On-hand:** barang fisik tercatat berada di gudang; kolom `inventories.on_hand_quantity`.
- **Reserved:** bagian on-hand yang sudah dialokasikan untuk order/operasi namun belum keluar.
- **Available:** `on_hand - reserved`; tidak boleh negatif.
- **Incoming:** kuantitas yang sudah dipesan/diantisipasi masuk tetapi belum diterima; tidak menambah on-hand sebelum receipt posted.

Kalkulasi keputusan menggunakan `available + incoming` sesuai rule yang disetujui; stok incoming tidak boleh dipakai untuk memenuhi transaksi keluar sebelum diterima.

### Atomic stock update

`StockTransactionService` membuka `DB::transaction`, mencari satu row `inventories` dengan `lockForUpdate()`, memeriksa idempotency key, menghitung saldo baru, menulis ledger, memperbarui inventory, menulis audit, lalu commit. Deadlock dapat di-retry terbatas dengan urutan lock product ID yang konsisten. Tidak ada update saldo langsung dari controller, Livewire, atau Python.

### Retur, koreksi, dan pembatalan

- **Return in:** barang kembali dari pihak luar dan layak stok; menambah on-hand dengan reference transaksi asal.
- **Return out:** barang dikembalikan ke supplier; mengurangi on-hand hanya jika cukup.
- **Adjustment:** hanya actor berwenang, wajib alasan dan evidence/reference; memakai `adjustment_in`/`adjustment_out` dan masuk audit.
- **Cancellation:** transaksi posted tidak diedit atau dihapus. Pembatalan membuat reversal transaction yang menunjuk `reversal_of_id`, lalu status asal menjadi `reversed` dalam satu transaction.
- Retur barang rusak/karantina memerlukan status lokasi/batch pada pengembangan lanjutan; MVP harus mencatatnya di metadata dan kebijakan bisnis.

### Pencegahan duplikasi dan saldo ilegal

- `idempotency_key` unique dan harus berasal dari request/reference yang stabil.
- Unique reference bisnis dapat ditambahkan bila sumber transaksi menjamin keunikan.
- Issue/return-out ditolak jika `available < quantity`; reserved tidak boleh dipakai dua kali.
- Constraint aplikasi dan database mencegah nilai saldo negatif; exception dikembalikan tanpa partial write.
- Reversal terhadap transaksi yang sudah reversed/cancelled ditolak.

### Pembentukan demand history

Transaksi barang keluar yang berstatus `posted` menjadi kandidat demand/penjualan setelah normalisasi berdasarkan product dan demand date. Return-out mengurangi demand hanya jika kebijakan bisnis menyatakan itu pembatalan penjualan; return-in tidak otomatis mengubah histori tanpa aturan sumber. Adjustment, transfer, dan koreksi tidak menjadi demand. `DemandHistoryService` melakukan upsert berdasarkan `(product_id, demand_date, source)`, menyimpan `quantity_sold`, dan memberi label stock availability/out-of-stock bila tersedia. Reprocessing wajib idempotent dan menyimpan source serta waktu ingestion.

## 7. Status implementasi Tahap 5

- Migration domain tersedia untuk users role/status, categories, products, suppliers, product suppliers, inventories, stock history, sales history, forecasts, forecast explanations, recommendations, recommendation decisions, dan audit logs.
- Implementasi Laravel menggunakan tabel `sales_history` (singular) agar sesuai kontrak ERD; model `SalesHistory` menetapkan nama tabel eksplisit.
- Database development existing tetap tidak dimigrasikan atau di-reset oleh agen. Migration dijalankan hanya melalui test database in-memory saat pengujian.
- Konfigurasi `.env.example` diarahkan ke MySQL; `.env` lokal pengguna tidak diubah agar tidak memutus environment yang sedang dipakai.
