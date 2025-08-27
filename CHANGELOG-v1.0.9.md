# 📋 **Changelog - MM Web Monitoring v1.0.9**

## [1.0.9] - 2025-09-05

### ✨ **Added**
- **FEATURE**: Menambahkan tombol "Click Enable Domain Expiry Monitoring" untuk mempermudah aktivasi pemantauan domain
- **FEATURE**: Implementasi AJAX handler untuk pemeriksaan domain secara real-time
- **FEATURE**: Tampilan status pemeriksaan domain (berhasil/gagal) yang lebih informatif

### ⚡ **Improved**
- **ENHANCEMENT**: Peningkatan antarmuka pengguna untuk pemantauan domain
- **ENHANCEMENT**: Instruksi yang lebih jelas pada checkbox "Enable domain expiration monitoring"
- **ENHANCEMENT**: Penyimpanan tanggal kedaluwarsa domain yang lebih efisien ke database
- **PERFORMANCE**: Optimasi cron job untuk pemeriksaan domain 30 hari sebelum kedaluwarsa

### 🔧 **Fixed**
- **BUG**: Memperbaiki masalah pada handler AJAX untuk pemantauan domain
- **BUG**: Mengatasi masalah pada penghentian cron pemeriksaan WHOIS saat pengguna menonaktifkan checkbox

### 🔒 **Security**
- **SECURITY**: Peningkatan validasi input pada form pemantauan domain
- **SECURITY**: Penambahan nonce untuk verifikasi permintaan AJAX

### 📚 **Documentation**
- **DOCS**: Dokumentasi kode yang lebih lengkap untuk fitur pemantauan domain
- **DOCS**: Panduan pengguna yang diperbarui untuk fitur pemantauan domain

### 🧹 **Maintenance**
- **CLEANUP**: Penyederhanaan kode JavaScript untuk pemantauan domain
- **CLEANUP**: Pengorganisasian kode yang lebih baik untuk handler AJAX