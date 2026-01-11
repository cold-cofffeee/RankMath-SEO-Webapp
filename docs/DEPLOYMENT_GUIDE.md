# Deployment Guide - GitHub to Production

## 📋 Pre-Deployment Checklist

Before pushing to GitHub and deploying:

### Files to Keep OUT of Git

Create/update `.gitignore`:

```gitignore
# API Keys & Secrets (NEVER COMMIT!)
config/api-keys.php
webapp/config/database.php

# Environment files
.env
.env.local
.env.production

# User uploaded content (if any)
uploads/
cache/

# IDE & OS
.vscode/
.idea/
*.sublime-*
.DS_Store
Thumbs.db

# Logs
*.log
error_log
debug.log

# Dependencies (if using Composer)
vendor/

# Temporary files
*.tmp
*.temp
```

### Files to Include in Git

✅ All PHP files  
✅ JavaScript/CSS files  
✅ SQL schema files  
✅ Documentation files  
✅ `.htaccess` files  
✅ `config/api-keys.php.example` (template without real keys)

---

## 🔧 Preparing for GitHub

### Step 1: Create Template Config Files

Create `config/api-keys.php.example`:

```php
<?php
/**
 * API Keys Configuration Template
 * Copy this file to api-keys.php and add your real keys
 */

return [
    // Google Gemini API - Required for Content AI
    'gemini' => [
        'api_key' => '', // Get from: https://makersuite.google.com/app/apikey
        'model' => 'gemini-2.0-flash-exp',
        'endpoint' => 'https://generativelanguage.googleapis.com/v1beta/models',
    ],
    
    // Google Search Console - Optional for enhanced keyword research
    'google_search_console' => [
        'client_id' => '', // Get from: https://console.cloud.google.com/
        'client_secret' => '',
        'redirect_uri' => 'https://yourdomain.com/api.php/api/google/oauth-callback',
    ],
    
    // SEMrush - Optional for advanced metrics
    'semrush' => [
        'api_key' => '', // Requires paid SEMrush plan
    ],
];
```

Create `webapp/config/database.php.example`:

```php
<?php
/**
 * Database Configuration Template
 * Copy this file to database.php and configure for your environment
 */

return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'rankmath_webapp',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'prefix' => 'rm_',
    
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

### Step 2: Remove Sensitive Data

```bash
# Remove your actual API keys file (keep .example)
git rm --cached config/api-keys.php

# Remove database config
git rm --cached webapp/config/database.php

# Verify .gitignore is working
git status
```

### Step 3: Create Installation Documentation

Update `README.md` with:
- Clear installation steps
- API setup instructions (link to `docs/API_CONFIGURATION.md`)
- Quick start guide
- Troubleshooting section

---

## 🚀 Pushing to GitHub

### Initialize Repository

```bash
cd c:\xampp\htdocs\rankmath

# Initialize Git (if not already)
git init

# Add all files (respecting .gitignore)
git add .

# Commit
git commit -m "Initial commit: RankMath SEO Webapp"

# Add remote repository
git remote add origin https://github.com/yourusername/rankmath-seo-webapp.git

# Push to GitHub
git push -u origin main
```

### Create Repository on GitHub

1. Go to: https://github.com/new
2. Repository name: `rankmath-seo-webapp`
3. Description: "Complete SEO analysis and management platform with AI-powered content generation"
4. Choose: **Public** or **Private**
5. Don't initialize with README (you already have one)
6. Click "Create repository"

---

## 🌐 Deploying to Production

### Option 1: Shared Hosting (cPanel/Plesk)

#### Step 1: Prepare Server

1. **Create Database**
   - cPanel → MySQL Databases
   - Create database: `username_rankmath`
   - Create user with strong password
   - Grant all privileges

2. **Get Domain Ready**
   - Point domain to hosting
   - Wait for DNS propagation

#### Step 2: Upload Files

**Via Git (Recommended):**
```bash
# SSH into server
ssh user@yourserver.com

# Navigate to web root
cd public_html

# Clone repository
git clone https://github.com/yourusername/rankmath-seo-webapp.git rankmath

