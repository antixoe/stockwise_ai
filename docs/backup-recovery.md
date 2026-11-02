# Backup and Recovery

## Backup policy

- MySQL: daily full logical backup memakai mysqldump --single-transaction, plus binary logs/point-in-time recovery bila tersedia.
- Simpan backup terenkripsi di lokasi berbeda; retention contoh: 7 daily, 4 weekly, 12 monthly sesuai kebijakan organisasi.
- Uji restore minimal bulanan pada database staging terisolasi. Backup yang tidak pernah direstore dianggap belum tervalidasi.
- Jangan menyimpan password di command line atau repository. scripts/backup-mysql.ps1 meminta MYSQL_PWD dari secure session/secret manager.

## Backup command

Pada host backup dengan MySQL client dan environment non-production yang benar:

~~~powershell
$env:MYSQL_PWD = '<provided-by-secret-manager>'
.\scripts\backup-mysql.ps1 -OutputDirectory 'D:\backups\stockwise'
Remove-Item Env:\MYSQL_PWD
~~~

Validasi file, checksum, ukuran, dan status exit code. Jangan menjalankan script dengan output directory yang tidak disetujui.

## Recovery drill

1. Umumkan maintenance dan hentikan writer/queue worker agar tidak ada transaksi baru.
2. Provision MySQL staging/recovery instance dan restore backup ke database baru, bukan menghapus database sumber.
3. Apply migration yang sesuai hanya bila prosedur recovery menyetujuinya.
4. Verifikasi row counts, foreign keys, latest audit log, saldo inventory, login, dan smoke test.
5. Untuk point-in-time recovery, replay binlog sampai timestamp yang disetujui.
6. Reconnect application hanya setelah credential, DNS/load balancer, queue, dan AI endpoints diverifikasi.
7. Catat RPO/RTO aktual dan hasil drill.

Recovery production memerlukan approval eksplisit, backup yang valid, dan runbook operator. Tahap ini tidak menjalankan restore atau tindakan destruktif.
