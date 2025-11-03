# Security Guide

## Overview

While WharfDocs is a documentation system, it's a **dynamic PHP application** that requires security considerations. This guide outlines the security measures in place and recommendations for production deployment.

## Built-in Security Features

### ✅ Implemented

1. **Output Escaping**
   - All user-facing output uses `htmlspecialchars()` to prevent XSS attacks
   - Template variables are properly escaped

2. **Input Validation**
   - Search queries are validated and length-limited (max 200 characters)
   - Path traversal protection through controlled file access

3. **Security Headers**
   - `X-Content-Type-Options: nosniff`
   - `X-Frame-Options: SAMEORIGIN`
   - `X-XSS-Protection: 1; mode=block`
   - `Referrer-Policy: strict-origin-when-cross-origin`

4. **Safe File Handling**
   - Markdown files are parsed, not executed
   - No direct file path access from user input
   - Uses directory scanning with numeric prefix matching

5. **Cache Security**
   - Cache files stored outside web root (recommended)
   - Cache validation prevents stale data attacks

## Production Deployment Checklist

### 🔒 Essential Security Steps

#### 1. Web Server Configuration

**Apache (.htaccess)**
```apache
# Prevent directory browsing
Options -Indexes

# Protect sensitive files
<FilesMatch "^(config\.php|composer\.(json|lock)|\.git.*|\.env)$">
    Require all denied
</FilesMatch>

# Protect cache directory
<DirectoryMatch "cache">
    Require all denied
</DirectoryMatch>
```

**Nginx**
```nginx
# Deny access to sensitive files
location ~ ^/(config\.php|composer\.(json|lock)|\.git) {
    deny all;
    return 404;
}

# Deny access to cache directory
location /cache {
    deny all;
    return 404;
}

# Deny access to src directory
location /src {
    deny all;
    return 404;
}
```

#### 2. File Permissions

```bash
# Set proper permissions
chmod 755 /path/to/wharfdocs
chmod 644 /path/to/wharfdocs/index.php
chmod 644 /path/to/wharfdocs/config.php
chmod 755 /path/to/wharfdocs/cache
chmod 644 /path/to/wharfdocs/cache/*

# Make cache writable by web server
chown -R www-data:www-data /path/to/wharfdocs/cache
```

#### 3. PHP Configuration

Add to `php.ini` or `.user.ini`:

```ini
# Disable dangerous functions
disable_functions = exec,passthru,shell_exec,system,proc_open,popen

# Hide PHP version
expose_php = Off

# Disable error display in production
display_errors = Off
log_errors = On
error_log = /path/to/error.log

# Session security
session.cookie_httponly = 1
session.cookie_secure = 1
session.use_strict_mode = 1
```

#### 4. HTTPS Configuration

**Always use HTTPS in production:**

```apache
# Apache - Force HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

```nginx
# Nginx - Force HTTPS
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}
```

#### 5. Content Security Policy

Add to your web server config or PHP headers:

```php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; img-src 'self' data:;");
```

### 🛡️ Additional Recommendations

#### Rate Limiting

Implement rate limiting for API endpoints:

**Apache (mod_ratelimit)**
```apache
<Location "/api/search">
    SetOutputFilter RATE_LIMIT
    SetEnv rate-limit 400
</Location>
```

**Nginx**
```nginx
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;

location /api/ {
    limit_req zone=api burst=20;
}
```

#### Monitoring & Logging

1. **Enable Error Logging**
   ```php
   // In config.php or index.php
   ini_set('log_errors', 1);
   ini_set('error_log', '/path/to/wharfdocs-errors.log');
   ```

2. **Monitor Access Logs**
   - Watch for unusual patterns
   - Monitor API endpoint usage
   - Track 404 errors (potential scanning)

3. **Set Up Alerts**
   - High error rates
   - Unusual traffic patterns
   - Failed file access attempts

#### Regular Updates

1. **Keep Dependencies Updated**
   ```bash
   composer update
   ```

2. **Monitor Security Advisories**
   - Check Parsedown security updates
   - Monitor PHP security bulletins

3. **Review Access Logs**
   - Weekly review of access patterns
   - Check for suspicious activity

## Security Best Practices

### ✅ Do

- Use HTTPS in production
- Keep PHP and dependencies updated
- Set proper file permissions
- Enable error logging (but disable display)
- Use strong passwords for server access
- Regular backups
- Monitor logs for suspicious activity

### ❌ Don't

- Don't expose sensitive files (config.php, .git, etc.)
- Don't run with elevated privileges
- Don't disable security headers
- Don't ignore error logs
- Don't use default credentials
- Don't skip HTTPS

## Vulnerability Reporting

If you discover a security vulnerability, please email: [your-security-email@domain.com]

**Please do not:**
- Open public issues for security vulnerabilities
- Exploit the vulnerability

## Security Checklist

Before going to production:

- [ ] HTTPS enabled and enforced
- [ ] Security headers configured
- [ ] Sensitive files protected (.htaccess rules)
- [ ] File permissions set correctly
- [ ] PHP error display disabled
- [ ] Error logging enabled
- [ ] Cache directory protected
- [ ] Rate limiting implemented
- [ ] Monitoring and alerts configured
- [ ] Regular backup schedule established
- [ ] Dependencies updated
- [ ] Security headers tested
- [ ] Content Security Policy configured

## Testing Security

### Test Protected Files

```bash
# These should return 403 or 404
curl -I https://yourdomain.com/config.php
curl -I https://yourdomain.com/.git/config
curl -I https://yourdomain.com/composer.json
curl -I https://yourdomain.com/cache/
```

### Test Security Headers

```bash
curl -I https://yourdomain.com/
```

Should include:
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: SAMEORIGIN`
- `X-XSS-Protection: 1; mode=block`

### Test Search Input Validation

```bash
# Should reject queries over 200 characters
curl "https://yourdomain.com/api/search?q=$(python3 -c 'print("a"*201)')"
```

## Additional Resources

- [OWASP PHP Security Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/PHP_Configuration_Cheat_Sheet.html)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [Apache Security Tips](https://httpd.apache.org/docs/2.4/misc/security_tips.html)
- [Nginx Security Controls](https://docs.nginx.com/nginx/admin-guide/security-controls/)
