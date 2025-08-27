# 📋 **Panduan Pengguna - MM Web Monitoring v1.0.9**

## Fitur Pemantauan Domain

### Mengaktifkan Pemantauan Domain

1. Buka halaman edit website monitoring di WordPress admin
2. Scroll ke bagian "Domain Monitoring"
3. Centang kotak "Enable domain expiration monitoring"
4. Klik tombol "Update" atau "Publish" untuk menyimpan perubahan
5. Setelah halaman dimuat ulang, Anda akan melihat tombol "Click Enable Domain Expiry Monitoring"
6. Klik tombol tersebut untuk memulai pemeriksaan domain

### Memahami Hasil Pemeriksaan

#### Pemeriksaan Berhasil
Jika pemeriksaan domain berhasil, Anda akan melihat:
- Tanggal kedaluwarsa domain
- Jumlah hari tersisa sebelum domain kedaluwarsa
- Nama registrar domain (jika tersedia)

#### Pemeriksaan Gagal
Jika pemeriksaan domain gagal, Anda akan melihat:
- Pesan error yang menjelaskan alasan kegagalan
- Form untuk memasukkan tanggal kedaluwarsa domain secara manual

### Memasukkan Tanggal Kedaluwarsa Secara Manual

1. Jika pemeriksaan otomatis gagal, Anda akan melihat form "Manual Domain Expiry Date"
2. Masukkan tanggal kedaluwarsa domain dalam format MM/DD/YYYY
3. Klik di luar field atau tekan Enter untuk menyimpan

### Mematikan Pemantauan Domain

1. Buka halaman edit website monitoring di WordPress admin
2. Scroll ke bagian "Domain Monitoring"
3. Hapus centang pada kotak "Enable domain expiration monitoring"
4. Klik tombol "Update" untuk menyimpan perubahan

### Notifikasi Domain Kedaluwarsa

Setelah pemantauan domain diaktifkan, sistem akan secara otomatis:
- Memeriksa status domain secara berkala
- Mengirimkan notifikasi email 30 hari sebelum domain kedaluwarsa
- Menampilkan peringatan di dashboard WordPress jika domain akan segera kedaluwarsa

### Pemecahan Masalah

#### Tombol "Click Enable Domain Expiry Monitoring" Tidak Muncul
- Pastikan Anda telah menyimpan perubahan setelah mengaktifkan checkbox
- Pastikan URL website yang dimasukkan valid dan berformat benar

#### Pemeriksaan Domain Selalu Gagal
- Pastikan domain yang dimonitor valid dan aktif
- Periksa apakah server Anda mengizinkan perintah WHOIS atau koneksi ke API WHOIS
- Pertimbangkan untuk mengkonfigurasi API key WHOISXML di pengaturan plugin

#### Tanggal Kedaluwarsa Tidak Akurat
- Beberapa registrar domain mungkin tidak menyediakan informasi kedaluwarsa yang akurat melalui WHOIS
- Gunakan opsi input manual untuk memasukkan tanggal yang benar

### Tips

- Aktifkan pemantauan domain untuk semua website penting Anda
- Periksa dashboard WordPress secara berkala untuk melihat status domain
- Pertimbangkan untuk memperpanjang domain setidaknya 30 hari sebelum tanggal kedaluwarsa
- Gunakan fitur pemantauan domain bersamaan dengan pemantauan SSL untuk keamanan website yang komprehensif