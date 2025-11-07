# Migration Guide: Adding Multi-Version Support

This guide helps you migrate your existing WharfDocs installation to use the new multi-version documentation feature.

## Option 1: Keep Single Version (No Migration Needed)

If you don't need multi-version support, simply set `versions.enabled` to `false` in `config.php`:

```php
'versions' => [
    'enabled' => false,
]
```

Your documentation will continue to work exactly as before.

## Option 2: Migrate to Multi-Version

Follow these steps to enable versioning for your existing documentation:

### Step 1: Backup Your Documentation

```bash
cp -r docs docs-backup
```

### Step 2: Create Version Directory

```bash
mkdir docs/v1.0
```

### Step 3: Move Existing Documentation

```bash
# Move all existing documentation folders to v1.0
mv docs/1.getting-started docs/v1.0/
mv docs/2.core-concepts docs/v1.0/
mv docs/3.advanced docs/v1.0/
# Repeat for all your documentation folders
```

### Step 4: Create Version Metadata

Create `docs/v1.0/version.json`:

```json
{
    "number": "1.0",
    "label": "v1.0",
    "status": "stable",
    "released": "2024-01-01"
}
```

### Step 5: Update Configuration

Edit `config.php`:

```php
'versions' => [
    'enabled' => true,
    'default' => 'v1.0',
    'show_selector' => true,
    'label' => 'Version',
]
```

### Step 6: Clear Cache

```bash
php clear-cache.php
```

### Step 7: Test

Visit your documentation site. You should now see:
- Version selector in the header
- URLs with version prefix: `/v1.0/getting-started/introduction`
- All navigation working correctly

## Creating Additional Versions

Once you've migrated to v1.0, you can create new versions:

### For v2.0 (Copy from v1.0)

```bash
# Copy the entire v1.0 directory
cp -r docs/v1.0 docs/v2.0

# Update version metadata
cat > docs/v2.0/version.json << EOF
{
    "number": "2.0",
    "label": "v2.0",
    "status": "stable",
    "released": "2024-11-01"
}
EOF

# Update the default version in config.php
# 'default' => 'v2.0',

# Clear cache
php clear-cache.php
```

### For v3.0-beta (New Version)

```bash
# Create new version directory
mkdir docs/v3.0-beta

# Add version metadata
cat > docs/v3.0-beta/version.json << EOF
{
    "number": "3.0",
    "label": "v3.0 Beta",
    "status": "beta",
    "released": "2024-12-01"
}
EOF

# Create documentation structure
mkdir -p docs/v3.0-beta/1.getting-started
# Add your new documentation files

# Clear cache
php clear-cache.php
```

## URL Changes After Migration

### Before (Single Version)
```
/getting-started/introduction
/core-concepts/basics
/advanced/features
```

### After (Multi-Version)
```
/v1.0/getting-started/introduction
/v1.0/core-concepts/basics
/v1.0/advanced/features
```

## Updating Links

If you have hardcoded links in your documentation, you may need to update them:

### Internal Links (Recommended)
Use relative paths - they work automatically:
```markdown
[See Installation](installation)
[Back to Getting Started](../getting-started/introduction)
```

### Absolute Links (If Needed)
If you must use absolute paths, they'll need version prefixes:
```markdown
[See v1.0 Installation](/v1.0/getting-started/installation)
[See v2.0 Installation](/v2.0/getting-started/installation)
```

## Rollback Plan

If you need to rollback to single-version mode:

### Step 1: Restore Documentation

```bash
# Move docs back from version directory
mv docs/v1.0/* docs/
rmdir docs/v1.0
```

### Step 2: Disable Versioning

```php
'versions' => [
    'enabled' => false,
]
```

### Step 3: Clear Cache

```bash
php clear-cache.php
```

## Common Issues

### Issue: 404 errors after migration

**Solution:** Ensure all documentation folders were moved to the version directory and clear cache.

### Issue: Version selector not showing

**Solution:** 
- Check `versions.enabled` is `true`
- Check `versions.show_selector` is `true`
- Ensure at least one version directory exists
- Clear cache

### Issue: Navigation links broken

**Solution:** Navigation is automatically version-aware. Clear cache and reload.

### Issue: Search not working

**Solution:** Search is version-scoped. Clear cache to rebuild search index for each version.

## Best Practices

1. **Start with v1.0**: Even if it's your first version, use v1.0 for clarity
2. **Use Semantic Versioning**: v1.0, v1.1, v2.0, etc.
3. **Document Changes**: Add a changelog or "What's New" page for each version
4. **Set Default Wisely**: Set the default version to your recommended/latest stable version
5. **Test Thoroughly**: Test all navigation, search, and links after migration

## Need Help?

- See [VERSIONING.md](VERSIONING.md) for complete versioning documentation
- Check the example in `docs/v1.0` and `docs/v2.0`
- Review the configuration in `config.php`

## Summary

The migration process is straightforward:
1. Create version directory (`docs/v1.0`)
2. Move existing docs into it
3. Add `version.json`
4. Enable versioning in config
5. Clear cache

Your documentation will now support multiple versions with a clean UI for switching between them!
