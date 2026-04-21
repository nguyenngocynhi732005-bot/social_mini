<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylor@laravel.com>
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/') {
    $publicFile = __DIR__ . '/public' . $uri;

    if (is_file($publicFile)) {
        // When PHP built-in server is started from project root (without -t public),
        // static files under /public are not served correctly. Stream them manually.
        $mimeType = function_exists('mime_content_type') ? mime_content_type($publicFile) : null;
        if (!$mimeType) {
            $mimeType = 'application/octet-stream';
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . (string) filesize($publicFile));
        readfile($publicFile);
        return true;
    }
}

require_once __DIR__.'/public/index.php';
