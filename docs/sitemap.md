# Sitemap StockWise AI

Navigasi ditampilkan berdasarkan role, tetapi setiap route tetap dilindungi middleware dan Policy/Gate di server.

```mermaid
flowchart TD
    ROOT[//]
    ROOT --> LOGIN[/login]
    LOGIN --> DASH[/dashboard]
    DASH --> STOCK[Persediaan]
    DASH --> TX[Transaksi Stok]
    DASH --> FORE[Forecast AI]
    DASH --> REC[Rekomendasi Restock]
    DASH --> REPORT[Laporan]
    DASH --> AUDIT[Audit Log]

    STOCK --> STOCKLIST[/inventory]
    TX --> IN[/stock-transactions/in]
    TX --> OUT[/stock-transactions/out]
    TX --> HISTORY[/stock-transactions/history]

    MASTER[Master Data] --> PRODUCTS[/products]
    MASTER --> CATEGORIES[/categories]
    MASTER --> SUPPLIERS[/suppliers]
    PRODUCTS --> PRODUCTCREATE[/products/create]
    PRODUCTS --> PRODUCTEDIT[/products/{product}/edit]

    FORE --> FORECASTLIST[/forecasts]
    FORE --> FORECASTDETAIL[/forecasts/{forecast}]
    FORECASTDETAIL --> XAI[/forecasts/{forecast}/explanation]
    REC --> RECLIST[/recommendations]
    RECLIST --> RECDETAIL[/recommendations/{recommendation}]
    RECDETAIL --> DECIDE[/recommendations/{recommendation}/decision]

    REPORT --> STOCKREPORT[/reports/stock]
    REPORT --> TXREPORT[/reports/transactions]
    REPORT --> AIREPORT[/reports/forecast-decisions]
    ADMIN[Admin] --> USERS[/users]
    ADMIN --> SETTINGS[/settings]

    DASH -.-> MASTER
    DASH -.-> ADMIN
```

## Menu berdasarkan role

| Area | Admin | Staff Gudang | Manajer |
|---|---:|---:|---:|
| Dashboard | ✓ | ✓ | ✓ |
| Persediaan | ✓ | ✓ | ✓ lihat |
| Transaksi masuk/keluar | ✓ | ✓ | lihat |
| Produk/kategori/supplier | ✓ | - | - |
| Pengguna/role | ✓ | - | - |
| Forecast dan XAI | ✓ lihat | - | ✓ |
| Rekomendasi | ✓ lihat | - | ✓ approve/reject |
| Laporan | ✓ | -/terbatas | ✓ |
| Audit log | ✓ | - | -/sesuai kebijakan |

Route aktual, naming convention, dan middleware akan ditentukan pada Tahap 4 setelah desain UI disetujui.
