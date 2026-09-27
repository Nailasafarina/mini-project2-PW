# Product Manager App (Mini Project 2)

## Cara Menjalankan
1. Buat database baru melalui phpMyAdmin atau MySQL CLI.
2. Jalankan script SQL yang ada di `database/store_db.sql` untuk membuat tabel dan data awal.
3. Pastikan konfigurasi di `config/db.php` sudah sesuai dengan pengaturan server MySQL lokal Anda (username: root, password kosong).
4. Jalankan built-in web server PHP di folder root project:
   `php -S localhost:8000 -t public`
5. Buka browser dan akses: `http://localhost:8000/`

## Fitur & Keamanan yang Diimplementasi
* **CRUD Penuh:** Sesuai standar dengan input form.
* **Keamanan:** PDO Prepared Statements, CSRF Token untuk menghapus data, pencegahan XSS.
* **Validasi:** Server-side validasi & Pola Post-Redirect-Get.