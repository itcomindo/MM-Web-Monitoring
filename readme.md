# MM Web Monitoring

> Plugin WordPress yang simpel namun powerful untuk memantau performa dan uptime website langsung dari dasbor WordPress Anda.

![Tangkapan layar plugin MM Web Monitoring](screenshot.png)

## Deskripsi

**MM Web Monitoring** adalah solusi terintegrasi untuk agensi, developer, atau siapa saja yang mengelola banyak website dan membutuhkan cara efisien untuk memastikan semua situs tersebut online dan berfungsi dengan baik. Lupakan layanan eksternal yang mahal, semua alat yang Anda butuhkan kini ada di dalam dasbor WordPress yang sudah Anda kenal.

Plugin ini memungkinkan Anda menambahkan daftar website untuk dipantau secara berkala. Anda dapat memilih interval pengecekan, tipe verifikasi (hanya kode respons atau mencari elemen HTML tertentu), dan menerima notifikasi email cerdas saat terjadi masalah atau saat masalah telah teratasi. Dengan fitur manajemen massal dan *inline editing*, mengelola puluhan website menjadi sangat cepat dan mudah.

## Fitur Utama & Manfaat

*   **💻 Monitoring Terpusat:** Pantau semua website Anda dari satu dasbor WordPress. Tidak perlu lagi login ke berbagai layanan monitoring.
*   **⚙️ Pengecekan Fleksibel:**
    *   **Response Code Only:** Pengecekan cepat untuk memastikan website merespons dengan status OK (HTTP 200).
    *   **Fetch HTML Content:** Verifikasi lebih dalam dengan memastikan elemen HTML penting (seperti logo atau judul halaman) ada di halaman, mencegah kasus "halaman putih" atau error fatal.
*   **📧 Notifikasi Email Cerdas:**
    *   Atur email notifikasi per website atau gunakan email default global.
    *   Pilih kapan Anda ingin menerima email: **Always** (setiap ada perubahan status, baik down maupun pulih) atau **On Error & Recovery** (hanya saat terjadi error dan saat error tersebut teratasi).
*   **🚀 Manajemen Massal (Bulk Management):**
    *   **Bulk Add:** Tambahkan puluhan website sekaligus hanya dengan menempelkan daftar URL.
    *   **Bulk Actions:** Pilih beberapa website dan jalankan pengecekan, aktifkan (start), atau jeda (pause) monitoring secara bersamaan dengan progress bar yang informatif.
*   **⚡ Alur Kerja Cepat (Inline Editing):**
    *   Ubah interval pengecekan, alamat email, provider hosting, dan mode notifikasi langsung dari tabel daftar website tanpa perlu membuka halaman editor satu per satu.
*   **📊 Informasi Lengkap:**
    *   Lihat status terakhir, waktu pengecekan terakhir, dan jadwal pengecekan berikutnya (dengan countdown).
    *   Catat provider hosting untuk setiap website agar mudah dilacak.

## Instalasi

1.  Unduh file `.zip` dari plugin ini.
2.  Login ke dasbor WordPress Anda.
3.  Pergi ke menu **Plugins > Add New**.
4.  Klik tombol **Upload Plugin** di bagian atas halaman.
5.  Pilih file `.zip` yang sudah Anda unduh dan klik **Install Now**.
6.  Setelah instalasi selesai, klik **Activate Plugin**.

**Metode Alternatif (FTP):**
1.  Unzip file `.zip`.
2.  Unggah folder `mm-web-monitoring` ke direktori `/wp-content/plugins/` di server Anda.
3.  Pergi ke menu **Plugins** di dasbor WordPress dan aktifkan plugin "MM Web Monitoring".

## Cara Penggunaan

Setelah diaktifkan, menu baru bernama **"Web Monitoring"** akan muncul di sidebar admin Anda.

### 1. Mengatur Opsi Global

Sebelum memulai, disarankan untuk mengatur email default:
1.  Pergi ke **Web Monitoring > Global Options**.
2.  Masukkan alamat email yang akan digunakan sebagai penerima notifikasi default jika Anda tidak menentukannya secara spesifik untuk sebuah website.
3.  Klik **Save Changes**.

### 2. Menambahkan Website Secara Massal (Bulk Add)

Ini adalah cara tercepat untuk memulai:
1.  Pergi ke **Web Monitoring > Bulk Add**.
2.  Tempelkan daftar URL website yang ingin Anda pantau di dalam textarea. Pastikan setiap URL berada di baris baru.
3.  Klik tombol **Add Bulk Monitoring**.
4.  Plugin akan memproses setiap URL dengan jeda 2 detik, secara otomatis membuat entri monitoring, mengaktifkannya, dan langsung menjalankan pengecekan pertama.

### 3. Memantau dan Mengelola Website (Halaman All Websites)

Halaman utama plugin ada di **Web Monitoring > All Websites**. Di sini Anda bisa melakukan hampir semua hal:

*   **Melihat Status:** Cek status `UP`, `DOWN`, atau `CONTENT_ERROR` di kolom "Check Result".
*   **Inline Editing:** Cukup klik pada nilai di kolom **Interval**, **Host In**, **Email Report**, atau **Email To** untuk mengubahnya secara langsung tanpa meninggalkan halaman.
*   **Aksi Individu:** Gunakan tombol-tombol di kolom "Actions" untuk menjalankan pengecekan manual (`Check Now`), menjeda (`Pause`), mengaktifkan (`Start`), atau menghentikan (`Stop`) monitoring untuk satu website.
*   **Aksi Massal:** Centang beberapa website menggunakan checkbox, lalu gunakan tombol **Bulk Check Now**, **Bulk Start**, atau **Bulk Pause** yang ada di atas tabel untuk melakukan aksi secara massal.

---