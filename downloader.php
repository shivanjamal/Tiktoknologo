<?php
// ---- downloader.php ----

// دیارکرنا جۆرێ داتایێ کو دێ هێتە زڤراندن (JSON)
header('Content-Type: application/json');
error_reporting(0); // شاردنا خەلەتیێن نەپێدڤی

// 1. وەرگرتنا لینکێ ژ داخازییا JavaScript
$tiktokUrl = $_POST['url'] ?? '';

if (empty($tiktokUrl)) {
    echo json_encode(['success' => false, 'message' => 'تکایە لینکەکی دابنە.']);
    exit;
}

// 2. پێزانینێن APIـیا خۆ ل ڤێرە دابنە
$apiKey = '667f3c6a42mshf470829593814f0p1edcb0jsnc9f30089ef3e'; // ⚠️ کلیلا خۆ یا API ل ڤێرە دابنە
$apiHost = 'tiktok-video-no-watermark2.p.rapidapi.com';

// 3. دروستکرنا داخازیێ ب cURL بۆ RapidAPI
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://{$apiHost}/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => http_build_query(['url' => $tiktokUrl]),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/x-www-form-urlencoded",
        "X-RapidAPI-Host: {$apiHost}",
        "X-RapidAPI-Key: {$apiKey}"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo json_encode(['success' => false, 'message' => 'کێشەیەک د پەیوەندییا دەرەکی دا روویدا.']);
    exit;
}

$data = json_decode($response, true);

// 4. پرۆسەکرنا بەرسڤا ژ API وەرگرتی
if ($data && $data['code'] === 0 && isset($data['data']['play'])) {
    $videoUrl = $data['data']['play']; // لینکێ راستەوخۆ یێ ڤیدیۆیێ
    $videoTitle = $data['data']['title'] ?? 'TikTok Video';
    $videoCover = $data['data']['cover'] ?? '';

    // 5. داگرتن و خەزنکرنا ڤیدیۆیێ لسەر سێرڤەری
    $videosFolder = 'videos';
    if (!is_dir($videosFolder)) {
        mkdir($videosFolder, 0755, true); // ئەگەر فۆلدەر نەبیت، دروست بکە
    }

    $fileName = uniqid('tiktok_', true) . '.mp4'; // دروستکرنا ناڤەکێ ئێکتا بۆ فایلی
    $filePath = $videosFolder . '/' . $fileName;

    // داگرتنا فایلی ژ لینکێ وەرگرتی
    $videoContent = @file_get_contents($videoUrl);

    if ($videoContent === FALSE) {
        echo json_encode(['success' => false, 'message' => 'نەشیا ڤیدیۆ بهێتە داگرتن ژ سێرڤەرێ سەرەکی.']);
        exit;
    }
    
    // خەزنکرنا فایلی لسەر سێرڤەرێ تە
    file_put_contents($filePath, $videoContent);

    // 6. هنارتنا لینکێ ڤیدیۆیا خەزنکری بۆ بکارهێنەری
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $localUrl = "{$protocol}://{$_SERVER['HTTP_HOST']}/{$filePath}";
    
    echo json_encode([
        'success' => true,
        'downloadUrl' => $localUrl,
        'title' => $videoTitle,
        'cover' => $videoCover
    ]);

} else {
    echo json_encode(['success' => false, 'message' => $data['message'] ?? 'ڤیدیۆ نەهاتە دیتن یان لینک خەلەتە.']);
}
?>