# 📋 **Changelog - MM Web Monitoring**

All notable changes to MM Web Monitoring plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

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

---

## [1.0.8] - 2025-08-26

### 🔧 **Fixed**
- **CRITICAL**: Fixed fatal plugin activation errors that prevented plugin from loading
- **BUG**: Resolved "Website URL not found" AJAX errors in admin interface  
- **BUG**: Fixed incorrect meta field reference (_mmwm_website_url → _mmwm_target_url)
- **BUG**: Corrected CPT Domain Monitoring field functionality
- **BUG**: Fixed JavaScript event handler conflicts in admin interface
- **BUG**: Resolved AJAX handler registration issues causing admin errors

### ⚡ **Improved**
- **PERFORMANCE**: Enhanced plugin initialization with proper error handling
- **PERFORMANCE**: Optimized hook registration timing for better compatibility
- **PERFORMANCE**: Improved admin table column rendering performance
- **STABILITY**: Strengthened plugin activation/deactivation workflow
- **STABILITY**: Enhanced admin interface reliability and error handling

### 🔒 **Security**
- **SECURITY**: Improved AJAX handler validation and security checks
- **SECURITY**: Enhanced admin capability verification for sensitive operations
- **SECURITY**: Strengthened input sanitization in admin forms

### 📚 **Documentation**
- **DOCS**: Enhanced code documentation with better inline comments
- **DOCS**: Improved error handling documentation for debugging
- **DOCS**: Updated installation and troubleshooting guides

### 🧹 **Maintenance**
- **CLEANUP**: Removed experimental domain expiry enable button feature due to conflicts
- **CLEANUP**: Simplified admin interface to prevent JavaScript conflicts
- **CLEANUP**: Cleaned up unused AJAX handlers and event listeners
- **CLEANUP**: Optimized admin JavaScript for better performance

### 🛠️ **Technical Changes**
- Reverted complex AJAX implementations that interfered with CPT functionality
- Simplified admin UI to use instructional text instead of interactive buttons
- Enhanced plugin class initialization with comprehensive error handling
- Improved compatibility with WordPress core CPT and meta field systems

---

## [1.0.7] - 2025-08-25

### ✨ **Added**
- **NEW**: Complete website uptime monitoring system
- **NEW**: SSL certificate expiration monitoring
- **NEW**: Domain registration expiration tracking  
- **NEW**: Custom Post Type for website management
- **NEW**: Professional email notification system
- **NEW**: Responsive admin dashboard with real-time status
- **NEW**: Bulk operations for website management
- **NEW**: Customizable monitoring intervals (5 minutes to 24 hours)
- **NEW**: Smart notification triggers (Always notify vs Error & Recovery)
- **NEW**: Anti-spam email protection with rate limiting

### 🎨 **Design**
- **UI**: Modern, responsive admin interface design
- **UI**: Professional HTML email templates
- **UI**: Intuitive website status indicators
- **UI**: Clean, organized settings panels

### 🚀 **Features**
- **MONITORING**: HTTP response code checking
- **MONITORING**: HTML element verification for content validation
- **MONITORING**: SSL certificate validation and expiry tracking
- **MONITORING**: Domain whois lookup for expiration dates
- **NOTIFICATIONS**: Unified email system with HTML templates
- **DASHBOARD**: Real-time status overview with auto-refresh
- **MANAGEMENT**: Bulk add, edit, delete operations for websites

---

## [1.0.6] - 2025-08-24

### 🧪 **Development**
- **DEV**: Initial beta release for testing
- **DEV**: Core plugin architecture development
- **DEV**: Basic monitoring functionality implementation

---

## [1.0.5] - 2025-08-23

### 🏗️ **Foundation**
- **INIT**: Plugin foundation and structure setup
- **INIT**: WordPress compatibility framework
- **INIT**: Basic admin interface skeleton

---

## [1.0.0] - 2025-08-20

### 🎉 **Initial Release**
- **INIT**: Project inception and initial development
- **INIT**: Core concept and architecture planning
- **INIT**: WordPress plugin framework setup
