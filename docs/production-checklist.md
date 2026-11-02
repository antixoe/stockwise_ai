# Production Readiness Checklist

## Status

Status Tahap 10: **siap untuk persiapan staging, belum live**.

### Configuration and security

- [x] .env.production.example tersedia tanpa secret nyata.
- [x] Laravel default APP_DEBUG false bila environment tidak mengoverride.
- [ ] APP_ENV=production, APP_KEY, HTTPS URL, dan secrets diisi pada secret manager staging/production.
- [ ] Secure session cookie diverifikasi pada HTTPS production.
- [x] CSRF Laravel/Livewire aktif; UI same-origin sehingga wildcard CORS tidak diperlukan.
- [ ] Firewall, TLS certificate, HSTS, dan reverse proxy configured.
- [ ] Storage/log directory owner dan mode diverifikasi pada target host.
- [ ] Log shipping/redaction configured; password/token/AI secret tidak boleh masuk log.

### Laravel

- [x] composer.lock dan package-lock.json tersedia.
- [x] Vite production build lulus pada Tahap 9.
- [x] Route cache production berhasil dibuat setelah nama route API diberi namespace `internal.*`.
- [ ] composer install --no-dev dan php artisan optimize dijalankan pada staging.
- [x] Database queue/cache migrations tersedia.
- [ ] Queue worker dijalankan di process manager dan failed jobs dimonitor.
- [ ] Scheduler/cron dipasang; saat ini belum ada recurring command bisnis.

### MySQL

- [x] Foreign keys, unique constraints, indexes, dan migration order direview.
- [ ] Migration dijalankan pada staging MySQL, bukan hanya SQLite preview.
- [ ] Aplikasi memakai user least-privilege, bukan root.
- [ ] Backup otomatis, retention, encryption, restore drill, dan alert verified.

### AI/XAI

- [x] requirements.lock disediakan berdasarkan environment test tervalidasi.
- [ ] Virtualenv/immutable Python image dibuat pada staging.
- [ ] Artifact model, metadata model version, checksum, dan feature contract verified.
- [x] Laravel timeout/retry dan HMAC headers tersedia.
- [ ] AI /health dan private routing verified.
- [ ] Multi-process replay protection/nonce store dan HTTPS/mTLS hardened.

### Go/no-go

Go-live hanya setelah semua checkbox staging/production yang bertanda [ ] selesai dan smoke test Tahap 9 diulang terhadap MySQL, queue, AI service nyata, dan HTTPS. Website belum boleh disebut LIVE tanpa production URL yang benar-benar diverifikasi.