cd rankmath
```

**Via FTP/SFTP:**
- Upload all files to `public_html/rankmath/`
- Ensure all folders and files transferred

#### Step 3: Configure

```bash
# Copy example configs
cp config/api-keys.php.example config/api-keys.php
cp webapp/config/database.php.example webapp/config/database.php

# Edit with actual values
nano config/api-keys.php
# Add production Gemini API key
# Update redirect URIs to production domain

nano webapp/config/database.php
# Add production database credentials
```

#### Step 4: Set Permissions

```bash
# Set directory permissions
find . -type d -exec chmod 755 {} \;

# Set file permissions
find . -type f -exec chmod 644 {} \;

# Secure sensitive files
chmod 600 config/api-keys.php
chmod 600 webapp/config/database.php
```

#### Step 5: Update .htaccess

```bash
nano webapp/.htaccess

# Update RewriteBase
RewriteBase /rankmath/        # If in subdirectory
# OR
RewriteBase /                 # If in root
```

#### Step 6: Run Installation

Visit: `https://yourdomain.com/rankmath/install.php`

Complete installation wizard.

---

### Option 2: VPS/Cloud Server (DigitalOcean, AWS, etc.)

#### Step 1: Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install LAMP stack
sudo apt install apache2 mysql-server php php-mysql php-curl php-dom php-mbstring -y

# Enable Apache modules
sudo a2enmod rewrite
sudo systemctl restart apache2

# Secure MySQL
sudo mysql_secure_installation
```

#### Step 2: Configure Virtual Host

```bash
sudo nano /etc/apache2/sites-available/rankmath.conf
```

Add:
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    ServerAlias www.yourdomain.com
    DocumentRoot /var/www/html/rankmath/webapp
    
    <Directory /var/www/html/rankmath/webapp>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/rankmath-error.log
    CustomLog ${APACHE_LOG_DIR}/rankmath-access.log combined
</VirtualHost>
```

Enable site:
```bash
sudo a2ensite rankmath.conf
sudo systemctl reload apache2
```

#### Step 3: Setup SSL (Let's Encrypt)

```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache -y

# Get SSL certificate
sudo certbot --apache -d yourdomain.com -d www.yourdomain.com

# Auto-renewal (already set up by certbot)
# Verify: sudo certbot renew --dry-run
```

#### Step 4: Deploy Application

```bash
cd /var/www/html

# Clone from GitHub
sudo git clone https://github.com/yourusername/rankmath-seo-webapp.git rankmath

cd rankmath

# Copy and configure
sudo cp config/api-keys.php.example config/api-keys.php
sudo cp webapp/config/database.php.example webapp/config/database.php

sudo nano config/api-keys.php
# Add production keys

sudo nano webapp/config/database.php
# Add database credentials

# Set ownership
sudo chown -R www-data:www-data /var/www/html/rankmath

# Set permissions
sudo find . -type d -exec chmod 755 {} \;
sudo find . -type f -exec chmod 644 {} \;
sudo chmod 600 config/api-keys.php
sudo chmod 600 webapp/config/database.php
```

#### Step 5: Create Database

```bash
sudo mysql -u root -p

CREATE DATABASE rankmath_webapp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rankmath_user'@'localhost' IDENTIFIED BY 'strong_password_here';
GRANT ALL PRIVILEGES ON rankmath_webapp.* TO 'rankmath_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

#### Step 6: Run Installation

Visit: `https://yourdomain.com/install.php`

---

### Option 3: Docker Deployment

Create `Dockerfile`:

```dockerfile
FROM php:8.1-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-install pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
```

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  webapp:
    build: .
    ports:
      - "8080:80"
    environment:
      - DB_HOST=db
      - DB_NAME=rankmath_webapp
      - DB_USER=rankmath
      - DB_PASSWORD=secure_password
    volumes:
      - ./config:/var/www/html/config
    depends_on:
      - db

  db:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=root_password
      - MYSQL_DATABASE=rankmath_webapp
      - MYSQL_USER=rankmath
      - MYSQL_PASSWORD=secure_password
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

Deploy:
```bash
docker-compose up -d
```

