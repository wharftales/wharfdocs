# Deployment Guide

## Apache with mod_rewrite (Recommended)

### Enable mod_rewrite

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### VirtualHost Configuration

```apache
<VirtualHost *:80>
    ServerName docs.yourdomain.com
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

The `.htaccess` file will handle URL rewriting automatically.

---

## Apache WITHOUT mod_rewrite (Alternative)

If you cannot enable mod_rewrite, you have two options:

### Option 1: Use index.php in URLs

Access pages like:
- `https://yourdomain.com/index.php`
- `https://yourdomain.com/index.php?path=getting-started/introduction`

**Update index.php to support query parameters:**

Replace line 27 in `index.php`:
```php
// OLD
$path = parse_url($requestUri, PHP_URL_PATH);

// NEW
$path = $_GET['path'] ?? parse_url($requestUri, PHP_URL_PATH);
```

### Option 2: Configure Apache VirtualHost with Rewrite Rules

Add rewrite rules directly to your VirtualHost configuration:

```apache
<VirtualHost *:80>
    ServerName docs.yourdomain.com
    DocumentRoot /var/www/html
    
    <Directory /var/www/html>
        AllowOverride None
        Require all granted
        
        # Rewrite rules (instead of .htaccess)
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
</VirtualHost>
```

Then restart Apache:
```bash
sudo systemctl restart apache2
```

---

## Nginx Configuration

For Nginx servers, create this configuration:

```nginx
server {
    listen 80;
    server_name docs.yourdomain.com;
    root /var/www/html;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }
}
```

---

## Docker Deployment

### Dockerfile with mod_rewrite enabled

```dockerfile
FROM php:7.4-apache

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 80
```

### docker-compose.yml

```yaml
version: '3.8'
services:
  docs:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./docs:/var/www/html/docs
```

---

## Shared Hosting

Most shared hosting providers have mod_rewrite enabled by default. If not:

1. Contact your hosting provider to enable it
2. Or use the query parameter method (Option 1 above)
3. Check cPanel → Apache Configuration → mod_rewrite

---

## Troubleshooting

### Error: "Invalid command 'RewriteEngine'"

**Cause:** mod_rewrite is not enabled

**Fix:**
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Error: "404 Not Found" on all pages

**Cause:** .htaccess not being read

**Fix:** Check Apache configuration allows `.htaccess`:
```apache
<Directory /var/www/html>
    AllowOverride All  # Must be "All" not "None"
</Directory>
```

### Error: Pages work but no CSS/styling

**Cause:** Base path issue

**Fix:** Check that assets are accessible:
```bash
ls -la /var/www/html/assets/
```

---

## Testing Your Deployment

1. **Test homepage:**
   ```
   curl https://yourdomain.com/
   ```

2. **Test clean URLs:**
   ```
   curl https://yourdomain.com/getting-started/introduction
   ```

3. **Test search API:**
   ```
   curl https://yourdomain.com/api/search?q=test
   ```

4. **Test navigation API:**
   ```
   curl https://yourdomain.com/api/navigation
   ```

All should return valid responses without 404 errors.

---

## Quick Fix for Your Current Error

Based on your error, run these commands on your server:

```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2

# Verify it's enabled
apache2ctl -M | grep rewrite
# Should output: rewrite_module (shared)
```

If you don't have sudo access, contact your hosting provider or use the query parameter method described above.
