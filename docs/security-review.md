# Security Review Tahap 9

## Hasil review

- SQL injection: query memakai Eloquent/query builder; pencarian produk tetap diparameterisasi. Tidak ditemukan `unprepared` atau interpolasi raw SQL yang berasal langsung dari input.
- XSS: output Blade menggunakan escaping default; payload chart memakai `@js`. Tidak ditemukan `{!! ... !!}` pada view aplikasi.
- CSRF: operasi web/Livewire berada pada middleware Laravel; API internal tetap berada di group `auth` dan request mutation memakai middleware web.
- Authorization/IDOR: controller memanggil policy untuk resource; route user/audit log dibatasi role admin; komponen Forecast/XAI/Recommendation memeriksa role server-side.
- Session/password: starter kit Laravel, password hashing, login throttling, session regeneration, HTTP-only dan SameSite cookie default aktif. `SESSION_SECURE_COOKIE` perlu `true` di HTTPS production.
- Secret management: AI HMAC secret dibaca dari environment; service menolak start bila secret tidak ada. `.env` di-ignore dan tidak dimasukkan ke dokumentasi.
- File upload: tidak ada endpoint upload sehingga validasi upload tidak applicable.
- AI service authentication: timestamp, nonce, HMAC constant-time comparison, private localhost default, dan error response generik tersedia. Production tetap memerlukan private network/HTTPS atau mTLS.
- Rate limiting: login memiliki limiter 5 attempts/menit dan route verification memakai throttle. Endpoint internal tidak memiliki throttle eksplisit; akses autentikasi/authorization tetap wajib.

## Temuan dan rekomendasi

1. Dependency vulnerability belum dapat diverifikasi online karena akses registry diblokir. Jalankan `composer audit --locked` dan `npm audit --omit=dev` pada CI/staging yang memiliki akses registry sebelum release.
2. `.env.example` adalah template development dengan `APP_DEBUG=true`; deployment production wajib override ke `APP_ENV=production`, `APP_DEBUG=false`, HTTPS cookie secure, secret manager, dan konfigurasi database production.
3. HMAC nonce disimpan in-memory pada proses AI service; deployment multi-process/restart membutuhkan nonce store bersama atau mekanisme replay protection terdistribusi.
4. Endpoint AI memakai `http.server` bawaan Python. Untuk production gunakan reverse proxy/private network dan transport/authentication production-grade.

Tidak ditemukan bypass authorization, raw SQL injection, secret hardcode, atau bug XAI yang mengklaim kontribusi sebagai hubungan kausal. Penjelasan XAI secara eksplisit menyatakan bahwa SHAP menjelaskan perilaku model, bukan sebab-akibat.