---

## 🔐 Post-Deployment Security

### 1. Update Google OAuth

- Go to: https://console.cloud.google.com/apis/credentials
- Edit OAuth client
- Add production domain to Authorized redirect URIs:
  - `https://yourdomain.com/api.php/api/google/oauth-callback`

### 2. Restrict API Keys

**Gemini API:**
- Visit: https://makersuite.google.com/app/apikey
- Click on your key → "Restrict Key"
- Add domain restrictions: `yourdomain.com`

### 3. Configure Firewall

```bash
# UFW (Ubuntu)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp  # SSH
sudo ufw enable
```

### 4. Set up Monitoring

**Error Monitoring:**
```bash
# Check Apache logs
tail -f /var/log/apache2/rankmath-error.log

# PHP errors
tail -f /var/log/php-errors.log
```

**Uptime Monitoring:**
- Use services like UptimeRobot, Pingdom, or StatusCake
- Monitor: `https://yourdomain.com/api.php/api/health`

### 5. Backup Strategy

```bash
# Database backup script
cat > /home/user/backup-rankmath.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u rankmath_user -p'password' rankmath_webapp > /backups/rankmath_db_$DATE.sql
find /backups -name "rankmath_db_*.sql" -mtime +7 -delete
EOF

chmod +x /home/user/backup-rankmath.sh

# Add to cron (daily at 2 AM)
crontab -e
0 2 * * * /home/user/backup-rankmath.sh
```

---

## 🔄 Updates & Maintenance

### Pulling Updates from GitHub

```bash
cd /var/www/html/rankmath

# Backup first
sudo cp -r . ../rankmath_backup_$(date +%Y%m%d)

# Pull updates
sudo git pull origin main

# Check for new dependencies or migrations
# Re-run installation if database schema changed

# Restart Apache
sudo systemctl restart apache2
```

### Regular Maintenance

**Weekly:**
```bash
# Clean old logs
find /var/log/apache2 -name "*.log" -mtime +30 -delete

# Optimize database
mysql -u rankmath_user -p rankmath_webapp -e "OPTIMIZE TABLE rm_analytics_keywords, rm_analytics_pages, rm_seo_analysis;"
```

**Monthly:**
```bash
# Review security
sudo apt update && sudo apt upgrade -y

# Check SSL renewal
sudo certbot renew

# Review API usage and costs
```

---

## 📊 Performance Optimization

### Enable PHP OPcache

```ini
# /etc/php/8.1/apache2/php.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
```

### Enable Gzip Compression

Already in `.htaccess`, verify working:
```bash
curl -H "Accept-Encoding: gzip" -I https://yourdomain.com
```

### Database Optimization

```sql
-- Add indexes if missing
ALTER TABLE rm_analytics_keywords ADD INDEX idx_date (date);
ALTER TABLE rm_analytics_keywords ADD INDEX idx_keyword (keyword(191));
ALTER TABLE rm_seo_analysis ADD INDEX idx_analyzed_at (analyzed_at);
```

---

## ✅ Final Deployment Checklist

- [ ] `.gitignore` configured correctly
- [ ] `api-keys.php.example` created (no real keys)
- [ ] `database.php.example` created
- [ ] All documentation complete
- [ ] README.md updated with quick start
- [ ] Pushed to GitHub
- [ ] Production server configured
- [ ] Database created and configured
- [ ] Files uploaded and permissions set
- [ ] API keys configured with production values
- [ ] `.htaccess` RewriteBase updated
- [ ] Installation completed successfully
- [ ] SSL certificate installed
- [ ] Google OAuth updated with production domain
- [ ] All features tested on production
- [ ] Monitoring set up
- [ ] Backup strategy implemented
- [ ] Documentation accessible to team

---

## 🆘 Support

### Issues During Deployment

1. Check error logs first
2. Verify all configuration files
3. Test database connection
4. Check file permissions
5. Review Apache/Nginx configuration

### Common Deployment Issues

See `README.md` Troubleshooting section.

---

**Last Updated:** January 11, 2026  
**Version:** 1.0.0

Ready to deploy! 🚀
