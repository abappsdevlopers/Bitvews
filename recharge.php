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
$amount_from_godot = $data['amount']; // هذه القيمة قادمة من جودو بالدولار الآن

// 1. تنظيف الرقم
$phone = preg_replace('/[^0-9]/', '', $phone); 
if (strpos($phone, '0') === 0) {
    $phone = "213" . substr($phone, 1);
}

$prefix = substr($phone, 3, 2); 
$sku = "";
$send_value = 0.0;

// 2. مطابقة الـ Sku مع السعر الدقيق بالدولار من ملف Excel الخاص بك
if ($prefix == "61" || $prefix == "62" || $prefix == "63" || $prefix == "64" || $prefix == "65" || $prefix == "66" || $prefix == "67" || $prefix == "69") {
    // Mobilis
    if ($amount_from_godot < 2.0) { $sku = "MDDZDZ89262"; $send_value = 1.03; } // 100 DZD
    elseif ($amount_from_godot < 4.0) { $sku = "MDDZDZ66459"; $send_value = 2.05; } // 200 DZD
    else { $sku = "MDDZDZ36714"; $send_value = 5.13; } // 500 DZD
} 
elseif ($prefix == "77" || $prefix == "78" || $prefix == "79") {
    // Djezzy
    if ($amount_from_godot < 2.0) { $sku = "DJDZDZ71553"; $send_value = 1.03; } // 100 DZD
    elseif ($amount_from_godot < 4.0) { $sku = "DJDZDZ44375"; $send_value = 2.05; } // 200 DZD
    else { $sku = "DJDZDZ45928"; $send_value = 5.13; } // 500 DZD
} 
elseif ($prefix == "54" || $prefix == "55" || $prefix == "56") {
    // Ooredoo
    if ($amount_from_godot < 2.0) { $sku = "OODZDZ59569"; $send_value = 1.03; } // 100 DZD
    elseif ($amount_from_godot < 4.0) { $sku = "OODZDZ10118"; $send_value = 2.05; } // 200 DZD
    else { $sku = "OODZDZ53377"; $send_value = 5.13; } // 500 DZD
}

if ($sku == "") {
    die(json_encode(["status" => "error", "message" => "Operator not supported"]));
}

// 3. إرسال الطلب إلى DingConnect
$url = "https://api.dingconnect.com/api/V1/SendTransfer";
$post_data = [
    "SkuCode" => $sku,
    "SendValue" => (float)$send_value, // إرسال القيمة الدقيقة بالدولار
    "AccountNumber" => (string)$phone,
    "DistributorRef" => uniqid("BitV_"), 
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
curl_close($ch);

echo $response;
?>
