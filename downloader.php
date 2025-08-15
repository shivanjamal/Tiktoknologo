<?php
// ---- downloader.php (Guhertoya Başتر) ----

// دیارکرنا جۆرێ داتایێ کو دێ هێتە زڤراندن (JSON)
header('Content-Type: application/json');

// 1. وەرگرتنا لینکێ ژ داخازییا JavaScript
$tiktokUrl = $_POST['url'] ?? '';

if (empty($tiktokUrl)) {
    // ئەگەر لینک بەتال بوو، پەیامەکا خەلەتیێ بزڤرینە
    echo json_encode(['success' => false, 'message' => 'تکایە لینکەکی دابنە.']);
    exit;
}

// 2. پێزانینێن APIـیا خۆ ل ڤێرە دابنە
$apiKey = '667f3c6a42mshf470829593814f0p1edcb0jsnc9f30089ef3e'; // ⚠️ پشتراست بە کو ئەڤ کلیلە یا دروستە و چالاکە
$apiHost = 'tiktok-video-no-watermark2.p.rapidapi.com';

// 3. دروستکرنا داخازیێ ب cURL بۆ RapidAPI
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://{$apiHost}/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => http_build_query(['url' => $tiktokUrl, 'hd' => 1]), // 'hd=1' بۆ کوالێتیا باشتر
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/x-www-form-urlencoded",
        "X-RapidAPI-Host: {$apiHost}",
        "X-RapidAPI-Key: {$apiKey}"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

// ئەگەر کێشەیەک د پەیوەندیێ دا هەبوو
if ($err) {
    echo json_encode(['success' => false, 'message' => 'کێشەیەک د پەیوەندییا دەرەکی دا روویدا: ' . $err]);
    exit;
}

// وەرگێرانا بەرسڤێ ژ JSON بۆ PHP
$data = json_decode($response, true);

// 4. پرۆسەکرنا بەرسڤا ژ API وەرگرتی
// ئەم پشتراست دبین کا بەرسڤ سەرکەفتی بوویە و لینکێ ڤیدیۆیێ تێدایە
if ($data && isset($data['code']) && $data['code'] === 0 && isset($data['data']['play'])) {

    // **گوهۆڕینا سەرەکی:**
    // ئەم لینکێ ژ API دهێت، راستەوخۆ د زڤرینین بۆ بکارهێنەری
    // بێی کو لسەر سێرڤەرێ خۆ خەزن بکەین
    echo json_encode([
        'success' => true,
        'downloadUrl' => $data['data']['play'], // لینکێ راستەوخۆ ژ API
        'title' => $data['data']['title'] ?? 'Sernav nehate dîtin',
        'cover' => $data['data']['cover'] ?? ''
    ]);

} else {
    // ئەگەر API خەلەتیەک زڤراند، پەیاما وێ نیشان بدە
    echo json_encode(['success' => false, 'message' => $data['message'] ?? 'ڤیدیۆ نەهاتە دیتن یان لینک خەلەتە. تکایە دوبارە هەول بدە.']);
}
?>