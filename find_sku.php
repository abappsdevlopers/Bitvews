<?php
header("Content-Type: application/json");
$api_key = "6DBlPJy48l86NVLofTrSS8";

// طلب قائمة المنتجات المتوفرة لدولة الجزائر (DZ)
$url = "https://api.dingconnect.com/api/V1/GetProducts?countryCodes=DZ";

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "api_key: $api_key",
    "Accept: application/json"
]);

$response = curl_exec($ch);
curl_close($ch);

echo $response; // هذا سيطبع لك قائمة بكل الشركات والأكواد الخاصة بها
?>
