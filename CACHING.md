# Caching System

WharfTales Documentation includes an intelligent caching system that automatically improves performance while keeping content fresh.

## How It Works

The caching system automatically caches:
- **Parsed HTML pages** - Rendered markdown content
- **Navigation structure** - Site navigation menu
- **Search index** - Full-text search data

### Auto-Refresh on Changes

The cache automatically invalidates and refreshes when:
- Any markdown file is modified
- Files are added or removed from the docs directory
- The source file is newer than the cached version

This means you can edit your documentation and see changes immediately without manually clearing the cache.

## Configuration

Configure caching in `config.php`:

```php
'cache' => [
    'enabled' => true,                    // Enable/disable caching
    'directory' => __DIR__ . '/cache',    // Cache storage directory
]
```

## Manual Cache Management

### Clear All Cache

Run the cache clearing script:

```bash
php clear-cache.php
```

Or programmatically:

```php
$docsEngine = new DocumentationEngine();
$docsEngine->clearCache();
```

### Disable Caching

Set `enabled` to `false` in `config.php`:

```php
'cache' => [
    'enabled' => false,
]
```

## Performance Benefits

With caching enabled:
- **First request**: Normal speed (cache is built)
- **Subsequent requests**: 10-50x faster (served from cache)
- **After content update**: Automatic rebuild on next request

## Cache Storage

- Cache files are stored in the `/cache` directory
- Files are serialized PHP data with `.cache` extension
- The directory is automatically created if it doesn't exist
- Cache directory is excluded from version control (`.gitignore`)

## Best Practices

1. **Keep caching enabled in production** for best performance
2. **Disable during development** if you want to see every change instantly
3. **Clear cache after major updates** to ensure consistency
4. **Monitor cache directory size** if you have a very large documentation site

## Technical Details

The cache system uses file modification times (`filemtime()`) to determine if source files have changed. When a cached item is requested:

1. Check if cache file exists
2. Compare source file modification time with cache time
3. If source is newer, rebuild and cache
4. If cache is newer, serve from cache

This approach ensures zero-configuration automatic updates while maintaining excellent performance.
