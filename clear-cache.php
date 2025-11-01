#!/usr/bin/env php
<?php
/**
 * Clear Cache Utility
 * Run this script to clear all cached content
 */

require_once __DIR__ . '/src/Cache.php';

use WharfDocs\Cache;

// Load config
$config = require __DIR__ . '/config.php';
$cacheDir = $config['cache']['directory'] ?? __DIR__ . '/cache';

// Initialize cache
$cache = new Cache($cacheDir, true);

// Clear cache
echo "Clearing cache...\n";
if ($cache->clear()) {
    echo "✓ Cache cleared successfully!\n";
    echo "Cache directory: $cacheDir\n";
} else {
    echo "✗ Failed to clear cache or cache directory doesn't exist.\n";
}
