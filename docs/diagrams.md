# System Diagrams

Semua diagram berikut menggunakan Mermaid dan dapat dirender oleh dokumentasi yang mendukung Mermaid.

## 1. Use Case Diagram (representasi Mermaid)

```mermaid
flowchart LR
    A[Admin] --> AUTH[Login / Logout]
    A --> MASTER[Kelola Produk, Kategori, Supplier]
    A --> USERS[Kelola Pengguna & Role]
    A --> VIEW[Dashboard, Transaksi, Laporan, Audit]
    S[Staff Gudang] --> AUTH
    S --> STOCK[Catat Barang Masuk / Keluar]
    S --> INV[Lihat Persediaan & Riwayat]
    M[Manajer] --> AUTH
    M --> DASH[Lihat Dashboard & Laporan]
    M --> AI[Lihat Forecast & XAI]
    M --> REC[Tinjau Rekomendasi Restock]
    M --> APPROVE[Approve / Reject + Komentar]
    STOCK --> RULE[Inventory Rules]
    RULE --> DB[(MySQL)]
    AI --> PY[Python AI Runtime]
    PY --> FORECAST[Forecast + SHAP]
    FORECAST --> REC
    REC --> APPROVE
    APPROVE --> AUDIT[Audit Log]
```

## 2. Activity Diagram Login

```mermaid
flowchart TD
    S([Mulai]) --> F[Buka halaman login]
    F --> I[Input email dan password]
    I --> V{Validasi format}
    V -- Tidak --> E[Tampilkan error validasi]
    E --> I
    V -- Ya --> C[Auth service memeriksa kredensial]
    C --> OK{Valid dan aktif?}
    OK -- Tidak --> FAL[Tampilkan pesan login gagal dan catat event aman]
    FAL --> I
    OK -- Ya --> SES[Buat/regenerasi session]
    SES --> R[Resolve role dan permission]
    R --> D[Redirect dashboard role]
    D --> END([Selesai])
```

## 3. Activity Diagram CRUD Produk

```mermaid
flowchart TD
    S([Mulai]) --> P[Pilih menu Produk]
    P --> AUTH{Policy mengizinkan?}
    AUTH -- Tidak --> DENY[403 / tampilkan akses ditolak]
    AUTH -- Ya --> ACT{Pilih aksi}
    ACT -->|Tambah/Edit| FORM[Tampilkan form]
    FORM --> VAL{Form Request valid?}
    VAL -- Tidak --> ERR[Tampilkan error field]
    ERR --> FORM
    VAL -- Ya --> UNIQUE{SKU unik dan relasi valid?}
    UNIQUE -- Tidak --> ERR
    UNIQUE -- Ya --> SAVE[Simpan dalam transaction dan audit]
    ACT -->|Arsipkan| ARCH[Validasi produk tidak menghapus histori]
    ARCH --> SAVE2[Set is_active false dan audit]
    ACT -->|Lihat/Cari| LIST[Query terfilter dan pagination]
    SAVE --> LIST
    SAVE2 --> LIST
    LIST --> END([Selesai])
```

## 4. Activity Diagram Barang Masuk/Keluar

```mermaid
flowchart TD
    S([Mulai]) --> F[Isi form transaksi]
    F --> V[Form Request + Policy]
    V --> OK{Valid?}
    OK -- Tidak --> E[Tampilkan error]
    E --> F
    OK -- Ya --> KEY{Idempotency key sudah ada?}
    KEY -- Ya --> EXIST[Return hasil transaksi sebelumnya]
    KEY -- Tidak --> TX[Buka DB transaction]
    TX --> LOCK[SELECT inventory FOR UPDATE]
    LOCK --> TYPE{Barang masuk atau keluar?}
    TYPE -->|Masuk| ADD[Naikkan on_hand]
    TYPE -->|Keluar| CHECK{on_hand - reserved cukup?}
    CHECK -- Tidak --> ROLLBACK[Rollback dan tolak transaksi]
    CHECK -- Ya --> SUB[Turunkan on_hand]
    ADD --> POST[Simpan ledger posted]
    SUB --> POST
    POST --> AUD[Audit dan commit]
    AUD --> END([Selesai])
    ROLLBACK --> END
    EXIST --> END
```

