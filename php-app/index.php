<?php
// Mitigations for php2shell (CVE-2026-109618/109619) on EOL PHP 7.4
// Disable session upload progress and remove memory limits that aid exploitation.
@ini_set('session.upload_progress.enabled', '0');
@ini_set('session.upload_progress.cleanup', '1');
@ini_set('memory_limit', '-1');
@ini_set('file_uploads', '0');

// Block multipart/form-data requests (php2shell trigger)
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'multipart/form-data') !== false) {
    http_response_code(415);
    echo "Unsupported Media Type: multipart/form-data is not allowed\n";
    exit;
}

$action = $_GET['action'] ?? 'home';

if ($action === 'fetch') {
    // Fix SSRF: validate and restrict URL to trusted internal hosts only.
    $url = $_GET['url'] ?? 'http://localhost:3000/api/data';
    $allowedHosts = ['localhost', '127.0.0.1', '::1', 'node-service'];
    $allowedSchemes = ['http', 'https'];

    $parsed = parse_url($url);
    if ($parsed === false || !isset($parsed['scheme'], $parsed['host'])) {
        http_response_code(400);
        echo "Invalid URL\n";
        exit;
    }
    if (!in_array(strtolower($parsed['scheme']), $allowedSchemes, true)) {
        http_response_code(400);
        echo "URL scheme not allowed\n";
        exit;
    }
    if (!in_array($parsed['host'], $allowedHosts, true)) {
        http_response_code(403);
        echo "Target host not allowed\n";
        exit;
    }
    if (isset($parsed['port']) && !in_array((int)$parsed['port'], [80, 443, 3000], true)) {
        http_response_code(400);
        echo "Port not allowed\n";
        exit;
    }
    // Prevent embedded credentials / path traversal tricks
    if (isset($parsed['user']) || isset($parsed['pass'])) {
        http_response_code(400);
        echo "Credentials in URL not allowed\n";
        exit;
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    if ($response === false) {
        http_response_code(502);
        echo "Upstream request failed\n";
    } else {
        echo $response;
    }
    curl_close($ch);
    exit;
}

if ($action === 'upload') {
    // Input validation for file uploads (defense in depth)
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo "Upload error\n";
            exit;
        }
        $maxSize = 2 * 1024 * 1024; // 2MB
        if (!isset($file['size']) || $file['size'] > $maxSize) {
            http_response_code(413);
            echo "File too large\n";
            exit;
        }
        $originalName = basename($file['name'] ?? '');
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'txt'];
        if (!in_array($ext, $allowedExts, true)) {
            http_response_code(415);
            echo "File type not allowed\n";
            exit;
        }
        // Validate MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
        if ($finfo) {
            finfo_close($finfo);
        }
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];
        if (!in_array($mime, $allowedMimes, true)) {
            http_response_code(415);
            echo "MIME type not allowed\n";
            exit;
        }
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $originalName);
        $dir = '/var/www/html/uploads/';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $target = $dir . $safeName;
        if (move_uploaded_file($file['tmp_name'], $target)) {
            echo "Uploaded: " . $safeName;
        } else {
            http_response_code(500);
            echo "Upload failed\n";
        }
        exit;
    }
    http_response_code(400);
    echo "No file provided\n";
    exit;
}

echo "php2shell-primagen app\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "Endpoints: ?action=fetch&url=... | ?action=upload (multipart)\n";
