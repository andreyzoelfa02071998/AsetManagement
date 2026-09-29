# Analisa Aset

Web app sederhana untuk mencatat aset, transaksi, dan harga emas. Dibuat dengan CodeIgniter 3 dan MySQL.

## Fitur

- Dashboard ringkasan portofolio.
- Master aset: saham, reksa dana, crypto, emas, properti, kas, dan lainnya.
- Transaksi beli/jual dengan kalkulasi nilai otomatis.
- Monitoring harga emas dengan sinyal beli otomatis saat harga `<= 24000`.
- SQL schema + seed data awal.

## Instalasi

1. Salin folder ini ke lokasi project, misalnya `D:\Analisa Aset`.
2. Buat database MySQL:

```sql
CREATE DATABASE analisa_aset CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Import file `database/analisa_aset.sql`.
4. Cek konfigurasi database di `application/config/database.php`.
5. Jalankan Apache dan MySQL lewat XAMPP.
6. Buka `http://localhost/Analisa%20Aset/` bila folder ditaruh di `htdocs`, atau atur Apache VirtualHost/alias ke `D:\Analisa Aset`.

## Default Database

- Host: `localhost`
- User: `root`
- Password: kosong
- Database: `analisa_aset`

"# AsetManagement" 
