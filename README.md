# OptiStock — Sistem Informasi Manajemen Inventaris (PHP Native)

OptiStock adalah aplikasi manajemen persediaan inventaris barang berbasis web yang dikembangkan menggunakan **PHP Native (Procedural & Modular)** serta database **MySQL**. Aplikasi ini dirancang untuk memudahkan toko dan pergudangan dalam mencatat pergerakan stok, memantau batas minimum persediaan secara real-time, dan mengelola master data logistik dengan aman dan efisien.

---

## Fitur Utama

### 1. Hak Akses Berbasis Peran (Role-Based Access Control)
* **Administrator (`admin`)**:
  * Akses penuh ke dashboard ringkasan statistik dan analitik stok.
  * Manajemen katalog produk: tambah produk, edit detail produk, upload foto, dan hapus data.
  * Transaksi pencatatan stok masuk (*Stock In*) dan stok keluar (*Stock Out*).
  * Pemantauan riwayat log mutasi barang masuk dan keluar secara menyeluruh.
  * Manajemen master data: Kategori barang, Vendor/Supplier, dan Akun Petugas.
* **Staff Gudang (`staff`)**:
  * Memantau persediaan stok barang pada katalog produk.
  * Melakukan pencatatan operasional stok masuk dan barang keluar.
  * Memeriksa rekam jejak histori mutasi stok.
  * Akses dibatasi secara otomatis dari menu master data dan manajemen user.

### 2. Modul & Fungsionalitas Sistem
* **Dashboard Metrik**: Ringkasan jumlah SKU, total estimasi nilai aset persediaan, serta akumulasi kuantitas barang masuk dan keluar.
* **Early Warning System**: Peringatan visual otomatis untuk produk yang kuantitasnya berada di bawah batas minimum (*low stock alert*).
* **Katalog Produk Modern**: Tampilan berbasis kartu (*grid card*) lengkap dengan filter pencarian instan dan kategori.
* **Logika Mutasi Terproteksi**: Transaksi stok keluar divalidasi ketat untuk mencegah terjadinya stok negatif (minus).
* **Sistem Helper Terpusat**: Modularitas fungsi autentikasi, proteksi injeksi (*escape input*), dan validasi form.

---

## Spesifikasi Teknologi (Tech Stack)

* **Bahasa Pemrograman:** PHP 7.4 / 8.x (Native)
* **Basis Data:** MySQL / MariaDB
* **Web Server:** Apache (XAMPP / Laragon)
* **Desain Antarmuka:** Custom CSS3 (Modern Emerald & Slate Theme)
* **Tipografi:** Google Fonts *Plus Jakarta Sans*

---

## Akun Demo Pengujian

Gunakan akun pengujian di bawah ini untuk menguji hak akses sistem:

| Peran (Role) | Username | Password | Cakupan Hak Akses |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin` | `admin123` | Akses penuh (Dashboard, Produk, Mutasi, Kategori, Supplier, Kelola User) |
| **Staff Gudang** | `staff` | `staff123` | Akses operasional (Dashboard, Katalog, Stok Masuk, Stok Keluar, Histori) |

---

## Panduan Instalasi & Menjalankan Aplikasi

### 1. Penempatan Folder Proyek
Pastikan folder proyek ini diletakkan di dalam direktori web server lokal Anda:
* **Pengguna XAMPP:**
  ```text
  C:/xampp/htdocs/inventory_app/
  ```
* **Pengguna Laragon:**
  ```text
  C:/laragon/www/inventory_app/
  ```

### 2. Import Database MySQL
1. Buka kontrol panel **XAMPP** atau **Laragon**, lalu pastikan service **Apache** dan **MySQL** dalam status aktif (*running*).
2. Buka browser dan buka phpMyAdmin melalui alamat:
   ```text
   http://localhost/phpmyadmin
   ```
3. Buat database baru dengan nama:
   ```sql
   inventory_app
   ```
4. Pilih database `inventory_app`, buka tab **Import**, lalu pilih file skema SQL yang berada di dalam folder:
   ```text
   database/schema.sql
   ```
5. Klik tombol **Import / Kirim** dan pastikan seluruh tabel berhasil dieksekusi.

### 3. Konfigurasi Koneksi Database
Periksa berkas konfigurasi di `config/database.php` dan pastikan parameternya sesuai dengan lingkungan lokal Anda:

```php
<?php
$host     = "localhost";
$username = "root";
$password = "";
$database = "inventory_app";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}
?>
```

### 4. Akses Aplikasi di Browser
Buka peramban (Google Chrome, Firefox, Edge) dan akses alamat lokal:
```text
http://localhost/inventory_app
```
*(atau jika diarahkan langsung ke folder public: `http://localhost/inventory_app/public`)*

---

## Struktur Direktori Proyek

Berikut adalah struktur file dan folder dari aplikasi **OptiStock (PHP Native)**:

```text
inventory_app/
├── config/
│   └── database.php          # Konfigurasi koneksi MySQL
├── database/
│   └── schema.sql            # Skema database & data seeding awal
├── helpers/
│   ├── auth.php              # Fungsi pengecekan session login & role guard
│   ├── escape.php            # Fungsi pembersihan input (sanitasi/anti-XSS)
│   └── validation.php        # Helper validasi data input form
├── public/
│   ├── assets/
│   │   └── style.css         # Styling CSS tema Emerald & Slate
│   ├── categories/
│   │   ├── create.php        # Form / proses tambah kategori
│   │   ├── delete.php        # Proses hapus kategori
│   │   ├── edit.php          # Form / proses edit kategori
│   │   └── index.php         # Daftar master kategori
│   ├── products/
│   │   ├── create.php        # Form tambah produk baru & upload foto
│   │   ├── edit.php          # Form edit detail & ganti gambar produk
│   │   └── index.php         # Tampilan katalog produk grid & filter
│   ├── stock/
│   │   ├── history.php       # Riwayat mutasi stok barang
│   │   ├── in.php            # Form pencatatan barang masuk
│   │   └── out.php           # Form pencatatan barang keluar
│   ├── suppliers/
│   │   ├── create.php        # Form / proses tambah data supplier
│   │   ├── delete.php        # Proses hapus data supplier
│   │   ├── edit.php          # Form / proses edit data supplier
│   │   └── index.php         # Daftar master supplier
│   ├── users/
│   │   ├── create.php        # Form tambah akun petugas/admin baru
│   │   ├── delete.php        # Proses hapus akun pengguna
│   │   └── index.php         # Daftar kelola pengguna sistem
│   ├── dashboard.php         # Halaman ringkasan metrik inventaris
│   ├── index.php             # Gerbang utama aplikasi di area public
│   ├── login.php             # Halaman autentikasi / form login
│   └── logout.php            # Proses pembersihan session pengguna
├── views/
│   ├── footer.php            # Template bagian bawah & penutup HTML
│   └── header.php            # Template bagian atas & sidebar navigasi
├── index.php                 # File root router / redirect ke folder public
└── README.md                 # Dokumentasi panduan proyek
```

---

## Catatan Keamanan Sistem

* **Session & Role Authentication (`helpers/auth.php`)**: Memastikan modul sensitif seperti manajemen pengguna dan master data hanya bisa diakses oleh akun berstatus `admin`.
* **Data Sanitization (`helpers/escape.php`)**: Mencegah celah kerentanan Cross-Site Scripting (XSS) dan SQL Injection pada input form.
* **Negative Stock Prevention**: Logika pada modul `stock/out.php` menolak mutasi jika kuantitas barang yang dikeluarkan melebihi stok yang tersedia di gudang.


