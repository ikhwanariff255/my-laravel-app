# DefectGuru - Complete Setup Guide

This guide will help you get DefectGuru running smoothly on your system.

## Quick Start (5 minutes)

```bash
# 1. Extract the zip file
unzip defectguru.zip
cd defectguru

# 2. Install dependencies
composer install
npm install

# 3. Copy environment file
cp .env.example .env

# 4. Generate key
php artisan key:generate

# 5. Setup database (update .env first with your database details)
php artisan migrate

# 6. Create storage link
php artisan storage:link

# 7. Build assets
npm run build

# 8. Start server
php artisan serve
```

Access at: **http://localhost:8000**

---

## Detailed Installation Guide

### Prerequisites

Before starting, ensure you have:
- PHP 8.1+ installed
- Composer installed
- MySQL 8.0+ running
- Node.js & npm installed
- GD Library enabled for PHP

**Check PHP version:**
```bash
php -v
php -m | grep gd  # Should show 'gd' if GD is installed
```

### Step 1: Extract & Navigate

```bash
unzip defectguru-2.0.1.zip
cd defectguru
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

If you get permission errors:
```bash
chmod -R 755 .
composer install
```

### Step 3: Install Node Dependencies

```bash
npm install
```

### Step 4: Environment Configuration

**Copy the environment file:**
```bash
cp .env.example .env
```

**Edit .env file and configure:**
```env
APP_NAME=DefectGuru
APP_DEBUG=false          # Set to false in production
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=defectguru
DB_USERNAME=root
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

### Step 5: Generate Application Key

```bash
php artisan key:generate
```

### Step 6: Database Setup

**Create the database (if not exists):**
```bash
mysql -u root -p
# In MySQL prompt:
CREATE DATABASE defectguru CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

**Run migrations:**
```bash
php artisan migrate
```

**Optional - Seed sample data:**
```bash
php artisan db:seed
```

### Step 7: Create Storage Link

```bash
php artisan storage:link
```

This creates a symlink for uploaded files access.

### Step 8: Build Frontend Assets

**For production:**
```bash
npm run build
```

**For development with hot reload:**
```bash
npm run dev
```

### Step 9: Set File Permissions

```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

### Step 10: Start the Application

**Using Laravel's development server:**
```bash
php artisan serve
```

**Using a different port:**
```bash
php artisan serve --port=8001
```

**Using Valet (if installed):**
```bash
valet link defectguru
```

**Access the application:**
- URL: http://localhost:8000
- Default credentials: (Check database seeders or create via registration)

---

## Production Setup (Server Deployment)

### Apache Configuration

Create `/etc/apache2/sites-available/defectguru.conf`:

```apache
<VirtualHost *:80>
    ServerName defectguru.yourdomain.com
    ServerAlias www.defectguru.yourdomain.com
    DocumentRoot /var/www/defectguru/public

    <Directory /var/www/defectguru>
        AllowOverride All
        Require all granted
    </Directory>

    <Directory /var/www/defectguru/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/defectguru-error.log
    CustomLog ${APACHE_LOG_DIR}/defectguru-access.log combined
</VirtualHost>
```

Enable and restart:
```bash
sudo a2enmod rewrite
sudo a2ensite defectguru
sudo systemctl restart apache2
```

### Nginx Configuration

Create `/etc/nginx/sites-available/defectguru`:

```nginx
server {
    listen 80;
    server_name defectguru.yourdomain.com;
    root /var/www/defectguru/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Enable and test:
```bash
sudo ln -s /etc/nginx/sites-available/defectguru /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Environment Configuration for Production

Update `.env` for production:

```env
APP_DEBUG=false
APP_ENV=production
APP_URL=https://defectguru.yourdomain.com

DB_CONNECTION=mysql
DB_HOST=your-database-host
DB_DATABASE=defectguru_prod
DB_USERNAME=defectguru_user
DB_PASSWORD=strong-password-here

MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

### Optimization for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Build assets for production
npm run build
```

### SSL/HTTPS Setup

Using Let's Encrypt with Certbot:

```bash
sudo certbot certonly --webroot -w /var/www/defectguru/public -d defectguru.yourdomain.com
```

Update Nginx/Apache to use certificate and redirect HTTP to HTTPS.

---

## Troubleshooting

### Issue: "Class 'GD' not found"

**Solution:**
```bash
# Ubuntu/Debian
sudo apt-get install php-gd
sudo systemctl restart php-fpm

# CentOS/RHEL
sudo yum install php-gd
sudo systemctl restart php-fpm
```

### Issue: "No application encryption key has been specified"

**Solution:**
```bash
php artisan key:generate
```

### Issue: Storage folder permission denied

**Solution:**
```bash
sudo chown -R www-data:www-data /var/www/defectguru/storage
sudo chmod -R 775 storage/
```

### Issue: Database connection error

**Solution:**
1. Verify MySQL is running: `sudo systemctl status mysql`
2. Check .env database credentials
3. Test connection: `mysql -h 127.0.0.1 -u root -p`
4. Ensure database exists: `SHOW DATABASES;`

### Issue: npm install fails

**Solution:**
```bash
rm package-lock.json
npm cache clean --force
npm install
```

### Issue: Composer install fails

**Solution:**
```bash
rm composer.lock
composer update
```

### Issue: Assets not loading

**Solution:**
```bash
npm run build
php artisan view:cache --clear
```

---

## Maintenance

### Regular Tasks

**Weekly:**
- Check error logs: `tail -f storage/logs/laravel.log`
- Backup database

**Monthly:**
- Update dependencies: `composer update`
- Review access logs
- Clean up temporary files

**Quarterly:**
- Security audit
- Performance optimization
- Backup verification

### Useful Commands

```bash
# Clear all caches
php artisan cache:clear
php artisan route:cache --clear
php artisan view:cache --clear
php artisan config:cache --clear

# Check application status
php artisan tinker

# Database backup
mysqldump -u root -p defectguru > backup.sql

# Monitor logs
tail -f storage/logs/laravel.log

# Run queue jobs (if configured)
php artisan queue:work
```

---

## Support & Documentation

- **Application Logs**: `storage/logs/laravel.log`
- **Configuration**: `.env` file
- **Database**: Check migrations in `database/migrations/`
- **Views**: Located in `resources/views/`

---

## Security Checklist

- [ ] Change default admin credentials
- [ ] Set `APP_DEBUG=false` in production
- [ ] Enable HTTPS/SSL
- [ ] Setup firewall rules
- [ ] Backup sensitive data
- [ ] Regular security updates
- [ ] Monitor error logs
- [ ] Restrict file upload sizes

---

## Performance Optimization

1. **Enable Caching**
   ```bash
   php artisan config:cache
   php artisan route:cache
   ```

2. **Optimize Database**
   - Add indexes on frequently queried columns
   - Regular VACUUM (for SQLite)
   - Regular ANALYZE (for MySQL)

3. **Use CDN for Assets**
   - Configure in `.env`
   - Update asset URL in config

4. **Enable Compression**
   - Configure Nginx/Apache gzip
   - Compress CSS and JS files

---

**Installation complete! Happy inspecting!**

For additional help, refer to:
- README.md - Overview and features
- CHANGELOG.md - Version history
- Laravel Documentation: https://laravel.com/docs
