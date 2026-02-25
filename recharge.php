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
$amount = $data['amount'];

// 1. تنظيف الرقم (يجب أن يكون 213 ثم الرقم بدون + وبدون أصفار)
$phone = preg_replace('/[^0-9]/', '', $phone); 
if (strpos($phone, '0') === 0) {
    $phone = "213" . substr($phone, 1);
}

// 2. تحديد الشبكة
$prefix = substr($phone, 3, 2); 
$sku = "";
// --- تصحيح الأكواد بناءً على رد السيرفر الأخير ---
// --- الأكواد المحدثة لعام 2026 لضمان القبول في Ding Connect ---
if ($prefix == "61" || $prefix == "62" || $prefix == "63" || $prefix == "64" || $prefix == "65" || $prefix == "66" || $prefix == "67" || $prefix == "69") {
    $sku = "DZ_MOB_NAT";  // Mobilis
} elseif ($prefix == "77" || $prefix == "78" || $prefix == "79") {
    $sku = "DZ_OT_NAT";   // Djezzy
} elseif ($prefix == "54" || $prefix == "55" || $prefix == "56") {
    $sku = "DZ_WT_NAT";   // Ooredoo
}

if ($sku == "") {
    die(json_encode(["status" => "error", "message" => "Unknown Operator"]));
}

// 3. بناء الطلب باستخدام المفاتيح التي طلبها الخطأ (SendValue)
$url = "https://api.dingconnect.com/api/V1/SendTransfer";
$post_data = [
    "SkuCode" => $sku,
    "SendValue" => (float)$amount, // تم التغيير من SendAmount إلى SendValue
    "AccountNumber" => (string)$phone, // تم التغيير من PhoneNumber إلى AccountNumber
    "DistributorRef" => uniqid("godot_"), 
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

echo $response;
?>
