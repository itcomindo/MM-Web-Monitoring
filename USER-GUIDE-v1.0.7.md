# MM Web Monitoring Plugin v1.0.7 - User Guide

## Fitur Baru yang Ditambahkan

### 1. 📧 Email Notifikasi yang Konsisten

**Masalah yang diperbaiki**: Email notifikasi sebelumnya tidak konsisten antara format HTML dan plain text.

**Solusi**: Semua email sekarang menggunakan template HTML yang unified dan professional.

**Contoh Email Baru**:
- Format HTML responsif dengan styling yang konsisten
- Color coding untuk status (hijau=UP, merah=DOWN, kuning=WARNING)
- Informasi lengkap termasuk timestamp, reason, dan host information
- Professional branding dengan plugin logo

---

### 2. 🌐 Monitoring Domain Expiration

**Fitur Baru**: Plugin sekarang bisa memonitor kapan domain akan expired.

#### Cara Mengaktifkan:
1. Buka post editor untuk website yang ingin dimonitor
2. Scroll ke meta box "Website Configuration"
3. Centang checkbox **"Enable Domain Monitoring"**
4. Save post

#### Cara Kerja:
- Plugin akan otomatis extract root domain dari URL (contoh: `admin.example.com` → `example.com`)
- Support complex TLD seperti `.co.uk`, `.web.id`, `.my.id`
- Check WHOIS data untuk mendapatkan expiry date
- Kirim email notification jika domain akan expired dalam 30 hari

#### Domain yang Didukung:
- `.com`, `.net`, `.org` (standard TLD)
- `.co.uk`, `.org.uk` (UK domains)  
- `.web.id`, `.my.id`, `.co.id` (Indonesian domains)
- Dan banyak TLD lainnya

---

### 3. 📊 Konsolidasi Kolom SSL

**Perubahan**: Kolom "SSL Status" dan "SSL Expiry" digabung menjadi satu.

#### Sebelum:
- Kolom terpisah: "SSL Status" | "SSL Expiry"
- Memakan banyak ruang di tabel

#### Sekarang:
- Satu kolom: **"SSL Certificate"**
- Format: `Valid (Expires: 2024-12-31)` atau `Invalid/Expired`
- Color coding: hijau=valid, merah=expired/invalid

---

### 4. ⏰ Configurable Auto-Reload Interval

**Fitur Baru**: Sekarang bisa mengatur interval auto-reload halaman monitoring.

#### Cara Setting:
1. Go to **"MM Web Monitoring" → "Global Options"**
2. Cari section **"Auto-Reload Settings"**
3. Set interval (10-300 detik, default: 30 detik)
4. Save Changes

#### Fitur:
- Real-time update interval tanpa reload page
- Visual indicator menampilkan interval yang aktif
- Hanya berlaku di halaman "All Websites"
- Auto-pause jika tab tidak aktif (background)

---

## Cara Menggunakan Fitur Baru

### Email Template Testing
Untuk test email template yang baru:
1. Buat website baru atau edit yang existing
2. Set notification trigger ke "Always"
3. Klik "Check Now" untuk trigger email
4. Cek inbox untuk format email yang baru

### Domain Monitoring Setup
1. **Single Website**:
   - Edit website post
   - Enable "Domain Monitoring" checkbox
   - Save

2. **Bulk Setup**:
   - Bisa menggunakan bulk edit untuk enable domain monitoring di multiple websites sekaligus

### SSL Column Display
- Column akan otomatis update dengan format baru
- Tidak perlu setting tambahan
- Support sorting by SSL status

### Auto-Reload Configuration
- Setting tersimpan global untuk semua user
- Berlaku immediate setelah save
- Reset browser untuk apply setting baru

---

## Troubleshooting

### Domain Monitoring Tidak Berfungsi
1. **Check URL Format**: Pastikan URL lengkap dengan `http://` atau `https://`
2. **Check TLD Support**: Coba dengan domain standard (.com, .net) dulu
3. **Check WHOIS Access**: Pastikan server bisa akses WHOIS servers
4. **Check Logs**: Lihat error logs di WordPress admin

### Email Tidak Terkirim
1. **Check SMTP Settings**: Pastikan WordPress bisa kirim email
2. **Check Email Address**: Pastikan email notification address valid
3. **Check Spam Folder**: Email HTML mungkin masuk spam
4. **Test dengan Plugin Lain**: Test wp_mail() function

### Auto-Reload Tidak Berfungsi
1. **Check JavaScript**: Pastikan JavaScript enabled di browser
2. **Check Console**: Buka browser console untuk error messages
3. **Check Setting Value**: Pastikan interval setting dalam range 10-300
4. **Clear Cache**: Clear browser cache dan reload page

### SSL Column Display Issue
1. **Refresh Page**: Column update otomatis saat reload
2. **Check SSL Data**: Pastikan SSL checker berfungsi normal
3. **Browser Cache**: Clear cache jika display tidak update

---

## Changelog Summary

✅ **Fixed**: Email notifications sekarang konsisten HTML format  
✅ **Added**: Domain expiration monitoring dengan smart TLD detection  
✅ **Improved**: SSL columns digabung untuk save space  
✅ **Added**: Configurable auto-reload interval (10-300 seconds)  
✅ **Enhanced**: Better error handling dan logging  
✅ **Updated**: Professional email templates dengan branding  

---

## Compatibility

- **WordPress**: 5.0+
- **PHP**: 7.4+
- **Browsers**: Chrome, Firefox, Safari, Edge (modern browsers)
- **Server Requirements**: cURL enabled, outbound connections allowed

---

## Support

Jika ada issue atau pertanyaan:
1. Check WordPress error logs
2. Enable debugging: `define('WP_DEBUG', true);`
3. Test dengan theme default
4. Disable other plugins untuk isolate issue

**Version**: 1.0.7  
**Last Updated**: ${new Date().toISOString().split('T')[0]}
