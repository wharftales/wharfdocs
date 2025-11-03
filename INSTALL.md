# Installation Guide

Complete installation instructions for WharfDocs.

## Prerequisites

Before installing WharfDocs, ensure you have:

- **PHP 7.4 or higher** - Check with `php -v`
- **Web Server** - Apache, Nginx, or PHP's built-in server

## Step-by-Step Installation

### 1. Download WharfDocs

**Option A: Clone from Git**
```bash
git clone https://github.com/yourusername/wharfdocs.git
cd wharfdocs
```

**Option B: Download ZIP**
- Download and extract the ZIP file
- Navigate to the extracted directory

### 2. Configure Your Site

Edit `config.php` to customize your documentation:

```php
<?php

return [
    'site_name' => 'My Documentation',
    'site_description' => 'My awesome project documentation',
    'default_page' => 'getting-started/introduction',
    'github_repo' => 'https://github.com/yourusername/yourrepo',
];
```

### 3. Choose Your Server

#### Option A: PHP Built-in Server (Development)

Perfect for local development:

```bash
php -S localhost:8000
```

Then open: `http://localhost:8000`

#### Option B: Apache (Production)

1. **Create a Virtual Host**

Edit your Apache configuration (e.g., `/etc/apache2/sites-available/docs.conf`):

```apache
<VirtualHost *:80>
    ServerName docs.yourdomain.com
    DocumentRoot /path/to/wharfdocs
    
    <Directory /path/to/wharfdocs>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/docs-error.log
    CustomLog ${APACHE_LOG_DIR}/docs-access.log combined
</VirtualHost>
```

2. **Enable the site**

```bash
sudo a2ensite docs.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

3. **Update your hosts file** (for local testing)

```bash
# /etc/hosts
127.0.0.1 docs.yourdomain.com
```

#### Option C: Nginx (Production)

1. **Create a Server Block**

Edit your Nginx configuration (e.g., `/etc/nginx/sites-available/docs`):

```nginx
server {
    listen 80;
    server_name docs.yourdomain.com;
    root /path/to/wharfdocs;
    
    index index.php index.html;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\.ht {
        deny all;
    }
    
    access_log /var/log/nginx/docs-access.log;
    error_log /var/log/nginx/docs-error.log;
}
```

2. **Enable the site**

```bash
sudo ln -s /etc/nginx/sites-available/docs /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 4. Verify Installation

Open your browser and navigate to your documentation site. You should see the default documentation pages.

## Troubleshooting

### Issue: "Class 'Parsedown' not found"

**Solution:** Run `composer install` to install dependencies.

### Issue: 404 errors on all pages

**Solution:** 
- **Apache:** Ensure `mod_rewrite` is enabled and `.htaccess` is being read
- **Nginx:** Check that the `try_files` directive is correctly configured

### Issue: Permission denied errors

**Solution:** Set correct permissions:

```bash
# Make sure web server can read files
chmod -R 755 /path/to/wharfdocs

# If using Apache
sudo chown -R www-data:www-data /path/to/wharfdocs

# If using Nginx
sudo chown -R nginx:nginx /path/to/wharfdocs
```

### Issue: CSS/JS not loading

**Solution:** Check that the `assets/` directory is accessible and not blocked by `.htaccess` or server configuration.

## Next Steps

1. **Read the documentation** - Browse the included docs to learn all features
2. **Customize the config** - Edit `config.php` to match your needs
3. **Add your content** - Replace example docs with your own Markdown files
4. **Customize the theme** - Modify CSS and templates to match your brand

## Updating

To update WharfDocs:

```bash
# Pull latest changes
git pull origin main

# Update dependencies
composer update

# Clear any cache (if implemented)
rm -rf cache/*
```

## Production Checklist

Before deploying to production:

- [ ] Run `composer install --no-dev` to exclude development dependencies
- [ ] Configure proper domain and SSL certificate
- [ ] Set up proper file permissions
- [ ] Configure error logging
- [ ] Test all documentation pages
- [ ] Verify search functionality
- [ ] Check mobile responsiveness
- [ ] Set up backups
- [ ] Configure analytics (if needed)

## Getting Help

If you encounter issues:

1. Check the [documentation](docs/)
2. Search existing issues on GitHub
3. Open a new issue with details about your problem

---

Happy documenting! 📚
