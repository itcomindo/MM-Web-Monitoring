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

## 🚀 **Why MM Web Monitoring?**

Imagine managing 50+ client websites when suddenly 5 websites go down simultaneously in the middle of the night. With MM Web Monitoring, you'll know within minutes and can act quickly. This plugin is specifically designed for:

- 🏢 **Web Agencies** - Monitor all client websites from one dashboard
- 💻 **Freelance Developers** - Maintain reputation with 24/7 uptime monitoring  
- 🛒 **E-commerce Owners** - Ensure online stores are always accessible
- 📰 **Content Creators** - Monitor blogs and portfolio websites
- 🏗️ **System Administrators** - Centralized monitoring for multiple properties

---

## 🛡️ **The Problem with External Monitoring Services**

### **Real-World Challenge: Security vs. Monitoring**

Many website owners rely on external monitoring services like **Netumo.app**, **UptimeRobot**, or similar platforms. While these services work well in basic scenarios, they face a critical limitation in today's security-conscious environment.

**The Security Dilemma:**
- 🚨 **Increased Brute Force Attacks**: Rising cybersecurity threats require stricter website protection
- � **Geo-blocking Requirements**: Many sites now block traffic from specific countries
- 🤖 **Bot Protection**: Advanced security rules block automated requests
- 🔥 **Cloudflare Security**: WAF rules and rate limiting can block monitoring services
- 🚫 **False Negatives**: External monitors report "down" when sites are actually protected, not down

### **The Traditional Workaround Problems:**
1. **IP Whitelisting Complexity**: Managing dozens of monitoring service IPs
2. **Service IP Changes**: Monitoring services change IPs without notice
3. **Multiple Security Layers**: Cloudflare + server + application level blocking
4. **Cost Escalation**: Premium plans required for IP whitelisting features

### **Our Solution: Self-Hosted Monitoring**

MM Web Monitoring solves this by monitoring from **your own WordPress installation**:
- ✅ **Known IP Address**: Your monitoring server IP is under your control
- ✅ **Easy Cloudflare Allowlisting**: Simple security rule configuration
- ✅ **No External Dependencies**: No third-party service disruptions
- ✅ **Cost Effective**: One-time plugin cost vs. recurring subscriptions
- ✅ **Complete Control**: Custom monitoring logic for your specific needs

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

## ☁️ **Special Setup for Cloudflare Users**

### **The Cloudflare Challenge**

If your monitored websites use Cloudflare with security rules (geo-blocking, bot protection, WAF), external monitoring services often get blocked. MM Web Monitoring solves this by monitoring from your own server, but you need to configure Cloudflare to allow your monitoring server.

### **Solution: Allowlist Your Monitoring Server**

#### **Step 1: Identify Your Monitoring Server**
```bash
# Method 1: Check your monitoring server's IP
curl -4 icanhazip.com

# Method 2: Use WordPress admin
# Go to: Web Monitoring > Global Options
# Your server IP will be displayed in the info section
```

#### **Step 2: Create Cloudflare Security Rule**

1. **Login to Cloudflare Dashboard**
2. **Select your domain** → Go to **Security** → **WAF**
3. **Create Custom Rule** with these settings:

**Rule Name**: `Allow MM Web Monitoring Server`

**Expression Builder**:
```javascript
(ip.src eq YOUR_MONITORING_SERVER_IP) or 
(cf.client.bot eq false and http.user_agent contains "WordPress")
```

**Alternative Advanced Expression**:
```javascript
(ip.src eq YOUR_MONITORING_SERVER_IP) or
(http.host eq "yourmonitoringdomain.com" and http.user_agent contains "MM-Web-Monitoring")
```

**Action**: `Allow`
**Priority**: `1` (highest priority)

#### **Step 3: Configure HTTP User Agent (Optional)**

Add this to your monitoring server's wp-config.php:
```php
// Custom User Agent for MM Web Monitoring
define('MMWM_USER_AGENT', 'MM-Web-Monitoring/1.0.7 (WordPress/' . get_bloginfo('version') . ')');
```

#### **Step 4: Hostname-Based Allowlisting (Recommended)**

**For Advanced Users:**
1. **Create dedicated subdomain** for monitoring: `monitor.yourdomain.com`
2. **Point subdomain** to your monitoring server IP
3. **Create Cloudflare Rule**:
```javascript
(http.x_forwarded_for contains "monitor.yourdomain.com") or 
(cf.client.bot eq false and http.referer contains "monitor.yourdomain.com")
```

