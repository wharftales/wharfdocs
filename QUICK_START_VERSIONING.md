# Quick Start: Multi-Version Documentation

## 5-Minute Setup

### 1. Enable Versioning (config.php)
```php
'versions' => [
    'enabled' => true,
    'default' => 'v1.0',
    'show_selector' => true,
]
```

### 2. Create Version Directory
```bash
mkdir docs/v1.0
```

### 3. Move Your Docs
```bash
mv docs/1.getting-started docs/v1.0/
mv docs/2.core-concepts docs/v1.0/
# Move all your doc folders
```

### 4. Add Version Metadata
```bash
cat > docs/v1.0/version.json << 'EOF'
{
    "number": "1.0",
    "label": "v1.0",
    "status": "stable"
}
EOF
```

### 5. Clear Cache
```bash
php clear-cache.php
```

### 6. Done! 🎉
Visit your site - you'll see the version selector in the header!

---

## Add Another Version

```bash
# Copy existing version
cp -r docs/v1.0 docs/v2.0

# Update metadata
cat > docs/v2.0/version.json << 'EOF'
{
    "number": "2.0",
    "label": "v2.0",
    "status": "stable"
}
EOF

# Clear cache
php clear-cache.php
```

---

## Disable Versioning

```php
// config.php
'versions' => ['enabled' => false]
```

---

## URLs

**With versioning:**
- `/v1.0/getting-started/introduction`
- `/v2.0/getting-started/introduction`

**Without versioning:**
- `/getting-started/introduction`

---

## Version Status Options

- `stable` - Production ready
- `beta` - Beta release
- `rc` - Release candidate
- `deprecated` - Old version

---

## Troubleshooting

**Version selector not showing?**
```bash
# Check config
grep -A5 "versions" config.php

# Clear cache
php clear-cache.php

# Check version directories exist
ls -la docs/
```

**404 errors?**
```bash
# Verify structure
ls -la docs/v1.0/

# Clear cache
php clear-cache.php
```

---

## Full Documentation

- **Complete Guide**: [VERSIONING.md](VERSIONING.md)
- **Migration Help**: [MIGRATION_TO_VERSIONING.md](MIGRATION_TO_VERSIONING.md)
- **Implementation Details**: [MULTI_VERSION_IMPLEMENTATION_SUMMARY.md](MULTI_VERSION_IMPLEMENTATION_SUMMARY.md)

---

## Example Structure

```
docs/
├── v1.0/
│   ├── version.json
│   ├── 1.getting-started/
│   │   ├── 1.introduction.md
│   │   └── 2.installation.md
│   └── 2.core-concepts/
│       └── 1.basics.md
└── v2.0/
    ├── version.json
    ├── 1.getting-started/
    │   ├── 1.introduction.md
    │   └── 2.installation.md
    └── 2.core-concepts/
        └── 1.basics.md
```

---

That's it! Your documentation now supports multiple versions. 🚀
