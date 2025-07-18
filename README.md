# ✨ Pos Restoran

[![PHP](https://img.shields.io/badge/PHP-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![SCSS](https://img.shields.io/badge/SCSS-%23CC6699.svg?style=for-the-badge&logo=sass&logoColor=white)](https://sass-lang.com/)
[![Composer](https://img.shields.io/badge/composer-%23c5863a.svg?style=for-the-badge&logo=composer&logoColor=white)](https://getcomposer.org/)
[![MySQL](https://img.shields.io/badge/MySQL-%2300758F.svg?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![mPDF](https://img.shields.io/badge/mPDF-%23007bff.svg?style=for-the-badge&logo=mpdf&logoColor=white)](https://mpdf.github.io/mpdf/)
[![PhpSpreadsheet](https://img.shields.io/badge/PhpSpreadsheet-%23007bff.svg?style=for-the-badge&logo=phpspreadsheet&logoColor=white)](https://phpspreadsheet.readthedocs.io/en/latest/)


> Sistem Point of Sale (POS) restoran yang dibangun dengan PHP dan menggunakan template Sneat Admin Dashboard.  Sistem ini menyediakan antarmuka admin yang lengkap dan fungsional.

## ✨ Fitur Utama

- **Dasbor Admin Responsif:** Menawarkan antarmuka yang responsif dan mudah digunakan untuk manajemen restoran.
- **Manajemen Pengguna:** Mengelola pengguna dan peran dalam sistem.
- **Laporan:** Menampilkan berbagai laporan penjualan dan operasional restoran.
- **Ekspor Data:** Mengizinkan ekspor data ke format PDF dan Excel untuk analisis lebih lanjut.
- **Sistem Menu Restoran:**  Memungkinkan pengelolaan menu makanan dan minuman restoran.
- **Sistem Point of Sale (POS):**  Menangani transaksi penjualan secara langsung.
- **Cetak Struk:**  Mencetak struk transaksi penjualan.
- **Pengolahan Pembayaran:** Memproses pembayaran transaksi penjualan.
- **Pengaturan Sistem:**  Memberikan akses untuk mengatur parameter sistem.
- **Integrasi Database MySQL:** Menggunakan MySQL sebagai sistem manajemen basis data.
- **Integrasi Library mPDF:**  Menggunakan library mPDF untuk menghasilkan dokumen PDF.
- **Integrasi Library PhpSpreadsheet:** Menggunakan library PhpSpreadsheet untuk menghasilkan dokumen Excel.


## 🛠️ Tumpukan Teknologi

| Kategori          | Teknologi      | Catatan                                      |
|----------------------|-----------------|----------------------------------------------|
| Bahasa Pemrograman | PHP             | Backend                                      |
| CSS Preprocessor   | SCSS            | Untuk styling                                 |
| Framework          | N/A             | Menggunakan template Sneat, bukan framework utama |
| Database           | MySQL           | Penyimpanan data                              |
| Library           | mPDF, PhpSpreadsheet | Untuk ekspor PDF dan Excel                   |
| Manajer Paket      | Composer        | Manajemen dependensi                         |


## 🏛️ Tinjauan Arsitektur

Arsitektur aplikasi ini terbagi menjadi beberapa bagian utama: direktori `admin` untuk antarmuka admin, `assets` untuk sumber daya statis (CSS, JavaScript, gambar), `includes` untuk file PHP yang digunakan secara umum, `js` untuk file JavaScript tambahan, `libs` untuk library pihak ketiga, `pos` untuk sistem POS utama, dan `scss` untuk file SCSS.  Struktur ini mencerminkan pendekatan modular yang baik untuk pengembangan aplikasi web.


## 🚀 Memulai

1. Kloning repositori ini:
   ```bash
   git clone https://github.com/farzcode/pos-restoran.git
   ```
2. Impor database dari file `database.sql`.
3. Konfigurasi koneksi database di file `config.php`.
4. Instal dependensi dengan Composer:
   ```bash
   composer install
   ```
5. Jalankan server pengembangan (perintah spesifik bergantung pada konfigurasi server Anda, misal menggunakan PHP built-in web server atau XAMPP).


## 📂 Struktur File

```
/
├── .gitignore
├── README.md
├── admin
│   ├── dashboard.php
│   ├── export_excel.php
│   ├── export_pdf.php
│   ├── laporan.php
│   ├── menu.php
│   ├── settings.php
│   └── users.php
├── assets
│   ├── css
│   │   └── demo.css
│   ├── img
│   │   ├── avatars
│   │   │   ├── 1.png
│   │   │   ├── 2.png
│   │   │   ├── 5.png
│   │   │   ├── 6.png
│   │   │   └── 7.png
│   │   ├── backgrounds
│   │   │   └── 18.jpg
│   │   ├── elements
│   │   │   ├── 1.jpg
│   │   │   ├── 11.jpg
│   │   │   ├── 12.jpg
│   │   │   ├── 13.jpg
│   │   │   ├── 17.jpg
│   │   │   ├── 18.jpg
│   │   │   ├── 19.jpg
│   │   │   ├── 2.jpg
│   │   │   ├── 20.jpg
│   │   │   ├── 3.jpg
│   │   │   ├── 4.jpg
│   │   │   ├── 5.jpg
│   │   │   └── 7.jpg
│   │   ├── favicon
│   │   │   └── favicon.ico
│   │   ├── icons
│   │   │   ├── brands
│   │   │   │   ├── asana.png
│   │   │   │   ├── behance.png
│   │   │   │   ├── dribbble.png
│   │   │   │   ├── facebook.png
│   │   │   │   ├── github.png
│   │   │   │   ├── google.png
│   │   │   │   ├── instagram.png
│   │   │   │   ├── mailchimp.png
│   │   │   │   ├── slack.png
│   │   │   │   └── twitter.png
│   │   │   └── unicons
│   │   │       ├── cc-primary.png
│   │   │       ├── cc-success.png
│   │   │       ├── cc-warning.png
│   │   │       ├── chart-success.png
│   │   │       ├── chart.png
│   │   │       ├── paypal.png
│   │   │       ├── wallet-info.png
│   │   │       └── wallet.png
│   │   ├── illustrations
│   │   │   ├── girl-doing-yoga-light.png
│   │   │   ├── man-with-laptop-light.png
│   │   │   └── page-misc-error-light.png
│   │   ├── layouts
│   │   │   ├── layout-container-light.png
│   │   │   ├── layout-fluid-light.png
│   │   │   ├── layout-without-menu-light.png
│   │   │   └── layout-without-navbar-light.png
│   │   ├── logo_683a86195e6ea.png
│   │   └── logo_683a87820f7d7.png
│   ├── js
│   │   ├── config.js
│   │   ├── dashboards-analytics.js
│   │   ├── extended-ui-perfect-scrollbar.js
│   │   ├── form-basic-inputs.js
│   │   ├── main.js
│   │   ├── pages-account-settings-account.js
│   │   ├── ui-modals.js
│   │   ├── ui-popover.js
│   │   └── ui-toasts.js
│   └── vendor
│       ├── css
│       │   ├── core.css
│       │   ├── pages
│       │   │   ├── page-account-settings.css
│       │   │   ├── page-auth.css
│       │   │   ├── page-icons.css
│       │   │   └── page-misc.css
│       │   └── theme-default.css
│       ├── fonts
│       │   ├── boxicons.css
│       │   └── boxicons
│       │       ├── boxicons.eot
│       │       ├── boxicons.svg
│       │       ├── boxicons.ttf
│       │       ├── boxicons.woff
│       │       └── boxicons.woff2
│       ├── js
│       │   ├── bootstrap.js
│       │   ├── helpers.js
│       │   └── menu.js
│       └── libs
│           ├── apex-charts
│           │   ├── apex-charts.css
│           │   └── apexcharts.js
│           ├── highlight
│           │   ├── highlight-github.css
│           │   ├── highlight.css
│           │   └── highlight.js
│           ├── jquery
│           │   └── jquery.js
│           ├── masonry
│           │   └── masonry.js
│           ├── perfect-scrollbar
│           │   ├── perfect-scrollbar.css
│           │   └── perfect-scrollbar.js
│           ├── popper
│           │   └── popper.js
│           └── sweetalert2
│               ├── sweetalert2.css
│               └── sweetalert2.js
├── composer.json
├── composer.lock
├── config.php
├── database.sql
├── fonts
│   └── boxicons.scss
├── includes
│   ├── footer.php
│   └── header.php
├── js
│   ├── bootstrap.js
│   ├── helpers.js
│   └── menu.js
├── libs
│   ├── apex-charts
│   │   ├── apex-charts.scss
│   │   └── apexcharts.js
│   ├── highlight
│   │   ├── highlight-github.scss
│   │   ├── highlight.js
│   │   └── highlight.scss
│   ├── jquery
│   │   └── jquery.js
│   ├── masonry
│   │   └── masonry.js
│   ├── perfect-scrollbar
│   │   ├── perfect-scrollbar.js
│   │   └── perfect-scrollbar.scss
│   └── popper
│       └── popper.js
├── login.php
├── logout.php
├── pos
│   ├── cetak_struk.php
│   ├── index.php
│   └── proses_bayar.php
└── scss
    ├── _bootstrap-extended.scss
    └── _bootstrap-extended
        ├── _accordion.scss
        ├── _alert.scss
        ├── _badge.scss
        ├── _breadcrumb.scss
        ├── _button-group.scss
        ├── _buttons.scss
        ├── _card.scss
        ├── _carousel.scss
        ├── _close.scss
        ├── _dropdown.scss
        ├── _forms.scss
        ├── _functions.scss
        ├── _include.scss
        ├── _list-group.scss
        ├── _mixins.scss
        ├── _modal.scss
        ├── _nav.scss
        ├── _navbar.scss
        ├── _offcanvas.scss
        ├── _pagination.scss
        ├── _popover.scss
        ├── _progress.scss
        ├── _reboot.scss
        ├── _root.scss
        ├── _spinners.scss
        ├── _tables.scss
        ├── _toasts.scss
        ├── _tooltip.scss
        ├── _type.scss
        ├── _utilities-ltr.scss
        └── _utilities.scss
```

- **`admin`:**  Berisi file PHP untuk antarmuka administrasi sistem POS.
- **`assets`:**  Mengandung semua aset statis seperti CSS, JavaScript, dan gambar yang digunakan oleh aplikasi.
- **`includes`:**  File PHP yang digunakan secara umum di seluruh aplikasi, seperti header dan footer.
- **`js`:**  File JavaScript tambahan yang mungkin digunakan untuk fungsionalitas tertentu.
- **`libs`:**  Library pihak ketiga yang digunakan oleh aplikasi.
- **`pos`:**  File PHP yang menangani fungsi Point of Sale (POS) utama.
- **`scss`:**  File SCSS untuk mengatur tampilan aplikasi.


