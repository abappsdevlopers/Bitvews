<?php
header("Content-Type: application/json");

// مفتاح API الخاص بك
$api_key = "6DBlPJy48l86NVLofTrSS8";

/**
 * أضفنا &productTypes=0 لجلب منتجات الشحن (Top-up) 
 * لأن القيمة 0 في Ding تعني شحن الرصيد العادي
 */
$url = "https://api.dingconnect.com/api/V1/GetProducts?countryCodes=DZ&productTypes=0";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "api_key: $api_key",
    "Accept: application/json"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http_code !== 200) {
    echo json_encode(["error" => "Failed to fetch data", "http_code" => $http_code]);
    exit;
}

// سنقوم بتحليل النتيجة لكي نعطيك الأكواد بشكل مباشر ومرتب
$data = json_decode($response, true);
$result = [];

if (isset($data['Items'])) {
    foreach ($data['Items'] as $item) {
        $result[] = [
            "Provider" => $item['ProviderCode'],
            "SkuCode" => $item['SkuCode'],
            "Description" => $item['DefaultDisplayText'],
            "Min_Amount" => $item['Minimum']['SendValue'],
            "Max_Amount" => $item['Maximum']['SendValue'],
            "Currency" => $item['Minimum']['SendCurrencyIso']
        ];
    }
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>
