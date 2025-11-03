# WharfDocs Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────┐
│                        User Browser                          │
│  ┌────────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  │
│  │  Navigate  │  │  Search  │  │Dark Mode │  │   TOC    │  │
│  └────────────┘  └──────────┘  └──────────┘  └──────────┘  │
└─────────────────────────────────────────────────────────────┘
                            │
                            ▼
┌─────────────────────────────────────────────────────────────┐
│                      index.php (Router)                      │
│                                                               │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Request Handler                                      │   │
│  │  • Parse URL path                                     │   │
│  │  • Route to API or Page                               │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            │
            ┌───────────────┴───────────────┐
            ▼                               ▼
┌─────────────────────┐         ┌─────────────────────┐
│   API Endpoints     │         │  Page Rendering     │
│                     │         │                     │
│  /api/search        │         │  DocumentationEngine│
│  /api/navigation    │         │                     │
└─────────────────────┘         └─────────────────────┘
            │                               │
            ▼                               ▼
┌─────────────────────┐         ┌─────────────────────┐
│  SearchIndexer      │         │  MarkdownParser     │
│                     │         │                     │
│  • Index all docs   │         │  • Parse Markdown   │
│  • Search content   │         │  • Generate TOC     │
│  • Rank results     │         │  • Add anchors      │
└─────────────────────┘         └─────────────────────┘
            │                               │
            │                   ┌───────────┴───────────┐
            │                   ▼                       ▼
            │         ┌─────────────────┐   ┌─────────────────┐
            │         │NavigationBuilder│   │  Template       │
            │         │                 │   │  (layout.php)   │
            │         │ • Scan docs/    │   │                 │
            │         │ • Build tree    │   │  • HTML output  │
            │         │ • Order items   │   │  • CSS/JS       │
            │         └─────────────────┘   └─────────────────┘
            │                   │                       │
            └───────────────────┴───────────────────────┘
                                │
                                ▼
                    ┌─────────────────────┐
                    │   docs/ Directory   │
                    │                     │
                    │  Markdown Files     │
                    │  • *.md files       │
                    │  • Frontmatter      │
                    │  • Content          │
                    └─────────────────────┘
```

## Request Flow

### Page Request Flow

```
1. User requests: /getting-started/introduction
                    │
                    ▼
2. .htaccess rewrites to: index.php
                    │
                    ▼
3. index.php parses path: "getting-started/introduction"
                    │
                    ▼
4. DocumentationEngine::render($path)
                    │
                    ├─→ findMarkdownFile()
                    │   └─→ docs/1.getting-started/1.introduction.md
                    │
                    ├─→ MarkdownParser::parse()
                    │   ├─→ Remove frontmatter
                    │   ├─→ Parse to HTML
                    │   ├─→ Generate TOC
                    │   └─→ Add heading anchors
                    │
                    ├─→ NavigationBuilder::build()
                    │   ├─→ Scan docs/ directory
                    │   ├─→ Extract titles
                    │   └─→ Build navigation tree
                    │
                    ├─→ generatePermalink()
                    │   └─→ Create canonical URL
                    │
                    └─→ renderPage()
                        └─→ templates/layout.php
                            └─→ HTML output to browser
```

### Search Request Flow

```
1. User types in search box
                    │
                    ▼
2. JavaScript debounces input (300ms)
                    │
                    ▼
3. AJAX request: /api/search?q=query
                    │
                    ▼
4. index.php routes to API handler
                    │
                    ▼
5. DocumentationEngine::search($query)
                    │
                    ▼
6. SearchIndexer::search($query)
                    │
                    ├─→ Match against titles (100 points)
                    ├─→ Match against headings (50 points)
                    ├─→ Match against content (10 points)
                    ├─→ Sort by score
                    └─→ Return top 10 results
                    │
                    ▼
7. JSON response to browser
                    │
                    ▼
8. JavaScript renders results
```

## Component Architecture

### DocumentationEngine.php

```php
DocumentationEngine
│
├── __construct()
│   ├─→ Initialize MarkdownParser
│   ├─→ Initialize NavigationBuilder
│   ├─→ Initialize SearchIndexer
│   └─→ Load config
│
├── render($path)
│   ├─→ findMarkdownFile($path)
│   ├─→ parse content
│   ├─→ extract metadata
│   ├─→ generate permalink
│   └─→ renderPage()
│
├── search($query)
│   └─→ SearchIndexer::search()
│
└── getNavigation()
    └─→ NavigationBuilder::build()
```

### MarkdownParser.php

```php
MarkdownParser
│
├── __construct()
│   └─→ Initialize ParsedownExtra
│
├── parse($markdown)
│   ├─→ removeFrontmatter()
│   ├─→ parsedown->text()
│   ├─→ generateTOC()
│   └─→ addHeadingAnchors()
│
├── extractMetadata($markdown)
│   └─→ Parse YAML frontmatter
│
└── slugify($text)
    └─→ Create URL-friendly slugs
```

### NavigationBuilder.php

```php
NavigationBuilder
│
├── build()
│   └─→ scanDirectory($docsPath)
│
├── scanDirectory($dir)
│   ├─→ Read directory entries
│   ├─→ Process subdirectories (sections)
│   ├─→ Process .md files (pages)
│   └─→ Sort by numeric prefix
│
├── formatName($name)
│   └─→ Clean display names
│
└── extractTitleFromFile($file)
    ├─→ Try frontmatter
    ├─→ Try H1 heading
    └─→ Fallback to filename
