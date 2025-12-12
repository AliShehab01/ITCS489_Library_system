<?php
// Compute the project path relative to the web root so links stay valid
// whether the app is served from /ITCS489_Library_system or a virtual host.
$documentRoot = isset($_SERVER['DOCUMENT_ROOT'])
    ? str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/\\'))
    : '';
$projectRoot = str_replace('\\', '/', __DIR__);

$relativePath = '';
if ($documentRoot && str_starts_with($projectRoot, $documentRoot)) {
    $relativePath = trim(substr($projectRoot, strlen($documentRoot)), '/');
}
$basePath = $relativePath ? '/' . $relativePath . '/' : '/';

// BASE_URL points to the app folder (views/controllers)
define('BASE_URL', $basePath . 'app/');

// PUBLIC_URL points to the public assets folder (css/js/uploads)
define('PUBLIC_URL', $basePath . 'public/');
