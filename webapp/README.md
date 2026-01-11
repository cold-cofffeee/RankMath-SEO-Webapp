# 🚀 RankMath SEO Webapp

A **free, standalone web application** with all the powerful SEO features from RankMath Pro plugin - completely license-free and ready for self-hosting!

## ✨ Features

### SEO Tools
- **SEO Analysis** - Comprehensive website SEO auditing
- **Competitor Analysis** - Analyze competitor websites
- **Content AI** - AI-powered content generation assistant
- **Keyword Research** - Research and track keywords

### Technical SEO
- **404 Monitor** - Track and manage 404 errors
- **Redirections Manager** - Create and manage URL redirects
- **Sitemap Generator** - Auto-generate XML sitemaps (General, News, Video)
- **Schema Markup** - Generate structured data

### Local & Image SEO
- **Local SEO** - Manage multiple business locations
- **Image SEO** - Optimize images with alt text and compression analysis

### Analytics
- **Analytics Dashboard** - Track keywords, impressions, clicks
- **Performance Metrics** - Monitor page performance
- **Search Console Integration** - Import GSC data

## 🖥️ Requirements

- **PHP** 7.4 or higher
- **MySQL** 5.7+ or **MariaDB** 10.2+
- **Apache** with mod_rewrite (or Nginx)
- **PHP Extensions**: PDO, cURL, JSON, mysqli

## 📦 Installation

### Option 1: XAMPP (Local Development)

1. **Copy webapp folder to XAMPP**
   ```
   Copy: C:\xampp\htdocs\rankmath\webapp
   ```

2. **Start XAMPP**
   - Start Apache
   - Start MySQL

3. **Run Installation**
   - Open browser: `http://localhost/rankmath/webapp/install.php`
   - Follow installation wizard
   - Configure database (default XAMPP: host=localhost, user=root, password=empty)

4. **Access Dashboard**
   - Open: `http://localhost/rankmath/webapp/`

### Option 2: Shared Hosting (Namecheap, etc.)

1. **Upload Files**
   - Upload entire `webapp` folder via FTP/cPanel File Manager
   - Place in: `public_html/rankmath-seo/` (or your preferred directory)

2. **Create MySQL Database**
   - Go to cPanel → MySQL Databases
   - Create new database: `your_database_name`
   - Create user and assign all privileges

3. **Run Installation**
   - Visit: `https://yourdomain.com/rankmath-seo/install.php`
   - Enter database credentials
   - Complete installation

4. **Set Permissions** (if needed)
   ```bash
   chmod 755 webapp/
   chmod 644 webapp/config/database.php
   ```

### Option 3: Linux Server (Ubuntu/Debian)

1. **Install Dependencies**
   ```bash
   sudo apt update
   sudo apt install apache2 mysql-server php php-mysql php-curl php-json php-mbstring
   sudo a2enmod rewrite
   sudo systemctl restart apache2
   ```

2. **Upload Application**
   ```bash
   cd /var/www/html
   sudo mkdir rankmath-seo
   # Upload webapp files to /var/www/html/rankmath-seo/
   sudo chown -R www-data:www-data rankmath-seo/
   ```

3. **Create Database**
   ```bash
   sudo mysql
   CREATE DATABASE rankmath_webapp;
   CREATE USER 'rankmath_user'@'localhost' IDENTIFIED BY 'your_password';
   GRANT ALL PRIVILEGES ON rankmath_webapp.* TO 'rankmath_user'@'localhost';
   FLUSH PRIVILEGES;
   EXIT;
   ```

4. **Configure Apache Virtual Host** (Optional)
   ```apache
   <VirtualHost *:80>
       ServerName seo.yourdomain.com
       DocumentRoot /var/www/html/rankmath-seo
       
       <Directory /var/www/html/rankmath-seo>
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

5. **Run Installation**
   - Visit: `http://your-server-ip/rankmath-seo/install.php`

## 📁 Directory Structure

```
webapp/
├── assets/
│   ├── css/
│   │   └── style.css          # Frontend styles
│   └── js/
│       └── app.js             # Frontend JavaScript
├── config/
│   ├── app.php                # Application config
│   └── database.php           # Database config (auto-generated)
├── core/
│   ├── Database.php           # Database handler
│   ├── Response.php           # API response helper
│   └── Router.php             # API router
├── database/
│   └── schema.sql             # Database schema
├── modules/
│   ├── SeoAnalysis/
│   │   └── SeoAnalysisController.php
│   ├── Analytics/
│   │   └── AnalyticsController.php
│   ├── ContentAi/
│   │   └── ContentAiController.php
│   ├── LocalSeo/
│   │   └── LocalSeoController.php
│   ├── ImageSeo/
│   │   └── ImageSeoController.php
│   ├── Monitor/
│   │   └── MonitorController.php
│   ├── Redirections/
│   │   └── RedirectionsController.php
│   └── Sitemap/
│       └── SitemapController.php
├── .htaccess                  # Apache rewrite rules
├── api.php                    # API entry point
├── index.php                  # Dashboard UI
├── install.php                # Installation wizard
└── install-handler.php        # Installation processor
```

## 🔧 Configuration

