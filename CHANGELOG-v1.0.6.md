# MM Web Monitoring Plugin v1.0.6 - New Features

## 🚀 New Features Added

### 1. **Auto-Reload All Websites Page**
- **Fitur**: Halaman "All Websites" otomatis reload setiap 30 detik untuk monitoring realtime
- **Benefit**: Monitor perubahan status website tanpa perlu refresh manual
- **Implementation**: JavaScript auto-reload dengan indicator visual

### 2. **Countdown Timer untuk Next Check**
- **Fitur**: Kolom "Next Check" menampilkan countdown timer realtime (jam:menit:detik)
- **Benefit**: Melihat waktu persis kapan website akan dicek selanjutnya
- **Implementation**: JavaScript countdown yang update setiap detik

### 3. **Bulk Action "Check Now"**
- **Fitur**: Dropdown "Bulk Actions" sekarang memiliki opsi:
  - Check Now - Cek multiple website sekaligus
  - Start Monitoring - Mulai monitoring multiple website
  - Pause Monitoring - Pause monitoring multiple website
  - Stop Monitoring - Stop monitoring multiple website
- **Benefit**: Efisien untuk mengelola banyak website sekaligus
- **Implementation**: WordPress native bulk actions dengan progress indicator

### 4. **SSL Certificate Monitoring**
- **Fitur Lengkap**:
  - ✅ **SSL Status Column**: Menampilkan status SSL (ACTIVE/INACTIVE/EXPIRING SOON/EXPIRED)
  - ✅ **SSL Expiry Column**: Menampilkan tanggal expired dan sisa hari
  - ✅ **Automatic SSL Check**: SSL dicek otomatis saat website monitoring berjalan
  - ✅ **SSL Expiring Notification**: Email otomatis 10 hari sebelum SSL expired
  - ✅ **SSL Issuer Information**: Menampilkan penerbit sertifikat SSL

## 📊 SSL Monitoring Details

### Status SSL yang Ditampilkan:
- **ACTIVE** (hijau): SSL aktif dan valid
- **EXPIRING SOON** (kuning): SSL akan expired dalam 10 hari
- **EXPIRED** (merah): SSL sudah expired
- **INACTIVE** (abu-abu): SSL tidak aktif atau website non-HTTPS

### Notifikasi SSL:
- Email otomatis dikirim 10 hari sebelum SSL expired
- Tidak akan spam (max 1 email per 24 jam per website)
- Menggunakan email setting yang sama dengan monitoring website

### Meta Data SSL yang Disimpan:
- `_mmwm_ssl_is_active`: Status aktif SSL (1/0)
- `_mmwm_ssl_error`: Error message jika SSL bermasalah
- `_mmwm_ssl_expiry_date`: Tanggal expired SSL
- `_mmwm_ssl_days_until_expiry`: Sisa hari sebelum expired
- `_mmwm_ssl_issuer`: Penerbit sertifikat (Let's Encrypt, DigiCert, dll)
- `_mmwm_ssl_last_check`: Timestamp last check SSL
- `_mmwm_ssl_last_notification`: Timestamp last notification sent

## 🎨 User Interface Improvements

### Enhanced JavaScript:
- Auto-reload dengan indicator visual
- Countdown timer realtime
- Bulk action progress dengan log detail
- Enhanced error handling dan user feedback

### Enhanced CSS:
- Responsive design untuk mobile
- Better styling untuk status badges
- Loading indicators dan animations
- Consistent color scheme

## 🔧 Technical Implementation

### New Files Added:
1. `includes/interfaces/interface-mmwm-ssl-checker.php` - SSL checker interface
2. `includes/monitoring/class-mmwm-ssl-checker.php` - SSL checker implementation
3. `includes/assets/admin-enhanced.js` - Enhanced JavaScript functionality
4. `includes/assets/admin-enhanced.css` - Enhanced CSS styling

### Enhanced Files:
1. `includes/class-mmwm-admin.php` - Added SSL columns, bulk actions, enhanced scripts
2. `includes/class-mmwm-core.php` - Added SSL dependencies and bulk action hooks
3. `includes/monitoring/class-mmwm-checker.php` - Added SSL checking integration
4. `includes/monitoring/class-mmwm-scheduler.php` - Added get_next_check_timestamp method
5. `mm-web-monitoring.php` - Updated version to 1.0.6

## 🚦 How to Use New Features

### 1. SSL Monitoring:
- SSL monitoring otomatis aktif untuk semua HTTPS website
- Lihat status di kolom "SSL Status" dan "SSL Expiry"
- Email notifikasi otomatis dikirim 10 hari sebelum expired

### 2. Bulk Actions:
- Pilih multiple website dengan checkbox
- Pilih action dari dropdown "Bulk Actions"
- Klik "Apply" untuk eksekusi
- Lihat progress realtime di log

### 3. Realtime Monitoring:
- Halaman auto-reload setiap 30 detik
- Countdown timer update setiap detik
- Visual indicator saat reload

## 🔍 Testing

### SSL Checker Test:
```bash
php test-ssl-checker.php
```

### WordPress Integration:
1. Activate plugin
2. Add HTTPS website
3. Check SSL columns in All Websites page
4. Test bulk actions
5. Verify auto-reload functionality

## 📈 Performance Notes

- SSL checking menggunakan stream_socket_client untuk performance
- Auto-reload hanya berjalan saat tab aktif (document.hidden check)
- Bulk actions menggunakan rate limiting (1 detik delay antar request)
- SSL notifications limited to 1 per 24 hours per website

## 🛡️ Security Features

- All AJAX calls protected with nonce verification
- Input sanitization untuk SSL data
- Proper capability checks untuk bulk actions
- Error handling untuk SSL connection failures

---

**Version**: 1.0.6
**Compatibility**: WordPress 6.0+, PHP 7.4+
**Branch**: v.1.0.6 (safe for development)
