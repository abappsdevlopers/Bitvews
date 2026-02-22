<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// بيانات Ding Connect الخاصة بك
$api_key = "6DBlPJy48l86NVLofTrSS8";

// استقبال البيانات من تطبيق Godot
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    die(json_encode(["status" => "error", "message" => "No data received"]));
}

$phone = $data['phone']; // مثال: 0661223344
$amount = $data['amount'];

// 1. تنظيف وتحويل الرقم للصيغة الدولية (213)
$phone = preg_replace('/[^0-9]/', '', $phone); // إزالة أي رموز غير الأرقام
if (strpos($phone, '0') === 0) {
    $phone = "213" . substr($phone, 1);
} elseif (strpos($phone, '213') !== 0) {
    $phone = "213" . $phone;
}

// 2. التعرف على الشركة والـ Provider ID و SkuCode
$sku = "";
$provider_id = 0;
$prefix = substr($phone, 3, 2); // يأخذ الرقمين بعد 213

if ($prefix == "61" || $prefix == "62" || $prefix == "63" || $prefix == "64" || $prefix == "65" || $prefix == "66" || $prefix == "67" || $prefix == "69") {
    $sku = "NAT_DZ_MOBILIS_OPEN"; // الكود المفتوح للمبالغ المتغيرة
    $provider_id = 420; 
} elseif ($prefix == "77" || $prefix == "78" || $prefix == "79") {
    $sku = "NAT_DZ_DJEZZY_OPEN";
    $provider_id = 421;
} elseif ($prefix == "54" || $prefix == "55" || $prefix == "56") {
    $sku = "NAT_DZ_OOREDOO_OPEN";
    $provider_id = 422;
}

if ($sku == "") {
    die(json_encode(["status" => "error", "message" => "Unknown Operator Prefix: " . $prefix]));
}

// 3. بناء طلب الـ API لـ Ding Connect
$url = "https://api.dingconnect.com/api/V1/SendTransfer";
$post_data = [
    "SkuCode" => $sku,
    "SendAmount" => $amount,
    "PhoneNumber" => $phone,
    "DistributorRef" => uniqid("godot_"), // مرجع فريد لكل عملية
    "ValidateOnly" => true // اجعلها true إذا كنت تريد التجربة بدون خصم رصيد حقيقي
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

// 4. إرسال الرد إلى Godot
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