### **Testing Your Cloudflare Configuration**

#### **Verification Steps:**
```bash
# Test 1: Direct IP check
curl -I https://yourprotectedsite.com

# Test 2: From monitoring server
# SSH to your monitoring server and run:
curl -I -H "User-Agent: MM-Web-Monitoring/1.0.7" https://yourprotectedsite.com

# Expected: HTTP/1.1 200 OK (not 403 Forbidden)
```

#### **Common Cloudflare Rule Examples:**

**Basic IP Allowlist:**
```javascript
(ip.src eq 203.0.113.1)
```

**Multiple Monitoring Servers:**
```javascript
(ip.src in {203.0.113.1 203.0.113.2 203.0.113.3})
```

**Hostname + User Agent:**
```javascript
(http.host eq "monitor.yourdomain.com" and http.user_agent contains "MM-Web-Monitoring")
```

**Advanced Security (recommended):**
```javascript
(ip.src eq 203.0.113.1 and http.user_agent contains "MM-Web-Monitoring" and http.x_forwarded_for contains "monitor.yourdomain.com")
```

### **Troubleshooting Cloudflare Issues**

<details>
<summary><strong>🚨 Monitoring shows "DOWN" but site is accessible</strong></summary>

**Cause**: Cloudflare is blocking your monitoring requests

**Solutions**:
1. Check Cloudflare **Firewall Events** → Look for blocked requests from your monitoring IP
2. Verify your security rule is **active** and has **highest priority**
3. Test rule syntax in **Expression Editor**
4. Ensure monitoring server IP is correct (dynamic IPs change)

</details>

<details>
<summary><strong>🔧 Security Rule Not Working</strong></summary>

**Debugging Steps**:
1. **Rule Priority**: Ensure allow rule has priority 1
2. **IP Format**: Use IPv4 format (203.0.113.1) not IPv6
3. **Expression Syntax**: Test in Cloudflare Expression Editor
4. **Rule Deployment**: Wait 2-3 minutes for rule propagation

</details>

<details>
<summary><strong>📡 Dynamic IP Monitoring Server</strong></summary>

**For Dynamic IPs**:
1. **Use Hostname-based rules** instead of IP-based
2. **Configure DDNS** for consistent hostname resolution
3. **Monitor IP changes** and update Cloudflare rules automatically
4. **Consider static IP** from hosting provider

</details>

### **Best Practices for Cloudflare + MM Web Monitoring**

✅ **Use dedicated monitoring subdomain**  
✅ **Implement hostname-based allowlisting**  
✅ **Set highest priority for allow rules**  
✅ **Test after every Cloudflare configuration change**  
✅ **Monitor Cloudflare firewall events regularly**  
✅ **Document your security rules for team members**  
✅ **Use specific User-Agent strings for identification**  
✅ **Consider static IP for monitoring server**  

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

## ❓ **Frequently Asked Questions (FAQ)**

<details>
<summary><strong>Q: Why should I choose MM Web Monitoring over external services?</strong></summary>

**A**: External monitoring services often face these challenges:
- **Blocked by Cloudflare** and other security systems
- **Limited customization** and rigid check intervals
- **Data privacy concerns** with third-party monitoring
- **Cost scaling issues** for multiple websites
- **Geographic limitations** affecting accuracy

MM Web Monitoring monitors from **your own server**, giving you complete control and bypassing security restrictions.

</details>

<details>
<summary><strong>Q: How does domain expiration monitoring work?</strong></summary>

**A**: The plugin uses multiple methods:
1. **WHOIS lookup** - Primary method for accurate expiration dates
2. **Manual date picker** - Fallback when WHOIS fails
3. **Smart TLD parsing** - Handles various domain extensions (.com, .org, .co.uk, etc.)
4. **Error handling** - Graceful fallback when domain data unavailable

</details>

<details>
<summary><strong>Q: Can I set different monitoring intervals for each website?</strong></summary>

**A**: Yes! Each website can have its own monitoring schedule:
- **Critical sites**: Every 5 minutes
- **Standard sites**: Every hour
- **Less critical**: Every 6-24 hours
- **Custom intervals**: From 5 minutes to 24 hours

