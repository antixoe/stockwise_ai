# Maintenance Guide

## Daily

- Periksa /up, AI /health, error rate, queue depth, failed jobs, disk, CPU, memory, dan MySQL connections.
- Review log level info/warning tanpa menyalin credential atau payload sensitif.
- Pastikan backup terakhir berhasil dan berada di lokasi berbeda.

## Release procedure

1. Review perubahan dan dependency lock.
2. Jalankan Laravel tests, Python tests, PHP lint, composer validate, dan npm run build.
3. Deploy ke release directory staging; install composer install --no-dev dan npm ci pada build host.
4. Jalankan php artisan optimize dan php artisan migrate --force hanya pada database staging yang telah dibackup.
5. Restart workers dengan php artisan queue:restart setelah release siap.
6. Jalankan smoke test authentication, CRUD, receipt/issue, forecast, XAI, approval, /up, AI /health.
7. Promosikan release dengan change approval; pantau error dan queue selama observation window.

## Queue and scheduler

Jobs forecast dan explanation membutuhkan worker. Monitor failed_jobs; retry hanya setelah memahami penyebabnya. schedule:run dapat dipasang per menit, tetapi repository saat ini belum punya recurring business task. Jangan menganggap scheduler aktif hanya karena cron tersedia.

## Rotation and incident response

- Rotate AI_SERVICE_SECRET, database password, SMTP credential, dan application keys via secret manager; APP_KEY rotation memerlukan rencana karena dapat menginvalidasi encrypted session/cookies.
- Jika AI gagal, sistem harus mempertahankan status failed/insufficient-data dan tidak membuat forecast palsu.
- Jika inventory anomaly, hentikan perubahan operasional yang berisiko, pertahankan audit log, snapshot database, dan investigasi ledger; jangan mengedit saldo manual tanpa prosedur audited.
- Untuk security incident, batasi network, preserve logs, rotate secrets, patch, dan jalankan recovery approval.
