# 🕸️ MM Web Monitoring

<div align="center">

[![WordPress Plugin](https://img.shields.io/badge/WordPress-Plugin-blue.svg)](https://wordpress.org/)
[![Version](https://img.shields.io/badge/Version-1.0.7-green.svg)]()
[![License](https://img.shields.io/badge/License-GPL--2.0-orange.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)]()

> **Plugin WordPress yang powerful untuk memantau uptime, SSL, dan domain expiration website langsung dari dasbor WordPress Anda.**

*Forget expensive third-party monitoring services. Get enterprise-level website monitoring right in your WordPress dashboard!*

![MM Web Monitoring Screenshot](screenshot.png)

</div>

---

## 🚀 **Mengapa MM Web Monitoring?**

Bayangkan Anda mengelola 50+ website client, tiba-tiba ada 5 website down bersamaan di tengah malam. Dengan MM Web Monitoring, Anda akan tahu dalam hitungan menit dan bisa bertindak cepat. Plugin ini dirancang khusus untuk:

- 🏢 **Web Agencies** - Monitor semua website client dari satu tempat
- 💻 **Freelance Developers** - Jaga reputasi dengan uptime monitoring 24/7  
- 🛒 **E-commerce Owners** - Pastikan toko online selalu accessible
- 📰 **Content Creators** - Monitor blog dan website portfolio
- 🏗️ **System Administrators** - Centralized monitoring untuk multiple properties

---

## ✨ **Fitur Unggulan v1.0.7**

### 🔍 **Website Monitoring**
- **Smart Monitoring**: Response code check + HTML element verification
- **Flexible Intervals**: 5 minutes to 24 hours customizable per website
- **Real-time Status**: Live dashboard dengan auto-reload
- **Bulk Operations**: Monitor 100+ websites dengan bulk add/actions

### 📧 **Intelligent Email Notifications**  
- **Unified HTML Templates**: Professional, responsive email design
- **Smart Triggers**: Always notify vs. Error & Recovery only
- **Customizable Recipients**: Per-website or global email settings
- **Anti-spam Protection**: Rate limiting untuk prevent email flooding

### 🔒 **SSL Certificate Monitoring**
- **Auto SSL Check**: Monitor certificate expiration
- **Early Warnings**: 30/10/7 days before expiry alerts  
- **Certificate Details**: Issuer, expiry date, days remaining
- **Bulk SSL Status**: See all SSL status di satu view

### 🌐 **Domain Expiration Monitoring** ⭐ *NEW*
- **Smart Domain Detection**: Auto-extract root domain dari URL complex
- **Multi-TLD Support**: .com, .co.uk, .web.id, .my.id dan 1000+ TLD lainnya
- **WHOIS Integration**: Real-time domain expiry checking
- **Manual Override**: Input manual jika WHOIS gagal
- **10-Day Alerts**: Early warning sebelum domain expired

### ⚡ **Enhanced User Experience**
- **Consolidated Columns**: SSL Status + Expiry dalam satu kolom
- **Configurable Auto-reload**: 10-300 seconds customizable interval
- **Beautiful UI**: Modern, responsive admin interface
- **Bulk Management**: Select multiple websites untuk bulk operations
- **Inline Editing**: Quick edit langsung dari table view

### 🕐 **Global Daily Monitoring**
- **Daily Global Check**: Full SSL & domain check sekali sehari
- **Customizable Schedule**: Set jam berapa global check berjalan (00:00-23:00)
- **Smart Notifications**: Anti-duplicate untuk avoid spam
- **Comprehensive Coverage**: Auto-check semua active monitoring

---

## 📊 **Perfect For These Use Cases**

| Use Case | Benefit |
|----------|---------|
| **Agency Managing 50+ Client Sites** | Monitor all client websites from one dashboard, get instant alerts, professional email reports |
| **E-commerce Multi-store Owner** | Ensure all stores are online 24/7, SSL certificates valid, domains not expiring |
| **Freelance Developer** | Proactive monitoring = happy clients, early problem detection before clients notice |
| **Corporate IT Team** | Centralized monitoring for all company domains, SSL compliance, uptime SLA tracking |
| **Content Creator Network** | Monitor blog performance, ensure AdSense/affiliate sites always accessible |

---

## 🛠️ **Installation & Quick Start**

### **Method 1: WordPress Admin (Recommended)**
1. Download plugin `.zip` file
2. Go to **Plugins > Add New > Upload Plugin**
3. Choose file dan click **Install Now**
4. Click **Activate Plugin**

### **Method 2: FTP Upload**
```bash
# Extract and upload
unzip mm-web-monitoring.zip
# Upload via FTP to: /wp-content/plugins/
# Activate from WordPress admin
```

### **Quick Setup (5 Minutes)**
1. **Set Global Options**: `Web Monitoring > Global Options`
   - Default email for notifications
   - Auto-reload interval (30s recommended)
   - Daily global check time (2 AM recommended)

2. **Add First Website**: `Web Monitoring > Add New`
   - Target URL
   - Check type (Response Code or HTML Content)
   - Monitoring interval
   - Email settings

3. **Test & Go Live**: Click "Check Now" → Verify results → Start monitoring

---

## 📋 **Complete Feature List**

<details>
<summary><strong>🔍 Monitoring Features</strong></summary>

- ✅ HTTP Response Code Monitoring (200, 404, 500, etc.)
- ✅ HTML Content Verification (find specific elements)
- ✅ SSL Certificate Status & Expiry Monitoring  
- ✅ Domain Registration Expiry Monitoring
- ✅ Custom Monitoring Intervals (5min - 24hour)
- ✅ Real-time Status Dashboard
- ✅ Historical Check Results
- ✅ Downtime Detection & Recovery Notifications

</details>

<details>
<summary><strong>📧 Email & Notifications</strong></summary>

- ✅ HTML Email Templates (Professional Design)
- ✅ Customizable Email Recipients (Per-site or Global)
- ✅ Smart Notification Triggers (Always vs Error-only)
- ✅ Anti-spam Rate Limiting
- ✅ Email Delivery Logs
- ✅ Multiple Notification Types (Uptime, SSL, Domain)
- ✅ Responsive Email Design (Mobile-friendly)

</details>

<details>
<summary><strong>⚡ Management & UI</strong></summary>

- ✅ Bulk Add Websites (Paste URL list)
- ✅ Bulk Actions (Start/Stop/Check multiple sites)
- ✅ Inline Editing (Quick edit from table view)
- ✅ Advanced Filtering & Sorting
- ✅ Auto-refresh Dashboard (Configurable interval)
- ✅ Mobile-responsive Admin Interface
- ✅ Progress Indicators for Bulk Operations
- ✅ Export/Import Capabilities

</details>

<details>
<summary><strong>🔧 Advanced Features</strong></summary>

- ✅ WordPress Cron Integration
- ✅ Custom Post Type Architecture
- ✅ Plugin Hooks & Filters for Developers
- ✅ Error Handling & Logging
- ✅ Performance Optimized (Lightweight)
- ✅ Multi-language Ready (i18n)
- ✅ WordPress Security Best Practices
- ✅ Database Optimization

</details>

---

## 📖 **How to Use (Step by Step)**

### **1. Global Configuration**
Go to `Web Monitoring > Global Options`:
```php
✅ Default Email: admin@yoursite.com
✅ Auto-reload Interval: 30 seconds  
✅ Daily Global Check: 02:00 (2 AM)
```

### **2. Add Your First Website**
```
🌐 Target URL: https://example.com
🔍 Check Type: Response Code Only / Fetch HTML Content  
⏰ Interval: Every 15 minutes
📧 Email: custom@email.com (or use global default)
📬 Trigger: Always / On Error & Recovery
```

### **3. Enable Advanced Monitoring**
```
🔒 SSL Monitoring: ✅ (Auto-enabled)
🌐 Domain Monitoring: ✅ Enable checkbox
📅 Manual Domain Expiry: 2025-12-31 (if auto-check fails)
```

### **4. Bulk Management** 
```
📝 Bulk Add: Paste multiple URLs, one per line
⚡ Bulk Actions: Select multiple → Check Now/Start/Pause/Stop
🔄 Progress Tracking: Real-time progress bar
```

### **5. Monitor & Maintain**
- Dashboard auto-refreshes every 30 seconds (configurable)
- Receive email alerts for downtime, SSL expiry, domain expiry
- Daily global check ensures nothing is missed
- Click "Check Now" for instant verification

---

## 🚨 **Troubleshooting & FAQ**

<details>
<summary><strong>❓ Website shows "Check Failed" - what to do?</strong></summary>

1. **Check URL Format**: Must include `http://` or `https://`
2. **Server Connectivity**: Ensure your WordPress can make outbound connections
3. **Firewall Issues**: Check if target site blocks your server IP
4. **SSL Issues**: Use HTTP instead of HTTPS for testing
5. **HTML Selector**: If using HTML check, verify selector exists on page

</details>

<details>
<summary><strong>❓ Domain monitoring not working?</strong></summary>

1. **Try Manual Override**: Use date picker if auto-check fails
2. **Check TLD Support**: Standard TLDs (.com, .net) usually work best
3. **WHOIS Access**: Ensure server can access WHOIS servers
4. **Complex URLs**: Plugin auto-extracts root domain (admin.site.com → site.com)

</details>

<details>
<summary><strong>❓ Email notifications not sending?</strong></summary>

1. **Test WordPress Email**: Try other WordPress email functions
2. **Check Spam Folder**: HTML emails sometimes flagged as spam  
3. **SMTP Plugin**: Consider using SMTP plugin for better delivery
4. **Email Logs**: Check plugin email logs in website edit screen

</details>

<details>
<summary><strong>❓ Performance concerns with many websites?</strong></summary>

1. **Stagger Intervals**: Don't set all websites to same interval
2. **Use Longer Intervals**: 30min-1hour for stable sites
3. **Monitor CPU Usage**: Plugin is optimized but monitor server resources
4. **Database Cleanup**: Plugin auto-manages old data

</details>

---

## 🔧 **Advanced Configuration**

### **For Developers: Hooks & Filters**
```php
// Custom notification logic
add_filter('mmwm_should_send_notification', function($should_send, $post_id, $status) {
    // Your custom logic here
    return $should_send;
}, 10, 3);

// Modify email content
add_filter('mmwm_email_template_data', function($data, $type) {
    // Customize email data
    return $data;
}, 10, 2);

// Custom check intervals
add_filter('mmwm_custom_intervals', function($intervals) {
    $intervals['2min'] = '2 minutes';
    return $intervals;
});
```

### **Server Requirements**
- **WordPress**: 5.0+ (tested up to 6.4)
- **PHP**: 7.4+ (8.x recommended)
- **MySQL**: 5.6+ or MariaDB 10.0+
- **Outbound Connections**: Required for HTTP checks, SSL verification, WHOIS
- **Memory**: 64MB+ (128MB recommended for 100+ websites)
- **Cron**: WordPress cron must be working

---

## 📊 **Changelog**

### **🎉 Version 1.0.7** (2025-08-25)
**🌟 Major Feature Release - Domain Monitoring & UI Enhancements**

#### **✨ New Features**
- **🌐 Domain Expiration Monitoring**: Complete domain expiry tracking with smart TLD detection
- **🛠️ Manual Domain Override**: Date picker for manual domain expiry input when auto-check fails
- **🕐 Global Daily Monitoring**: Scheduled daily check for SSL & domain expiration at custom time
- **⚙️ Individual Monitoring Intervals**: Set custom interval per website (5min - 24hour)
- **🎨 Enhanced UI Design**: Beautiful radio buttons, better spacing, modern styling

#### **🔧 Improvements**  
- **📧 Unified Email Templates**: Consistent HTML email design across all notification types
- **📊 Consolidated SSL Column**: Combined SSL status + expiry date in single column for space efficiency
- **⏱️ Configurable Auto-reload**: Customizable dashboard refresh interval (10-300 seconds)
- **🔧 HTML Selector Fix**: Preserve HTML tags in textarea (no more tag stripping)
- **⚡ Performance Optimization**: Better error handling, reduced database queries

#### **🛡️ Error Handling**
- **🔄 Domain Check Fallback**: Auto-switch to manual input if WHOIS fails
- **📈 Better Validation**: Input validation for all settings
- **🚨 Comprehensive Logging**: Detailed error logs for troubleshooting
- **🛠️ Graceful Degradation**: Plugin works even if some features fail

#### **🐛 Bug Fixes**
- Fixed HTML selector sanitization issue
- Improved SSL certificate parsing accuracy  
- Better timezone handling for scheduling
- Enhanced mobile responsiveness

---

### **Version 1.0.6** (2024-11-XX)
#### **🆕 Added**
- **📧 Email Template System**: HTML email notifications with professional styling
- **🔒 SSL Certificate Monitoring**: Auto-check SSL expiry with early warnings
- **⚡ Bulk Operations**: Mass add websites and bulk action improvements
- **📱 Mobile Optimization**: Responsive admin interface

#### **🔧 Improved**
- **🚀 Performance**: Optimized database queries and caching
- **🎨 UI/UX**: Cleaner interface with better visual hierarchy
- **📊 Reporting**: Enhanced monitoring status display

---

### **Version 1.0.5** (2024-10-XX)  
#### **🆕 Added**
- **⏰ Advanced Scheduling**: Flexible monitoring intervals
- **📈 Dashboard Enhancements**: Real-time status updates
- **🔧 Error Handling**: Better error reporting and recovery

---

### **Version 1.0.0** (2024-09-XX)
#### **🎉 Initial Release**
- **🌐 Basic Website Monitoring**: HTTP response code checking
- **📧 Email Notifications**: Basic email alerts for downtime
- **⚙️ WordPress Integration**: Custom post type for website management
- **📊 Admin Dashboard**: Simple monitoring overview

---

## 🤝 **Contributing & Support**

### **🐛 Found a Bug?**
1. Check existing issues on GitHub
2. Create detailed bug report with:
   - WordPress version
   - Plugin version  
   - Steps to reproduce
   - Expected vs actual behavior

### **💡 Feature Requests**
1. Search existing feature requests
2. Explain the use case and benefit
3. Provide detailed requirements

### **🔧 Want to Contribute Code?**
1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Follow WordPress coding standards
4. Test thoroughly
5. Submit pull request

### **📧 Need Help?**
- 📖 **Documentation**: Read this README thoroughly  
- 🐛 **Bug Reports**: GitHub Issues
- 💬 **General Questions**: WordPress.org support forum
- 🚀 **Custom Development**: Contact for custom modifications

---

## 📜 **License & Credits**

**License**: GPL-2.0+ - [View License](LICENSE)

**Credits**:
- WordPress Community for the amazing platform
- Contributors who helped improve this plugin
- Beta testers who provided valuable feedback

**Disclaimer**: This plugin is provided "as is" without warranty. Always test in staging environment before production use.

---

<div align="center">

**⭐ If this plugin helps you, please consider giving it a star on GitHub! ⭐**

*Built with ❤️ for the WordPress community*

[![Star on GitHub](https://img.shields.io/github/stars/yourusername/mm-web-monitoring?style=social)](https://github.com/yourusername/mm-web-monitoring)

</div>