<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: *");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

// الرابط المصدر الأساسي لملف القنوات الخاص بك
$target_m3u = "http://5startv.xyz:8080/get.php?username=DfErKszUWQ6r&password=LVvRSBavTkf4&type=m3u_plus";

if (isset($_GET['stream'])) {
    $video_url = base64_decode($_GET['stream']);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $video_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');
    
    if (strpos($video_url, '.m3u8') !== false) {
        header("Content-Type: application/x-mpegURL");
    } else {
        header("Content-Type: video/mp2t");
    }
    curl_exec($ch);
    curl_close($ch);
    exit;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target_m3u);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 40);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36');

$response = curl_exec($ch);
curl_close($ch);

if ($response) {
    header('Content-Type: audio/x-mpegurl');
    header('Content-Disposition: inline; filename="playlist.m3u"');

    $lines = explode("\n", $response);
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $current_script_url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, 'http://') === 0 || strpos($line, 'https://') === 0) {
            echo $current_script_url . "?stream=" . base64_encode($line) . "\n";
        } else {
            echo $line . "\n";
        }
    }
} else {
    header("HTTP/1.1 500 Internal Server Error");
    echo "خطأ: تعذر جلب البيانات من السيرفر الرئيسي.";
}
?>
