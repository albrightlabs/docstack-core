# Deployment

This guide covers deploying DocStack to a production server.

## Requirements

- PHP 8.1 or later
- Composer
- Web server (Apache or Nginx)
- SSL certificate (recommended)

## Deployment Options

### Option 1: Traditional VPS/Server

#### 1. Server Preparation

Connect to your server and install the required software:

```bash
# Ubuntu/Debian
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mbstring php8.2-xml composer nginx

# CentOS/RHEL
sudo dnf install php php-fpm php-mbstring php-xml composer nginx
```

#### 2. Clone and Install

```bash
cd /var/www
git clone https://github.com/albrightlabs/docstack-core.git docs
cd docs
composer install --no-dev --optimize-autoloader
```

#### 3. Configure Environment

```bash
cp .env.example .env
nano .env
```

Set your production values:

```env
SITE_NAME="My Documentation"
FEATURE_EDITING=true
```

#### 4. Set Permissions

```bash
# Set ownership
sudo chown -R www-data:www-data /var/www/docs

# Set directory permissions
sudo find /var/www/docs -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/docs -type f -exec chmod 644 {} \;

# Make content and data directories writable
sudo chmod -R 775 /var/www/docs/content
sudo chmod -R 775 /var/www/docs/data
sudo chmod -R 775 /var/www/docs/public/uploads
```

#### 5. Configure Nginx

Create `/etc/nginx/sites-available/docs`:

```nginx
server {
    listen 80;
    server_name docs.example.com;
    root /var/www/docs/public;
    index index.php;

    # Redirect to HTTPS (uncomment after SSL setup)
    # return 301 https://$server_name$request_uri;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(env|git|htaccess) {
        deny all;
    }
}
```

Enable the site:

```bash
sudo ln -s /etc/nginx/sites-available/docs /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

#### 6. Configure Apache (Alternative)

If using Apache instead of Nginx, ensure `mod_rewrite` is enabled:

```bash
sudo a2enmod rewrite
```

Create `/etc/apache2/sites-available/docs.conf`:

```apache
<VirtualHost *:80>
    ServerName docs.example.com
    DocumentRoot /var/www/docs/public

    <Directory /var/www/docs/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/docs_error.log
    CustomLog ${APACHE_LOG_DIR}/docs_access.log combined
</VirtualHost>
```

Enable the site:

```bash
sudo a2ensite docs.conf
sudo systemctl reload apache2
```

#### 7. SSL with Let's Encrypt

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d docs.example.com
```

### Option 2: Shared Hosting

Most shared hosting providers support PHP applications.

#### 1. Upload Files

Upload all files except `.git` to your hosting via FTP/SFTP or the hosting control panel.

#### 2. Set Document Root

Configure your domain's document root to point to the `public/` directory.

#### 3. Configure .htaccess

The included `.htaccess` file should work automatically. Ensure your hosting has `mod_rewrite` enabled.

#### 4. Create .env

Create the `.env` file in the root directory (one level above `public/`) with your configuration.

### Option 3: Platform as a Service

#### Deploy to Railway

```bash
# Install Railway CLI
npm install -g @railway/cli

# Login and deploy
railway login
railway init
railway up
```

#### Deploy to Render

Create a `render.yaml`:

```yaml
services:
  - type: web
    name: docstack
    env: php
    buildCommand: composer install --no-dev
    startCommand: php -S 0.0.0.0:$PORT -t public public/router.php
```

## Post-Deployment

### Initial Setup

1. Visit your documentation URL
2. You'll be redirected to `/setup` to create the first admin user
3. Log in and start adding content

### Security Checklist

- [ ] SSL/HTTPS enabled
- [ ] `.env` file not accessible from web
- [ ] Strong admin password set
- [ ] File permissions restricted
- [ ] Regular backups configured

### Updating

To update DocStack:

```bash
cd /var/www/docs
git pull origin main
composer install --no-dev --optimize-autoloader
```

Your content in `content/` and configuration in `.env` are preserved.

## Troubleshooting

### Blank Page / 500 Error

Check PHP error logs:

```bash
# Nginx
sudo tail -f /var/log/nginx/error.log

# Apache
sudo tail -f /var/log/apache2/error.log

# PHP-FPM
sudo tail -f /var/log/php8.2-fpm.log
```

### Permission Denied

Ensure the web server user owns the files:

```bash
sudo chown -R www-data:www-data /var/www/docs
```

### Composer Not Found

Install Composer globally:

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Rewrite Rules Not Working

For Apache, ensure `mod_rewrite` is enabled and `AllowOverride All` is set.

For Nginx, ensure the `try_files` directive is properly configured.
