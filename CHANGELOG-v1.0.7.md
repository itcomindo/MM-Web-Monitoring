# MM Web Monitoring Plugin - Changelog v1.0.7

## Fitur Baru & Perbaikan

### 1. ✅ Email Notifikasi yang Konsisten
- **Masalah**: Email notifikasi tidak konsisten antara HTML dan plain text
- **Solusi**: 
  - Dibuat unified email template system (`MMWM_Email_Template`)
  - Semua email sekarang menggunakan format HTML yang konsisten
  - Template yang sama untuk monitoring reports, SSL expiring, dan domain expiring
  - Design responsif dengan styling yang professional

### 2. ✅ Monitoring Domain Expiration
- **Fitur Baru**: Monitoring kapan domain akan expired
- **Implementasi**:
  - Interface `MMWM_Domain_Checker` untuk standardisasi
  - Class `MMWM_Domain_Checker` dengan intelligent root domain detection
  - Support untuk complex TLD seperti `.co.uk`, `.web.id`, `.my.id`
  - Checkbox "Enable Domain Monitoring" di post meta boxes
  - Automatic domain checking saat save post
  - Email notification untuk domain yang akan expired

### 3. ✅ Consolidation SSL Columns
- **Masalah**: Kolom SSL Status dan SSL Expiry terpisah memakan banyak ruang
- **Solusi**:
  - Digabung menjadi satu kolom "SSL Certificate"
  - Menampilkan status dan expiry date dalam format yang compact
  - Color coding untuk easy identification (hijau=valid, merah=expired/invalid)

### 4. ✅ Configurable Auto-Reload Interval
- **Fitur Baru**: Field input untuk menentukan kapan auto reload
- **Implementasi**:
  - Setting baru `mmwm_auto_reload_interval` di Global Options
  - Range 10-300 detik dengan default 30 detik
  - JavaScript yang dinamis menggunakan interval dari setting
  - Visual indicator menampilkan interval yang aktif

## File-File Yang Dibuat/Dimodifikasi

### File Baru:
1. `includes/interfaces/interface-mmwm-domain-checker.php` - Interface untuk domain checking
2. `includes/utilities/class-mmwm-email-template.php` - Unified email template system
3. `includes/monitoring/class-mmwm-domain-checker.php` - Domain expiration monitoring

### File Yang Dimodifikasi:
1. `mm-web-monitoring.php` - Version bump ke 1.0.7
2. `includes/class-mmwm-core.php` - Load dependencies baru
3. `includes/class-mmwm-admin.php` - Consolidate SSL columns, auto-reload setting
4. `includes/class-mmwm-cpt.php` - Tambah domain monitoring checkbox
5. `includes/assets/admin-enhanced.js` - Dynamic auto-reload interval

## Dependency Baru:
- Domain WHOIS checking capability
- Enhanced email template system
- Intelligent TLD parsing dengan public suffix list
- Configurable timing system

## Backward Compatibility:
- Semua fitur existing tetap berfungsi
- Email template baru tidak mempengaruhi existing notifications
- Default auto-reload tetap 30 detik jika setting tidak diset
- Domain monitoring bersifat optional (checkbox)

## Testing Recommendations:
1. Test email notifications untuk semua jenis (monitoring, SSL, domain)
2. Test domain monitoring dengan berbagai TLD (.com, .co.uk, .web.id, dll)
3. Test consolidated SSL column display
4. Test auto-reload dengan interval yang berbeda-beda
5. Test backward compatibility dengan data existing

## Security Enhancements:
- Proper nonce verification di semua forms
- Input sanitization untuk auto-reload interval
- Secure WHOIS query implementation
- Safe email template rendering

---

**Version**: 1.0.7  
**Release Date**: ${new Date().toISOString().split('T')[0]}  
**Compatibility**: WordPress 5.0+ | PHP 7.4+
