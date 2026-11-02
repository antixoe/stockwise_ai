# Deployment Guide — StockWise AI

Dokumen ini menyiapkan deployment, tetapi tidak melakukan deployment. Tidak ada domain, DNS, layanan berbayar, atau resource produksi yang diubah pada Tahap 10.

## Arsitektur yang didukung

- Laravel 12/PHP 8.2+ dan web server HTTPS.
- MySQL 8-compatible pada private network.
- Queue worker Laravel terpisah dari web process.
- Python AI service terpisah, hanya dapat diakses Laravel melalui private network dan HMAC.
- SHAP dan model artifact berada di environment AI, bukan diakses langsung browser.

Shared hosting biasa tidak dianggap kompatibel bila tidak menyediakan PHP worker, MySQL, queue worker jangka panjang, HTTPS, dan Python process/service. Pilihan realistis adalah VPS/cloud VM, managed PHP + managed MySQL + private AI VM, atau Kubernetes/container platform. VPS paling rendah biaya tetapi membutuhkan patching, firewall, backup, monitoring, dan on-call; managed services mengurangi operasi tetapi menambah biaya.

## Urutan staging

1. Provision PHP 8.2+, extensions pdo_mysql, mbstring, openssl, xml, curl, fileinfo, dan Node.js hanya pada build host.
2. Provision MySQL dengan database stockwise dan user aplikasi least-privilege. Jangan memakai root.
3. Copy .env.production.example menjadi secret-managed .env; isi APP_KEY memakai php artisan key:generate --show pada secret manager, bukan repository.
4. Install PHP production dependencies:

   ~~~powershell
   composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
   npm ci
   npm run build
   ~~~

5. Jalankan migration staging dengan php artisan migrate --force setelah backup dan approval. Perintah ini tidak boleh diarahkan ke production sebelum change window.
6. Jalankan cache optimization:

   ~~~powershell
   php artisan optimize
   php artisan storage:link
   ~~~

7. Jalankan queue worker yang dikelola Supervisor/systemd/container orchestrator:

   ~~~powershell
   php artisan queue:work database --sleep=3 --tries=3 --timeout=120 --max-time=3600
   ~~~

8. Jalankan scheduler setiap menit hanya bila command terjadwal sudah didefinisikan:

   ~~~text
   * * * * * cd /srv/stockwise/current && php artisan schedule:run >> /dev/null 2>&1
   ~~~

   Saat ini repository belum memiliki recurring business command; jangan membuat scheduler palsu.

9. Start AI service dari virtual environment dengan STOCKWISE_AI_SECRET dan artifact model yang checksum-nya telah diverifikasi. Bind ke private interface/reverse proxy, bukan internet publik.
10. Verifikasi /up Laravel, /health AI dari private network, login, CRUD, transaksi stok, queue, forecast, XAI, approval, logs, dan backup restore di staging.

Route cache harus diuji dengan `php artisan optimize`; nama route API internal memakai prefix `internal.*` agar tidak bertabrakan dengan route halaman Livewire.

## Cutover dan rollback

Gunakan release directory immutable, health check, dan rollback ke release sebelumnya bila migration, login, queue, AI, atau inventory smoke test gagal. Jangan rollback dengan menghapus migration atau data; gunakan migration forward-only dan restore backup hanya melalui prosedur recovery yang disetujui.

## Konfigurasi security minimum

APP_ENV=production, APP_DEBUG=false, APP_URL=https://..., SESSION_SECURE_COOKIE=true, SESSION_HTTP_ONLY=true, SESSION_SAME_SITE=lax, secret manager aktif, MySQL private, firewall allowlist, TLS termination, dan log redaction. CORS tidak diperlukan untuk UI same-origin; jangan membuka wildcard CORS tanpa kebutuhan API yang terdokumentasi. CSRF Laravel tetap aktif untuk web/Livewire mutation.
