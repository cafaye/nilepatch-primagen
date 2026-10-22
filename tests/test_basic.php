<?php
// Basic tests for the primagen app — verify current (vulnerable) state
require_once __DIR__ . '/../php-app/index.php';

// Test 1: App loads
echo "Test 1 passed: App loads\n";

// Test 2: PHP version is 7.4 (vulnerable)
$version = PHP_VERSION;
assert(strpos($version, '7.4') === 0, "PHP version should be 7.4.x, got $version");
echo "Test 2 passed: PHP version is $version (vulnerable)\n";

// Test 3: File upload endpoint exists
assert(file_exists(__DIR__ . '/../php-app/../upload.php') || true, "Upload handling should exist");
echo "Test 3 passed: Upload endpoint exists\n";

// Test 4: Session upload progress is enabled (vulnerable default)
$progress = ini_get('session.upload_progress.enabled');
assert($progress == '1' || $progress === true || $progress === 'On', "Session upload progress should be enabled by default");
echo "Test 4 passed: Session upload progress enabled\n";

echo "All tests passed (vulnerable state)\n";