</details>

<details>
<summary><strong>Q: What happens if my server has dynamic IP?</strong></summary>

**A**: For Cloudflare users with dynamic IP:
1. Use **hostname-based allowlisting** instead of IP-based
2. Set up **Dynamic DNS (DDNS)** for consistent hostname
3. Configure **subdomain monitoring** (monitor.yourdomain.com)
4. Update Cloudflare rules when IP changes

</details>

<details>
<summary><strong>Q: How many websites can I monitor?</strong></summary>

**A**: No artificial limits! Performance depends on:
- **Server resources** (CPU, memory, bandwidth)
- **Monitoring intervals** (more frequent = more resources)
- **Check complexity** (simple HTTP vs full page analysis)
- **Email notification frequency**

Typical servers handle 50-200 websites comfortably.

</details>

<details>
<summary><strong>Q: Does it work with SSL certificates from Let's Encrypt?</strong></summary>

**A**: Yes! MM Web Monitoring checks **any SSL certificate** including:
- **Let's Encrypt** (free certificates)
- **Commercial certificates** (Comodo, DigiCert, etc.)
- **Wildcard certificates**
- **Multi-domain (SAN) certificates**
- **Self-signed certificates** (with warnings)

</details>

---

## 🛠️ **Troubleshooting Guide**

### **Common Issues & Solutions**

<details>
<summary><strong>🚨 Website shows "DOWN" but is accessible in browser</strong></summary>

**Possible Causes**:
1. **Cloudflare blocking** monitoring requests
2. **Server IP blocked** by target website
3. **User-Agent restrictions** on target site
4. **Geographic restrictions** (geo-blocking)

**Solutions**:
1. Configure Cloudflare allowlisting (see guide above)
2. Check target website's firewall logs
3. Use custom User-Agent in WordPress config
4. Verify monitoring server location/IP reputation

</details>

<details>
<summary><strong>🔧 Domain expiration shows "Unknown" or wrong date</strong></summary>

**Possible Causes**:
1. **WHOIS server restrictions** for certain TLDs
2. **Privacy protection** hiding domain information
3. **Network connectivity** issues from monitoring server
4. **Rate limiting** from WHOIS servers

**Solutions**:
1. **Use manual date picker** as fallback
2. **Check domain privacy settings**
3. **Verify server internet connectivity**
4. **Space out domain checks** to avoid rate limits

</details>

<details>
<summary><strong>📧 Email notifications not working</strong></summary>

**Possible Causes**:
1. **WordPress mail function** not configured
2. **SMTP settings** incorrect or missing
3. **Email marked as spam** by recipient
4. **Server mail restrictions**

**Solutions**:
1. **Install SMTP plugin** (WP Mail SMTP recommended)
2. **Configure proper SMTP credentials**
3. **Add sender to email whitelist**
4. **Check server mail logs**
5. **Test with WordPress mail function**

</details>

<details>
<summary><strong>⏰ Monitoring not running automatically</strong></summary>

**Possible Causes**:
1. **WordPress cron disabled** or not triggering
2. **Server-level cron conflicts**
3. **Plugin cron not scheduled** properly
4. **Cache plugins interfering**

**Solutions**:
1. **Enable WordPress cron** in wp-config.php
2. **Set up server-level cron** for wp-cron.php
3. **Check plugin activation** and re-save settings
4. **Clear cache** and test manual triggers

</details>

<details>
<summary><strong>🎨 Radio buttons not styling properly</strong></summary>

**Possible Causes**:
1. **Theme CSS conflicts** overriding plugin styles
2. **CSS caching** not updated
3. **JavaScript not loading** properly

**Solutions**:
1. **Clear all caches** (page, plugin, server)
2. **Check browser console** for JavaScript errors
3. **Test with default theme** to isolate conflicts
4. **Force CSS refresh** with hard reload (Ctrl+F5)

</details>

### **Performance Optimization**

<details>
<summary><strong>⚡ Optimizing for Large Numbers of Websites</strong></summary>

**Server Resource Management**:
```php
// Add to wp-config.php for large monitoring setups
define('MMWM_MAX_CONCURRENT_CHECKS', 5);  // Limit simultaneous checks
define('MMWM_TIMEOUT_SECONDS', 30);       // Reduce timeout for faster processing
define('MMWM_BATCH_SIZE', 10);            // Process websites in batches
```

