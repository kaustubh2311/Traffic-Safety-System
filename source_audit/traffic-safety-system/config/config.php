<?php
// Database settings can be supplied by Vercel environment variables.
// Local XAMPP values remain the default for development.
define('DB_HOST', getenv('DB_HOST') !== false ? getenv('DB_HOST') : '127.0.0.1');
define('DB_NAME', getenv('DB_NAME') !== false ? getenv('DB_NAME') : 'traffic_safety_db');
define('DB_USER', getenv('DB_USER') !== false ? getenv('DB_USER') : 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_PORT', getenv('DB_PORT') !== false ? getenv('DB_PORT') : '3306');

// A Vercel deployment uses paths at the domain root. XAMPP uses the project subfolder.
$configuredBaseUrl = getenv('BASE_URL');
define('BASE_URL', $configuredBaseUrl !== false ? rtrim($configuredBaseUrl, '/') : (getenv('VERCEL') !== false ? '' : '/traffic-safety-system'));
