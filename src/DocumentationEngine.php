<?php

namespace WharfDocs;

class DocumentationEngine
{
    private $docsPath;
    private $parser;
    private $navigation;
    private $searchIndexer;
    private $config;
    private $cache;

    public function __construct()
    {
        $this->docsPath = __DIR__ . '/../docs';
        $this->loadConfig();
        
        // Initialize cache
        $cacheEnabled = $this->config['cache']['enabled'] ?? true;
        $cacheDir = $this->config['cache']['directory'] ?? __DIR__ . '/../cache';
        $this->cache = new Cache($cacheDir, $cacheEnabled);
        
        $this->parser = new MarkdownParser();
        $this->navigation = new NavigationBuilder($this->docsPath, $this->cache);
        $this->searchIndexer = new SearchIndexer($this->docsPath, $this->cache);
    }

    private function loadConfig()
    {
        $configFile = __DIR__ . '/../config.php';
        if (file_exists($configFile)) {
            $this->config = require $configFile;
        } else {
            $this->config = [
                'site_name' => 'Documentation',
                'site_description' => 'Project Documentation',
                'theme' => 'default',
                'default_page' => 'getting-started/introduction'
            ];
        }
        
        // Make config available globally for templates
        $GLOBALS['config'] = $this->config;
    }

    public function render($path = '')
    {
        // Default to introduction page
        if (empty($path) || $path === '/') {
            $path = $this->config['default_page'];
        }

        // Find the markdown file
        $mdFile = $this->findMarkdownFile($path);
        
        if (!$mdFile) {
            $this->render404();
            return;
        }

        // Try to get cached page data
        $cacheKey = 'page_' . md5($path);
        $cachedData = $this->cache->get($cacheKey, [$mdFile]);
        
        if ($cachedData !== null) {
            // Use cached data but rebuild navigation (it has its own cache)
            $cachedData['navigation'] = $this->navigation->build();
            $this->renderPage($cachedData);
            return;
        }

        // Parse the markdown
        $content = file_get_contents($mdFile);
        $parsedContent = $this->parser->parse($content);
        
        // Get metadata
        $metadata = $this->parser->extractMetadata($content);
        
        // Generate permalink
        $permalink = $this->generatePermalink($path);
        
        // Get prev/next pages
        $prevNext = $this->getPrevNextPages($path);
        
        // Prepare page data
        $pageData = [
            'content' => $parsedContent['html'],
            'title' => $metadata['title'] ?? $this->extractTitle($content),
            'description' => $metadata['description'] ?? '',
            'toc' => $parsedContent['toc'],
            'path' => $path,
            'permalink' => $permalink,
            'navigation' => $this->navigation->build(),
            'editUrl' => $this->generateEditUrl($mdFile),
            'prevPage' => $prevNext['prev'],
            'nextPage' => $prevNext['next']
        ];
        
        // Cache the page data
        $this->cache->set($cacheKey, $pageData);
        
        // Render the page
        $this->renderPage($pageData);
    }

    private function findMarkdownFile($path)
    {
        // Split path into parts
        $parts = explode('/', $path);
        
        // Try to find the file with numeric prefixes
        $currentPath = $this->docsPath;
        
        foreach ($parts as $part) {
            if (empty($part)) continue;
            
            // Look for directory or file with numeric prefix
            $found = false;
            $entries = scandir($currentPath);
            
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') continue;
                
                // Remove numeric prefix for comparison
                $cleanName = preg_replace('/^\d+\./', '', $entry);
                $cleanName = preg_replace('/\.md$/', '', $cleanName);
                
                if ($cleanName === $part) {
                    $currentPath .= '/' . $entry;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                return null;
            }
        }
        
        // If currentPath is a directory, look for index.md or README.md
        if (is_dir($currentPath)) {
            $indexFiles = ['index.md', 'README.md'];
            foreach ($indexFiles as $indexFile) {
                $entries = scandir($currentPath);
                foreach ($entries as $entry) {
                    $cleanName = preg_replace('/^\d+\./', '', $entry);
                    if ($cleanName === $indexFile && file_exists($currentPath . '/' . $entry)) {
                        return $currentPath . '/' . $entry;
                    }
                }
            }
            return null;
        }
        
        // If it's a file, return it
        if (file_exists($currentPath)) {
            return $currentPath;
        }
        
        return null;
    }

    private function extractTitle($content)
    {
        if (preg_match('/^#\s+(.+)$/m', $content, $matches)) {
            return trim($matches[1]);
        }
        return 'Documentation';
    }

    private function generatePermalink($path)
    {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        return $protocol . '://' . $host . $basePath . '/' . $path;
    }

    private function generateEditUrl($filePath)
    {
        // This can be configured to point to your Git repository
        $relativePath = str_replace($this->docsPath . '/', '', $filePath);
        return '#'; // Placeholder - configure with your repo URL
    }

    private function getPrevNextPages($currentPath)
    {
        // Get all pages in a flat list
        $allPages = $this->flattenNavigation($this->navigation->build());
        
        // Find current page index
        $currentIndex = -1;
        foreach ($allPages as $index => $page) {
            if ($page['path'] === $currentPath) {
                $currentIndex = $index;
                break;
            }
        }
        
        $prev = null;
        $next = null;
        
        if ($currentIndex > 0) {
            $prev = $allPages[$currentIndex - 1];
        }
        
        if ($currentIndex >= 0 && $currentIndex < count($allPages) - 1) {
            $next = $allPages[$currentIndex + 1];
        }
        
        return [
            'prev' => $prev,
            'next' => $next
        ];
    }

    private function flattenNavigation($navigation, &$result = [])
    {
        foreach ($navigation as $item) {
            if ($item['type'] === 'page') {
                $result[] = [
                    'title' => $item['title'],
                    'path' => $item['path']
                ];
            }
            
            if (isset($item['children']) && !empty($item['children'])) {
                $this->flattenNavigation($item['children'], $result);
            }
        }
        
        return $result;
    }

    private function renderPage($data)
    {
        extract($data);
        include __DIR__ . '/../templates/layout.php';
    }

    private function render404()
    {
        http_response_code(404);
        $data = [
            'content' => '<h1>404 - Page Not Found</h1><p>The requested documentation page could not be found.</p>',
            'title' => '404 - Not Found',
            'description' => '',
            'toc' => [],
            'path' => '',
            'permalink' => '',
            'navigation' => $this->navigation->build(),
            'editUrl' => '#',
            'prevPage' => null,
            'nextPage' => null
        ];
        $this->renderPage($data);
    }

    public function search($query)
    {
        return $this->searchIndexer->search($query);
    }

    public function getNavigation()
    {
        return $this->navigation->build();
    }
    
    /**
     * Clear all caches
     */
    public function clearCache()
    {
        return $this->cache->clear();
    }
    
    /**
     * Get cache instance
     */
    public function getCache()
    {
        return $this->cache;
    }
}
