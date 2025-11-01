<?php

return [
    'site_name' => 'WharfDocs',
    'site_description' => 'Modern Php Documentation System',
    'theme' => 'default',
    'default_page' => 'getting-started/introduction',
    'github_repo' => '', // Set your GitHub repo URL for edit links
    'logo' => '/assets/logo.svg',
    'social_links' => [
        'github' => 'https://github.com/yourusername/wharfdocs',
        'twitter' => 'https://twitter.com/yourusername',
        'discord' => 'https://discord.gg/yourinvite',
    ],
    'features' => [
        'search' => true,
        'dark_mode' => true,
        'edit_link' => true,
        'toc' => true,
    ],
    'cache' => [
        'enabled' => true,
        'directory' => __DIR__ . '/cache',
    ]
];
