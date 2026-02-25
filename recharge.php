<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$api_key = "6DBlPJy48l86NVLofTrSS8";

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    die(json_encode(["status" => "error", "message" => "No data received"]));
}

$phone = $data['phone']; 
$amount = (int)$data['amount']; // تحويل المبلغ لرقم صحيح للمقارنة

// 1. تنظيف وتجهيز الرقم (213...)
$phone = preg_replace('/[^0-9]/', '', $phone); 
if (strpos($phone, '0') === 0) {
    $phone = "213" . substr($phone, 1);
}

// 2. تحديد الـ SkuCode بناءً على الشبكة والمبلغ (مستخرج من ملفك Products.xlsx)
$prefix = substr($phone, 3, 2); 
$sku = "";

if ($prefix == "61" || $prefix == "62" || $prefix == "63" || $prefix == "64" || $prefix == "65" || $prefix == "66" || $prefix == "67" || $prefix == "69") {
    // Mobilis - موبيليس
    $sku = ($amount >= 500) ? "MDDZDZ36714" : "MDDZDZ89262"; 
} 
elseif ($prefix == "77" || $prefix == "78" || $prefix == "79") {
    // Djezzy - جيزي
    $sku = ($amount >= 500) ? "DJDZDZ45928" : "DJDZDZ71553";
} 
elseif ($prefix == "54" || $prefix == "55" || $prefix == "56") {
    // Ooredoo - أوريدو
    $sku = ($amount >= 500) ? "OODZDZ53377" : "OODZDZ59569";
}

if ($sku == "") {
    die(json_encode(["status" => "error", "message" => "Unknown Operator or Sku"]));
}

// 3. بناء الطلب بالمعايير الصحيحة لـ DingConnect
$url = "https://api.dingconnect.com/api/V1/SendTransfer";
$post_data = [
    "SkuCode" => $sku,
    "SendValue" => (float)$amount, 
    "AccountNumber" => (string)$phone,
    "DistributorRef" => uniqid("BitView_"), 
    "ValidateOnly" => false
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "api_key: $api_key",
    "Content-Type: application/json",
    "Accept: application/json"
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// طباعة الرد النهائي للتطبيق
echo $response;
?>
