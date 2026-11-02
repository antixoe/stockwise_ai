# Panduan Admin

## Administrasi pengguna

Admin dapat membuat pengguna dengan role admin, manager, atau warehouse_staff, serta menonaktifkan akun lain. Akun sendiri tidak dapat dinonaktifkan dari UI. Gunakan password kuat dan minta pengguna mengganti credential sesuai kebijakan organisasi.

## Audit and access review

Review audit log secara berkala untuk transaksi stok, reversal, approval/rejection, dan perubahan master data. Audit log tidak boleh dihapus untuk memperbaiki tampilan atau saldo.

## Environment and secrets

Kelola .env melalui secret manager. Pastikan APP_DEBUG=false, HTTPS, SESSION_SECURE_COOKIE=true, database user least-privilege, AI HMAC secret sama di Laravel dan AI service, dan tidak ada secret di log/repository.

## Operations

Monitor Laravel /up, AI /health, queue/failed jobs, MySQL backup, disk, dan logs. Gunakan maintenance-guide.md untuk release/incident dan backup-recovery.md untuk recovery.

## Model governance

Deploy hanya artifact yang memiliki model_version, metadata training, feature contract, evaluasi terhadap baseline, dan checksum yang direview. Dataset sintetis tidak boleh dipakai sebagai bukti performa produksi. SHAP tetap merupakan penjelasan model, bukan klaim kausal.
