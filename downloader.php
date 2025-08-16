<?php
// ---- downloader.php (Guhertoya bo Diyarkirina Kêşeyan - Debugging) ----

header('Content-Type: application/json');

// ******** BEŞÊ DIYARKIRINA KÊŞEYAN ********
// Em dê هەمی خەلەتیان نیشان دەین دا بزانین کێشە چیە
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// *******************************************

// 1. پشتراستبوون ژ هەبوونا cURL
if (!function_exists('curl_init')) {
    echo json_encode(['success' => false, 'message' => 'Kêşe: Pirtûkxaneya cURL li ser serverê te nehatiye sazkirin.']);
    exit;
}

// 2. وەرگرتنا لینکێ
$tiktokUrl = $_POST['url'] ?? '';
if (empty($tiktokUrl)) {
    echo json_encode(['success' => false, 'message' => 'تکایە لینکەکی دابنە.']);
    exit;
}

// 3. پێزانینێن API
$apiKey = '667f3c6a42mshf470829593814f0p1edcb0jsnc9f30089ef3e'; // ⚠️ تکایە دوبارە پشتراست بە کو ئەڤ کلیلە یا دروستە
$apiHost = 'tiktok-video-no-watermark2.p.rapidapi.com';

// 4. دروستکرنا داخازییا cURL
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://{$apiHost}/",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => http_build_query(['url' => $tiktokUrl, 'hd' => 1]),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/x-www-form-urlencoded",
        "X-RapidAPI-Host: {$apiHost}",
        "X-RapidAPI-Key: {$apiKey}"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE); // وەرگرتنا HTTP Code
curl_close($curl);

// 5. ئەڤە گرنگترین بەشە بۆ دیارکرنا کێشەیێ
if ($err) {
    // ئەگەر cURL نەشیا پەیوەندیێ بکەت
    echo json_encode(['success' => false, 'message' => 'Kêşeya cURL: ' . $err]);
    exit;
}

// Em bersiva xav (raw response) çap دکەین دا ببینن ka API çi vedigerîne
// Heke bersiv ne JSON be, em ê li vir bibînin
if ($http_code != 200) {
    echo json_encode([
        'success' => false,
        'message' => "API bi xeta bersiv da (HTTP Code: {$http_code}).",
        'api_response' => $response // Bersiva xav ji API
    ]);
    exit;
}

$data = json_decode($response, true);

// ئەگەر وەرگێرانا JSON سەرنەکەفت
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'message' => 'Neşiya bersiva API were fehm kirin (JSON ne durist e).',
        'api_response_raw' => $response
    ]);
    exit;
}

// 6. ئەنجامێ dawî
if ($data && isset($data['code']) && $data['code'] === 0 && isset($data['data']['play'])) {
    echo json_encode([
        'success' => true,
        'downloadUrl' => $data['data']['play'],
        'title' => $data['data']['title'] ?? 'Sernav nehate dîtin',
        'cover' => $data['data']['cover'] ?? ''
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $data['message'] ?? 'Bersivek neçaverêkirî ji API hat.',
        'api_data' => $data // Em hemî datayên ji API hatine nîşan didin
    ]);
}
?>