# MM Web Monitoring

## Dokumentasi Plugin

### Versi: 1.1.0

## Deskripsi

MM Web Monitoring adalah plugin WordPress yang dirancang untuk memantau kinerja dan ketersediaan website. Plugin ini menyediakan fitur pemantauan uptime, pemeriksaan SSL, dan pemantauan kedaluwarsa domain untuk memastikan website Anda tetap berjalan dengan optimal.

## Fitur Utama

1. **Pemantauan Uptime**
   - Pemeriksaan status website secara berkala
   - Notifikasi email saat website down
   - Interval pemantauan yang dapat disesuaikan

2. **Pemeriksaan SSL**
   - Verifikasi validitas sertifikat SSL
   - Peringatan saat sertifikat SSL akan kedaluwarsa
   - Informasi detail tentang sertifikat SSL (penerbit, tanggal kedaluwarsa)

3. **Pemantauan Kedaluwarsa Domain**
   - Pemeriksaan tanggal kedaluwarsa domain
   - Peringatan saat domain akan kedaluwarsa
   - Opsi input manual untuk domain yang tidak dapat diperiksa secara otomatis

4. **Antarmuka Pengguna yang Intuitif**
   - Dashboard yang mudah digunakan
   - Tampilan status yang jelas dengan kode warna
   - Tombol aksi dengan deskripsi yang jelas

## Cara Penggunaan

### Menambahkan Website untuk Dipantau

1. Buka menu "MM Web Monitoring" di dashboard WordPress
2. Klik "Add New"
3. Masukkan URL website yang ingin dipantau
4. Atur interval pemantauan sesuai kebutuhan
5. Aktifkan pemantauan domain jika diperlukan
6. Klik "Publish" untuk mulai memantau

### Mengaktifkan Pemantauan Domain

1. Pada halaman edit website, centang opsi "Enable domain expiration monitoring"
2. Klik "Update" untuk menyimpan perubahan
3. Klik tombol "Click Enable Domain Expiry Monitoring" untuk memulai pemeriksaan domain

### Menggunakan Tombol Aksi

- **Start**: Mulai atau lanjutkan pemantauan website
- **Pause**: Hentikan sementara pemantauan tanpa kehilangan pengaturan
- **Stop**: Hentikan pemantauan website sepenuhnya
- **Check Now**: Jalankan pemeriksaan segera tanpa menunggu jadwal

## Pemecahan Masalah

### Pemeriksaan Domain Gagal

Jika pemeriksaan domain gagal, Anda dapat:
1. Periksa informasi WHOIS domain melalui tautan yang disediakan
2. Masukkan tanggal kedaluwarsa domain secara manual

### Sertifikat SSL Bermasalah

Jika sertifikat SSL bermasalah, periksa:
1. Validitas sertifikat SSL di website Anda
2. Tanggal kedaluwarsa sertifikat
3. Konfigurasi SSL di server Anda

## Catatan Pengembangan

### Versi 1.1.0
- Ditambahkan tampilan informasi SSL yang lebih jelas
- Ditambahkan deskripsi untuk tombol-tombol pada CPT (Start, Pause, Stop, Check Now)
- Ditambahkan link WHOIS pada tampilan ketika pemeriksaan domain gagal
- Dibuat sistem versi terpusat dengan DEFINE dari file induk plugin
- Ditambahkan dokumentasi lengkap tentang plugin

### Versi 1.0.9
- Perbaikan bug pada pemeriksaan domain
- Peningkatan performa pemantauan uptime

## Roadmap Pengembangan

- Integrasi dengan layanan pemantauan eksternal
- Pemantauan kinerja server
- Pemantauan keamanan website
- Dashboard analitik yang lebih komprehensif

## Dukungan

Untuk pertanyaan atau dukungan, silakan hubungi pengembang di:
- Website: [https://budiharyono.id/](https://budiharyono.id/)

---

© 2023 Budi Haryono. Semua hak dilindungi.