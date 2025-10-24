<?php
// Global configuration for building URLs that work on localhost and production
if (session_status() === PHP_SESSION_NONE) { session_start(); }

// Compute BASE_URL dynamically based on the project folder relative to the web document root.
// Example:
// - If project lives at C:\xampp\htdocs\today.blog  => BASE_URL = '/today.blog'
// - If project lives directly at C:\xampp\htdocs     => BASE_URL = '' (site root)
$docRootFs = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : null;
$projectRootFs = realpath(dirname(__DIR__)); // parent of /includes

if ($docRootFs && $projectRootFs && strpos($projectRootFs, $docRootFs) === 0) {
  $relative = str_replace('\\', '/', substr($projectRootFs, strlen($docRootFs)));
  $base = $relative ? ('/' . ltrim($relative, '/')) : '';
  define('BASE_URL', $base);
} else {
  // Fallback: empty base
  define('BASE_URL', '');
}

// Helper to prefix asset and link paths with the base URL
function asset(string $path): string {
  return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}
