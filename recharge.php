<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// بيانات Ding Connect (يفضل وضعها في Variables في Railway)
$api_key = "6DBlPJy48l86NVLofTrSS8";

// استقبال البيانات من Godot
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    $phone = $data['phone'];      // رقم الهاتف (مثال: 0661234567)
    $amount = $data['amount'];    // المبلغ
    $country = "DZ";              // رمز الدولة (الجزائر)

    // إعداد طلب الشحن لـ Ding Connect
    $ch = curl_init("https://api.dingconnect.com/api/V1/SendTransfer");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "SkuCode" => "NAT_DZ_TOPUP", // مثال لكود الخدمة (يجب التأكد منه من Ding)
        "SendAmount" => $amount,
        "PhoneNumber" => $phone,
        "DistributorRef" => uniqid() // رقم مرجع فريد للعملية
    ]));
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "api_key: $api_key",
        "Content-Type: application/json"
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        echo json_encode(["status" => "success", "data" => json_decode($response)]);
    } else {
        http_response_code($httpCode);
        echo json_encode(["status" => "error", "message" => "Ding API Error: " . $response]);
    }
}
?>
