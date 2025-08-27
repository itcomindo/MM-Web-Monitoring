# Changelog MM Web Monitoring v1.1.0

## Peningkatan Utama

### Tampilan Informasi SSL yang Lebih Jelas
- Ditambahkan tampilan informasi SSL yang lebih jelas dengan indikator visual
- Ditambahkan kode warna untuk status SSL (hijau untuk valid, kuning untuk akan kedaluwarsa, merah untuk tidak valid)
- Ditampilkan informasi detail sertifikat SSL (penerbit, tanggal kedaluwarsa, hari tersisa)
- Ditambahkan pesan error jika sertifikat SSL bermasalah

### Peningkatan Antarmuka Pengguna
- Ditambahkan deskripsi untuk tombol-tombol pada CPT (Start, Pause, Stop, Check Now)
- Ditambahkan tooltip pada tombol untuk memudahkan pengguna memahami fungsi masing-masing tombol
- Ditambahkan penjelasan detail tentang fungsi setiap tombol di bawah panel tombol

### Peningkatan Pemeriksaan Domain
- Ditambahkan link WHOIS pada tampilan ketika pemeriksaan domain gagal
- Ditingkatkan tampilan pesan error pemeriksaan domain
- Ditambahkan petunjuk yang lebih jelas untuk input tanggal kedaluwarsa domain secara manual

### Sistem Versi Terpusat
- Dibuat sistem versi terpusat dengan DEFINE dari file induk plugin
- Diperbarui versi plugin dari 1.0.9 ke 1.1.0
- Diselaraskan versi di semua bagian plugin

### Dokumentasi
- Ditambahkan dokumentasi lengkap tentang plugin (README.md)
- Dibuat file roadmap pengembangan untuk monitoring server performance (ROADMAP.md)
- Ditambahkan changelog untuk versi 1.1.0

### Pembersihan Kode
- Dihapus file-file test/debug yang tidak terpakai
- Dioptimalkan struktur kode untuk performa yang lebih baik
- Ditingkatkan keamanan kode

## Bug Fixes
- Diperbaiki tampilan SSL yang tidak muncul dengan benar
- Diperbaiki beberapa kesalahan penulisan dan terjemahan

## Perubahan Internal
- Diperbarui struktur direktori plugin
- Ditingkatkan konsistensi penamaan fungsi dan variabel
- Ditingkatkan kualitas kode secara keseluruhan

---

© 2023 Budi Haryono. Semua hak dilindungi.