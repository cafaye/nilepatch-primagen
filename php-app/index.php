<?php
// ThePrimeagen's cursed app: PHP calling Node.js via cURL
// Running on EOL PHP 7.4 (php2shell vulnerable)
// Deployed on Railway (simulated)

$action = $_GET['action'] ?? 'home';

if ($action === 'fetch') {
    // VULNERABLE: SSRF via user-controlled URL passed to curl
    $url = $_GET['url'] ?? 'http://localhost:3000/api/data';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    echo $response;
    exit;
}

if ($action === 'upload') {
    // VULNERABLE: file upload triggers multipart/form-data (php2shell trigger)
    if (isset($_FILES['file'])) {
        $dir = '/var/www/html/uploads/';
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        move_uploaded_file($_FILES['file']['tmp_name'], $dir . $_FILES['file']['name']);
        echo "Uploaded: " . $_FILES['file']['name'];
        exit;
    }
}

echo "php2shell-primagen app\n";
echo "PHP version: " . PHP_VERSION . "\n";
echo "Endpoints: ?action=fetch&url=... | ?action=upload (multipart)\n";
