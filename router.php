<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$blocked = ['/config/', '/database.sql', '/install.php'];
foreach ($blocked as $b) {
    if (strpos($uri, $b) === 0) {
        http_response_code(403);
        exit('Forbidden');
    }
}

$ext = pathinfo($uri, PATHINFO_EXTENSION);
if (in_array($ext, ['sql', 'log', 'md', 'txt'])) {
    http_response_code(403);
    exit('Forbidden');
}

$file = __DIR__ . $uri;
if ($uri !== '/' && is_file($file)) {
    // Serve images with cache headers
    $ext = pathinfo($uri, PATHINFO_EXTENSION);
    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg', 'ico'])) {
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Expires: Thu, 31 Dec 2037 23:59:59 GMT');
    }
    return false;
}

if ($uri === '/' || $uri === '') {
    require __DIR__ . '/index.php';
} elseif (is_file(__DIR__ . $uri)) {
    require __DIR__ . $uri;
} else {
    http_response_code(404);
    echo 'Not Found';
}
