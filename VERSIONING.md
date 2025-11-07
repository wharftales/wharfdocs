# Multi-Version Documentation Guide

WharfDocs now supports multi-version documentation, allowing you to maintain documentation for different versions of your project simultaneously.

## Features

- **Version Selector**: A dropdown in the header allows users to switch between versions
- **Version-Aware Navigation**: All navigation links automatically include the current version
- **Separate Content**: Each version has its own documentation files
- **Version Metadata**: Configure version labels, status, and release dates
- **Automatic Detection**: Version directories are automatically detected
- **Cache Support**: Each version's content is cached separately for performance

## Directory Structure

To enable versioning, organize your documentation in version-specific directories:

```
docs/
├── v1.0/
│   ├── version.json
│   ├── 1.getting-started/
│   │   ├── 1.introduction.md
│   │   └── 2.installation.md
│   └── 2.core-concepts/
│       └── 1.basics.md
├── v2.0/
│   ├── version.json
│   ├── 1.getting-started/
│   │   ├── 1.introduction.md
│   │   └── 2.installation.md
│   └── 2.core-concepts/
│       └── 1.basics.md
└── v3.0-beta/
    ├── version.json
    └── ...
```

## Version Directory Naming

Version directories must follow these patterns:
- `v1.0`, `v2.0`, `v3.0` (with 'v' prefix)
- `1.0`, `2.0`, `3.0` (without 'v' prefix)
- `v1.0-beta`, `v2.0-rc1` (with status suffix)

## Version Metadata (version.json)

Each version directory should contain a `version.json` file with metadata:

```json
{
    "number": "2.0",
    "label": "v2.0",
    "status": "stable",
    "released": "2024-11-01"
}
```

**Fields:**
- `number`: Version number for sorting (required)
- `label`: Display label in the UI (optional, defaults to directory name)
- `status`: Version status - `stable`, `beta`, `rc`, `deprecated` (optional, defaults to `stable`)
- `released`: Release date in YYYY-MM-DD format (optional)

## Configuration

Enable and configure versioning in `config.php`:

```php
'versions' => [
    'enabled' => true,              // Enable/disable versioning
    'default' => 'v2.0',           // Default version (null = latest)
    'show_selector' => true,       // Show version selector in UI
    'label' => 'Version',          // Label for version selector
]
```

### Configuration Options

- **enabled**: Set to `true` to enable multi-version support, `false` to disable
- **default**: The default version to show when no version is specified in the URL
  - Set to a specific version like `'v2.0'`
  - Set to `null` to always use the latest version
- **show_selector**: Show/hide the version selector dropdown in the header
- **label**: Text label for the version selector button

## URL Structure

When versioning is enabled, URLs include the version:

```
/v2.0/getting-started/introduction
/v1.0/core-concepts/basics
/v3.0-beta/advanced/features
```

**Without versioning:**
```
/getting-started/introduction
```

## Version Switching

Users can switch versions using:
1. **Version Selector Dropdown**: In the header next to the site logo
2. **Direct URL**: Navigate to a specific version by including it in the URL

When switching versions, users stay on the same page path if it exists in the new version.

## Version Badges

The version selector automatically shows badges:
- **Latest**: Displayed for the newest version
- **Beta/RC/Deprecated**: Displayed based on the status in `version.json`

Example: `v2.0 (Latest)` or `v3.0 (Beta)`

## Creating a New Version

1. **Create version directory:**
   ```bash
   mkdir docs/v3.0
   ```

2. **Add version metadata:**
   ```bash
   cat > docs/v3.0/version.json << EOF
   {
       "number": "3.0",
       "label": "v3.0",
       "status": "beta",
       "released": "2024-12-01"
   }
   EOF
   ```

3. **Copy or create documentation:**
   ```bash
   # Copy from previous version
   cp -r docs/v2.0/1.getting-started docs/v3.0/
   
   # Or create new documentation
   mkdir -p docs/v3.0/1.getting-started
   ```

4. **Clear cache:**
   ```bash
   php clear-cache.php
   ```

## Migrating Existing Documentation

If you have existing documentation without versions:

1. **Create version directory:**
   ```bash
   mkdir docs/v1.0
   ```

2. **Move existing docs:**
   ```bash
   mv docs/1.getting-started docs/v1.0/
   mv docs/2.core-concepts docs/v1.0/
   mv docs/3.advanced docs/v1.0/
   ```

3. **Add version metadata:**
   ```bash
   cat > docs/v1.0/version.json << EOF
   {
       "number": "1.0",
       "label": "v1.0",
       "status": "stable"
   }
   EOF
   ```

4. **Update config.php:**
   ```php
   'versions' => [
       'enabled' => true,
       'default' => 'v1.0',
       'show_selector' => true,
   ]
   ```

## Disabling Versioning

To disable versioning and return to single-version mode:

1. **Update config.php:**
   ```php
   'versions' => [
       'enabled' => false,
   ]
   ```

2. **Move docs back to root** (optional):
   ```bash
   mv docs/v1.0/* docs/
   rmdir docs/v1.0
   ```

## Search and Versioning

Search is version-aware:
- Search results are filtered to the current version
- Each version maintains its own search index
- Switching versions updates the search scope

## Caching

Each version has separate cache entries:
- Navigation is cached per version
- Page content is cached with version prefix
- Clear cache when updating version content

## Best Practices

1. **Maintain Consistency**: Keep the same structure across versions when possible
2. **Document Changes**: Add a changelog or "What's New" page for each version
3. **Deprecation Notices**: Mark old versions as deprecated in `version.json`
4. **Default Version**: Set the default to your recommended/latest stable version
5. **Version Naming**: Use semantic versioning (e.g., v1.0, v2.0, v2.1)

## Troubleshooting

### Version selector not showing
- Check `versions.enabled` is `true` in config.php
- Check `versions.show_selector` is `true`
- Ensure version directories exist in `docs/`
- Clear cache with `php clear-cache.php`

### 404 errors on version pages
- Verify version directory structure matches the URL
- Check that markdown files exist in the version directory
- Ensure numeric prefixes are correct (e.g., `1.introduction.md`)

### Version not detected
- Verify directory name matches version pattern (e.g., `v1.0`, `2.0`)
- Check `version.json` exists and is valid JSON
- Clear cache and reload

## Example Implementation

See the `docs/` directory for a working example with v1.0 and v2.0 versions.

## API

### VersionManager Methods

The `VersionManager` class provides these methods:

```php
// Get all versions
$versions = $versionManager->getVersions();

// Get current version from path
$version = $versionManager->extractVersionFromPath($path);

// Get default version
$default = $versionManager->getDefaultVersion();

// Check if versioning is enabled
$enabled = $versionManager->isVersioningEnabled();

// Get version docs path
$docsPath = $versionManager->getVersionDocsPath('v2.0');

// Add version to path
$fullPath = $versionManager->addVersionToPath('getting-started', 'v2.0');
// Returns: 'v2.0/getting-started'

// Remove version from path
$cleanPath = $versionManager->removeVersionFromPath('v2.0/getting-started');
// Returns: 'getting-started'
```

## Support

For issues or questions about versioning, please refer to the main documentation or open an issue on GitHub.