**Recommended Settings**:
- **Stagger monitoring intervals** across websites
- **Use longer intervals** for stable sites
- **Monitor critical sites more frequently**
- **Group checks by server response time**

</details>

<details>
<summary><strong>📊 Database Optimization</strong></summary>

**Clean Up Old Data**:
```sql
-- Remove old monitoring results (optional)
DELETE FROM wp_postmeta 
WHERE meta_key LIKE 'mmwm_%' 
AND meta_value LIKE '%last_check%' 
AND post_id IN (
    SELECT ID FROM wp_posts 
    WHERE post_date < DATE_SUB(NOW(), INTERVAL 6 MONTH)
);
```

**Index Optimization**:
- Plugin automatically creates necessary indexes
- **Monitor database size** with large datasets
- **Archive old results** if needed

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

## 💡 **Pro Tips & Best Practices**

### **🎯 Monitoring Strategy**

**Interval Optimization:**
- **Critical business sites**: 5-15 minutes
- **Standard websites**: 30-60 minutes  
- **Development/staging**: 2-6 hours
- **Archive/backup sites**: 12-24 hours

**Alert Fatigue Prevention:**
- **Use escalation delays** (wait 2-3 failed checks before alerting)
- **Group notifications** by time periods
- **Set different alert thresholds** for different site types
- **Monitor during business hours** for non-critical sites

### **🔐 Security Considerations**

**Server Hardening:**
```php
// Add to wp-config.php for enhanced security
define('MMWM_RESTRICT_ACCESS', true);        // Limit admin access
define('MMWM_LOG_SECURITY_EVENTS', true);    // Log security events
define('MMWM_REQUIRE_AUTH_HEADERS', true);   // Require authentication headers
```

**Network Security:**
- **Use dedicated monitoring subdomain**
- **Implement IP allowlisting** where possible
- **Monitor from trusted server locations**
- **Regular security audits** of monitoring rules

### **📈 Scaling for Growth**

**Multi-Server Setup:**
```bash
# For large enterprises - distribute monitoring load
# Server 1: Critical websites (every 5-15 min)
# Server 2: Standard websites (every 30-60 min)  
# Server 3: Archive/development sites (every 6-24 hours)
```

**Database Management:**
- **Archive old results** after 6-12 months
- **Use database indexing** for faster queries
- **Monitor disk space** usage regularly
- **Backup configuration settings** before major changes

### **🚀 Advanced Integration**

**Webhook Integration:**
```php
// Custom webhook for external systems
add_action('mmwm_website_status_changed', function($post_id, $old_status, $new_status) {
    $webhook_url = 'https://your-system.com/webhook';
    $data = [
        'website_id' => $post_id,
        'old_status' => $old_status,
        'new_status' => $new_status,
        'timestamp' => current_time('timestamp')
    ];
    
    wp_remote_post($webhook_url, [
        'body' => json_encode($data),
        'headers' => ['Content-Type' => 'application/json']
    ]);
}, 10, 3);
```

**Slack/Teams Integration:**
```php
// Send alerts to Slack/Teams
add_filter('mmwm_notification_channels', function($channels) {
    $channels['slack'] = [
        'webhook_url' => 'https://hooks.slack.com/your-webhook',
        'channel' => '#monitoring-alerts',
        'enabled' => true
    ];
    return $channels;
});
```

### **🌍 Global Monitoring Setup**

**Multi-Region Strategy:**
- **Primary monitoring server**: Main business location
- **Secondary monitors**: Different geographic regions
- **Failover logic**: Switch regions if primary fails
- **Latency consideration**: Choose servers close to target websites

**Time Zone Management:**
```php
// Configure monitoring for different time zones
define('MMWM_BUSINESS_HOURS_START', 8);   // 8 AM local time
define('MMWM_BUSINESS_HOURS_END', 18);    // 6 PM local time
define('MMWM_WEEKEND_MONITORING', false); // Skip weekends for some sites
```

### **📊 Reporting & Analytics**

**Custom Dashboards:**
- **Export monitoring data** to external analytics
- **Create SLA reports** for clients/management
- **Track uptime trends** over time
- **Monitor response time patterns**

**Performance Metrics:**
- **Average response time** per website
- **Uptime percentage** calculations
- **SSL certificate renewal tracking**
- **Domain expiration timeline**

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