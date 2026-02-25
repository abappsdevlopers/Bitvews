<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$api_key = "6DBlPJy48l86NVLofTrSS8";

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    die(json_encode(["status" => "error", "message" => "No data received"]));
}

$phone = preg_replace('/[^0-9]/', '', $data['phone']); 
if (strpos($phone, '0') === 0) { $phone = "213" . substr($phone, 1); }

$amount_from_godot = (float)$data['amount'];
$prefix = substr($phone, 3, 2); 
$sku = "";
$send_value = 0.0;

// مطابقة دقيقة بناءً على جدول أسعارك في ملف Excel
if (in_array($prefix, ["61","62","63","64","65","66","67","69"])) { // Mobilis
    if ($amount_from_godot < 2.0) { $sku = "MDDZDZ89262"; $send_value = 1.03; }
    elseif ($amount_from_godot < 4.0) { $sku = "MDDZDZ66459"; $send_value = 2.05; }
    else { $sku = "MDDZDZ36714"; $send_value = 5.13; }
} 
elseif (in_array($prefix, ["77","78","79"])) { // Djezzy
    if ($amount_from_godot < 2.0) { $sku = "DJDZDZ71553"; $send_value = 1.03; }
    elseif ($amount_from_godot < 4.0) { $sku = "DJDZDZ44375"; $send_value = 2.05; }
    else { $sku = "DJDZDZ45928"; $send_value = 5.13; }
} 
elseif (in_array($prefix, ["54","55","56"])) { // Ooredoo
    if ($amount_from_godot < 2.0) { $sku = "OODZDZ59569"; $send_value = 1.03; }
    elseif ($amount_from_godot < 4.0) { $sku = "OODZDZ10118"; $send_value = 2.05; }
    else { $sku = "OODZDZ53377"; $send_value = 5.13; }
}

$post_data = [
    "SkuCode" => $sku,
    "SendValue" => $send_value, // إرسال القيمة الثابتة يحل خطأ ParameterOutOfRange
    "AccountNumber" => (string)$phone,
    "DistributorRef" => uniqid("BitV_"), 
    "ValidateOnly" => false
];

$ch = curl_init("https://api.dingconnect.com/api/V1/SendTransfer");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["api_key: $api_key", "Content-Type: application/json", "Accept: application/json"]);
$response = curl_exec($ch);
curl_close($ch);

echo $response;
?>
