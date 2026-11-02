# Known Issues Tahap 9

1. MySQL production-like integration belum tervalidasi karena tidak ada service database yang tersedia pada environment ini. SQLite in-memory digunakan oleh test suite.
2. Browser automation/device matrix belum tersedia, sehingga responsive layout, keyboard navigation, focus trap, chart readability, dan visual loading/error/empty state belum mendapat verifikasi visual.
3. Dependency audit online belum selesai karena network registry diblokir.
4. Concurrency lintas proses belum diuji pada MySQL; locking dan invariant saldo baru diverifikasi oleh test serial/SQLite.
5. E2E login sampai history dengan queue worker dan AI service nyata belum dijalankan sebagai satu sesi browser. Test per modul tersedia.
6. Dataset bisnis dan database production tidak tersedia; evaluasi ML masih berbasis dataset sintetis dan tidak boleh dianggap sebagai bukti performa bisnis.
7. Konfigurasi production security (debug off, secure cookie, secret manager, HTTPS/mTLS, rate limiting edge) memerlukan hardening deployment dan belum boleh dianggap selesai dari repository test ini.
8. Scheduler belum memiliki recurring business command; hanya memasang cron tanpa command tidak memberi fungsi operasional.