```

### SearchIndexer.php

```php
SearchIndexer
│
├── __construct($docsPath)
│   └─→ buildIndex()
│
├── buildIndex()
│   └─→ indexDirectory($docsPath)
│
├── indexDirectory($dir)
│   ├─→ Scan for .md files
│   └─→ indexFile() for each
│
├── indexFile($file)
│   ├─→ Extract title
│   ├─→ Extract headings
│   ├─→ Extract content
│   └─→ Store in index array
│
└── search($query)
    ├─→ Score title matches
    ├─→ Score heading matches
    ├─→ Score content matches
    ├─→ Sort by score
    └─→ Return top results
```

## Data Flow

### File Structure → Navigation

```
docs/
├── 1.getting-started/
│   ├── 1.introduction.md
│   └── 2.installation.md
└── 2.core-concepts/
    └── 1.markdown-syntax.md

                ↓

[
  {
    type: "section",
    title: "Getting Started",
    children: [
      { type: "page", title: "Introduction", path: "getting-started/introduction" },
      { type: "page", title: "Installation", path: "getting-started/installation" }
    ]
  },
  {
    type: "section",
    title: "Core Concepts",
    children: [
      { type: "page", title: "Markdown Syntax", path: "core-concepts/markdown-syntax" }
    ]
  }
]
```

### Markdown → HTML

```
---
title: Introduction
---

# Introduction

Welcome to **WharfDocs**!

## Features

- Search
- Navigation

                ↓

{
  metadata: {
    title: "Introduction"
  },
  html: "<h1>Introduction</h1><p>Welcome to <strong>WharfDocs</strong>!</p>...",
  toc: [
    { level: 2, title: "Features", slug: "features" }
  ]
}
```

## Frontend Architecture

### JavaScript Components

```
Document Ready
│
├── Syntax Highlighting
│   └─→ hljs.highlightElement() for all <pre><code>
│
├── Dark Mode Toggle
│   ├─→ Read localStorage
│   ├─→ Apply theme
│   └─→ Listen for toggle clicks
│
├── Search
│   ├─→ Listen to input events
│   ├─→ Debounce (300ms)
│   ├─→ Fetch /api/search
│   └─→ Render results
│
├── Sidebar Toggle (Mobile)
│   └─→ Toggle sidebar visibility
│
└── Smooth Scrolling
    └─→ Anchor links scroll smoothly
```

### CSS Architecture

```
CSS Variables (Theme)
├── Light Mode
│   ├── --color-primary
│   ├── --color-bg
│   └── --color-text
│
└── Dark Mode (.dark)
    ├── --color-primary
    ├── --color-bg (dark)
    └── --color-text (light)

Tailwind Utilities
├── Layout (flex, grid)
├── Spacing (p-, m-)
└── Typography (text-, font-)

Custom Classes
├── .prose (content styling)
├── .nav-item (navigation)
├── .heading-anchor (anchors)
└── .search-result (search)
```

## Performance Optimizations

### Backend
- **In-memory indexing**: Search index built once on init
- **Minimal file I/O**: Only read requested files
- **No database**: Pure file-based system

### Frontend
- **CDN assets**: Tailwind and Highlight.js from CDN
- **Debounced search**: Reduces API calls
- **Lazy loading**: Only load what's needed
- **CSS variables**: Fast theme switching

## Security Measures

```
Input Validation
├── URL path sanitization
├── Search query escaping
└── HTML output escaping

Parsedown
└── Safe mode enabled

.htaccess
├── Deny access to .git
├── Deny access to .md files (direct)
└── Force index.php routing

PHP
├── No eval() or exec()
├── No user file uploads
└── Read-only file operations
```

## Scalability Considerations

### Small Sites (< 100 pages)
- ✅ Perfect performance
- ✅ Instant search
- ✅ Fast navigation

### Medium Sites (100-500 pages)
- ✅ Good performance
- ⚠️ Consider search caching
- ✅ Navigation still fast

### Large Sites (> 500 pages)
- ⚠️ Consider static generation
- ⚠️ Implement search caching
- ⚠️ Lazy-load navigation

## Extension Points

### Adding Features

1. **Custom Markdown Syntax**
   - Modify `MarkdownParser::parse()`

2. **Custom Navigation Logic**
   - Modify `NavigationBuilder::build()`

3. **Enhanced Search**
   - Modify `SearchIndexer::search()`

4. **Custom Templates**
   - Create new templates in `templates/`

5. **API Endpoints**
   - Add routes in `index.php`

## Deployment Architecture

```
Production Server
│
├── Web Server (Apache/Nginx)
│   ├─→ Serve static assets
│   ├─→ URL rewriting
│   └─→ PHP-FPM
│
├── PHP Runtime
│   ├─→ Execute index.phpr
│
└── File System
    ├─→ docs/ (Markdown files)
    ├─→ src/ (PHP classes)
    ├─→ templates/ (HTML)
    └─→ assets/ (CSS/JS)
```

---

**Architecture Status**: ✅ Complete and Production-Ready
