# Quick Fix: Apache mod_rewrite Error

## Your Error
```
Invalid command 'RewriteEngine', perhaps misspelled or defined by a module not included in the server configuration
```

## Cause
Apache's `mod_rewrite` module is not enabled.

## Quick Fix (Run on your server)

```bash
# Enable mod_rewrite
sudo a2enmod rewrite

# Restart Apache
sudo systemctl restart apache2

# Verify it's enabled
apache2ctl -M | grep rewrite
```

You should see: `rewrite_module (shared)`

---

## Alternative: Work Without mod_rewrite

If you can't enable mod_rewrite, the app now supports query parameters!

### Access pages using:
- Homepage: `https://yourdomain.com/index.php`
- Pages: `https://yourdomain.com/index.php?path=getting-started/introduction`
- Search: `https://yourdomain.com/index.php?path=api/search&q=test`

### Update your links
If using query parameter mode, you'll need to update the template links. But it's better to enable mod_rewrite for clean URLs.

---

## Check Apache Configuration

Make sure your VirtualHost or Directory configuration allows `.htaccess`:

```apache
<Directory /var/www/html>
    AllowOverride All  # Must be "All" not "None"
    Require all granted
</Directory>
```

---

## Docker Users

If using Docker, make sure your Dockerfile enables mod_rewrite:

```dockerfile
FROM php:7.4-apache
RUN a2enmod rewrite
COPY . /var/www/html/
```

---

## Still Not Working?

1. Check Apache error logs:
   ```bash
   tail -f /var/log/apache2/error.log
   ```

2. Verify .htaccess is readable:
   ```bash
   ls -la /var/www/html/.htaccess
   ```

3. Test Apache configuration:
   ```bash
   apache2ctl configtest
   ```

4. Contact your hosting provider if on shared hosting

---

## Summary

**Best solution:** Enable mod_rewrite (1 command)
**Fallback:** Use query parameters (already supported in latest code)
