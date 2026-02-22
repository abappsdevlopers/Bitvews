<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// مفتاح الـ API الخاص بك الذي قدمته
$api_key = "6DBlPJy48l86NVLofTrSS8";

// استقبال البيانات من تطبيق Godot
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    die(json_encode(["status" => "error", "message" => "No data received"]));
}

$phone = $data['phone']; 
$amount = $data['amount'];

// 1. تنظيف الرقم وتحويله للصيغة الدولية (213)
$phone = preg_replace('/[^0-9]/', '', $phone); 
if (strpos($phone, '0') === 0) {
    $phone = "213" . substr($phone, 1);
} elseif (strpos($phone, '213') !== 0) {
    $phone = "213" . $phone;
}

// 2. تحديد الشبكة بناءً على أول رقمين بعد رمز الدولة
// الرقم الدولي يكون 2137... أو 2136... أو 2135...
$prefix = substr($phone, 3, 2); 

$sku = "";
$provider_id = 0;

if ($prefix == "61" || $prefix == "62" || $prefix == "63" || $prefix == "64" || $prefix == "65" || $prefix == "66" || $prefix == "67" || $prefix == "69") {
    // موبيليس
    $sku = "NAT_DZ_MOBILIS"; 
    $provider_id = 420; 
} elseif ($prefix == "77" || $prefix == "78" || $prefix == "79") {
    // جيزي
    $sku = "NAT_DZ_DJEZZY";
    $provider_id = 421;
} elseif ($prefix == "54" || $prefix == "55" || $prefix == "56") {
    // أوريدو
    $sku = "NAT_DZ_OOREDOO";
    $provider_id = 422;
}

// التحقق إذا لم يتم التعرف على الرقم
if ($sku == "") {
    die(json_encode(["status" => "error", "message" => "Unknown Operator: Prefix " . $prefix]));
}

// 3. بناء طلب الـ API لـ Ding Connect
$url = "https://api.dingconnect.com/api/V1/SendTransfer";
$post_data = [
    "SkuCode" => $sku,
    "SendAmount" => $amount,
    "PhoneNumber" => $phone,
    "DistributorRef" => uniqid("godot_"), 
    "ValidateOnly" => true // اجعلها true للتجربة بدون خصم رصيد
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

// 4. إرجاع النتيجة لتطبيق Godot
if ($http_code == 200) {
    echo $response;
} else {
    http_response_code($http_code);
    echo json_encode([
        "status" => "error", 
        "http_code" => $http_code,
        "details" => json_decode($response)
    ]);
}
?>