### Database Configuration
Edit `config/database.php` after installation:

```php
return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'rankmath_webapp',
    'username' => 'root',
    'password' => '',
    'prefix' => 'rm_',
];
```

### Application Configuration
Edit `config/app.php`:

```php
return [
    'base_url' => 'http://localhost/rankmath/webapp',
    'debug' => true, // Set to false in production
];
```

## 🎯 Usage Guide

### SEO Analysis
1. Go to **SEO Analysis** tab
2. Enter target URL
3. Click "Analyze Website"
4. Review comprehensive SEO report with score

### Competitor Analysis
1. Navigate to **Competitor Analysis**
2. Enter competitor URL
3. Get detailed analysis of their SEO setup

### Content AI
1. Open **Content AI** tab
2. Enter target keyword
3. Select content type (Title, Paragraph, Meta Description, etc.)
4. Click "Generate Content"
5. Copy generated content

### 404 Monitor
- Automatically tracks 404 errors
- View all 404 logs with hit counts
- Export logs as CSV
- Clear individual or all logs

### Redirections
- Add 301/302/307/308 redirects
- Import redirects from CSV
- Track redirect hits
- Delete outdated redirects

### Local SEO
- Add multiple business locations
- Auto-generate schema markup
- Search nearby locations
- Geocoding support

### Image SEO
- Bulk analyze images from any URL
- Get optimization suggestions
- AI-powered alt text suggestions
- Track optimization status

### Sitemaps
- Auto-crawl website to generate sitemap
- Support for general, news, video sitemaps
- Download XML sitemap files
- Submit to search engines

## 🔌 API Documentation

### Base URL
```
/rankmath/webapp/api.php
```

### Endpoints

#### SEO Analysis
- `POST /api/seo-analysis/analyze` - Analyze URL
- `GET /api/seo-analysis/history` - Get analysis history

#### Analytics
- `GET /api/analytics/dashboard` - Get dashboard data
- `POST /api/analytics/keyword` - Add keyword data

#### 404 Monitor
- `GET /api/404-monitor/logs` - Get all logs
- `POST /api/404-monitor/log` - Log 404 error
- `DELETE /api/404-monitor/{id}` - Delete log

#### Redirections
- `GET /api/redirections` - Get all redirections
- `POST /api/redirections` - Add redirection
- `DELETE /api/redirections/{id}` - Delete redirection

[... more endpoints documented in api.php]

## 🚀 Deployment Checklist

### Before Going Live

- [ ] Change `debug` to `false` in `config/app.php`
- [ ] Update `base_url` in `config/app.php`
- [ ] Set strong MySQL password
- [ ] Enable HTTPS (SSL certificate)
- [ ] Set up regular database backups
- [ ] Configure cron jobs for analytics (optional)
- [ ] Restrict database access to localhost only
- [ ] Remove or protect `install.php` after installation

### Security Best Practices

1. **Database Security**
   ```sql
   -- Don't use root user in production
   CREATE USER 'rm_app'@'localhost' IDENTIFIED BY 'strong_password_here';
   GRANT SELECT, INSERT, UPDATE, DELETE ON rankmath_webapp.* TO 'rm_app'@'localhost';
   ```

2. **File Permissions**
   ```bash
   chmod 755 webapp/
   chmod 644 config/database.php
   ```

3. **Disable Directory Listing**
   Add to `.htaccess`:
   ```apache
   Options -Indexes
   ```

## 🛠️ Troubleshooting

### "Database connection failed"
- Check database credentials in `config/database.php`
- Ensure MySQL service is running
- Verify database exists

### "404 Page not found"
- Enable Apache mod_rewrite: `sudo a2enmod rewrite`
- Check `.htaccess` file exists
- Verify `AllowOverride All` in Apache config

### "API requests failing"
- Check `base_url` in `assets/js/app.js`
- Verify API routes in `api.php`
- Check PHP error logs

### Blank page / White screen
- Enable error display:
  ```php
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  ```
- Check PHP error log
- Verify all PHP files are uploaded

## 📊 Performance Optimization

1. **Enable OPcache** (php.ini):
   ```ini
   opcache.enable=1
   opcache.memory_consumption=128
   ```

2. **MySQL Optimization**:
   ```sql
   -- Add indexes for better query performance
   ALTER TABLE rm_analytics_keywords ADD INDEX idx_date (date);
   ALTER TABLE rm_analytics_keywords ADD INDEX idx_keyword (keyword(100));
   ```

3. **Caching**:
   - Consider adding Redis/Memcached for session storage
   - Implement result caching for expensive queries

## 🤝 Contributing

This is a custom build adapted from RankMath Pro. Feel free to customize and extend based on your needs!

## 📝 License

This is a **free, license-free** version. No premium subscriptions, no license keys required.

**Original Plugin**: RankMath Pro (Enterprise License)
**Adaptation**: Standalone web application

## 🆘 Support

For issues and questions:
1. Check the troubleshooting section
2. Review PHP error logs
3. Check Apache/Nginx error logs

## 🎉 Credits

Built upon features from **RankMath SEO Pro** plugin, adapted into a standalone web application.

---

**Enjoy your free SEO toolkit! 🚀**