## 5. Activity Diagram Forecasting AI

```mermaid
flowchart TD
    S([Mulai terjadwal/manual]) --> AUTH[Policy + buat forecast run]
    AUTH --> SNAP[Ambil snapshot sales history dan parameter]
    SNAP --> Q[Dispatch queue job]
    Q --> PREP[Validasi kualitas dan feature contract]
    PREP --> ELIG{Data layak?}
    ELIG -- Tidak --> NA[Simpan status insufficient_data]
    ELIG -- Ya --> CALL[Kirim request bertanda tangan ke Python]
    CALL --> RESP{Response berhasil?}
    RESP -- Tidak --> FAIL[Simpan failed + retry terbatas/dead letter]
    RESP -- Ya --> SAVE[Simpan forecast idempotent + model version + metrics]
    SAVE --> EX[Dispatch job XAI]
    NA --> END([Selesai])
    FAIL --> END
    EX --> END
```

## 6. Activity Diagram Explainable AI

```mermaid
flowchart TD
    S([Forecast eligible]) --> LOAD[Ambil forecast, input snapshot, model version]
    LOAD --> CALL[Kirim permintaan SHAP ke Python]
    CALL --> V{Payload kontribusi valid?}
    V -- Tidak --> FAIL[Simpan explanation failed; tandai belum dapat dijelaskan]
    V -- Ya --> N[Normalisasi faktor, arah, nilai, dan batas ukuran]
    N --> SAVE[Simpan forecast_explanations]
    SAVE --> UI[Tampilkan faktor dan catatan batasan di UI]
    FAIL --> UI2[Tampilkan status XAI tidak tersedia]
    UI --> END([Selesai])
    UI2 --> END
```

## 7. Activity Diagram Rekomendasi Restock

```mermaid
flowchart TD
    S([Forecast + XAI siap]) --> LOAD[Ambil snapshot stok dan supplier]
    LOAD --> DATA{Parameter lengkap?}
    DATA -- Tidak --> REVIEW[Simpan not eligible / perlu tinjauan]
    DATA -- Ya --> CALC[Hitung demand selama lead time]
    CALC --> RULE[Tambahkan safety stock, kurangi on-hand/reserved/incoming]
    RULE --> ROUND[Bulatkan ke MOQ dan order multiple]
    ROUND --> POS{Jumlah > 0?}
    POS -- Tidak --> NO[Simpan no action / tidak perlu restock]
    POS -- Ya --> CREATE[Simpan recommendation + input snapshot + rationale]
    CREATE --> PEND[Status pending approval]
    REVIEW --> END([Selesai])
    NO --> END
    PEND --> END
```

## 8. Sequence Diagram Approval Manajer

```mermaid
sequenceDiagram
    actor M as Manajer
    participant UI as Livewire UI
    participant P as RecommendationPolicy
    participant S as RecommendationService
    participant DB as MySQL
    participant A as AuditLogService

    M->>UI: Buka detail rekomendasi
    UI->>P: authorize(view, recommendation)
    P-->>UI: allowed
    UI->>DB: Ambil recommendation + forecast + XAI
    DB-->>UI: Detail snapshot
    M->>UI: Pilih approve/reject + komentar
    UI->>P: authorize(decide, recommendation)
    P-->>UI: allowed / denied
    UI->>S: decide(recommendation, decision, comment)
    S->>DB: BEGIN + lock recommendation
    S->>DB: Validasi status pending dan simpan decision snapshot
    S->>A: Catat actor, status, komentar, request_id
    A->>DB: INSERT audit_log
    S->>DB: COMMIT
    DB-->>S: Decision persisted
    S-->>UI: Status keputusan
    UI-->>M: Tampilkan hasil dan jejak keputusan
```

## 9. Batasan diagram

Diagram menggambarkan alur konseptual, bukan kontrak route atau schema final yang sudah diimplementasikan. Detail field dan constraint berada di `docs/database.md`; detail modul berada di `docs/module-structure.md`.
