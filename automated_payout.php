<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// --- 1. إعدادات بايبال (ضع بياناتك هنا) ---
$PAYPAL_CLIENT_ID = 'AWThXWiB-oAtl9d3kesHixlkgh5Xk-euf08T2eybLEJskDXQSBaJilrS8J434sa-6qBJngrOWoqF-6ns';
$PAYPAL_SECRET    = 'EEHcG7kFgSu86ZUaVOD3JQ10wS2UADbqbUU7OHwKXOZ8KRgPJzSibb7bFF80k9SSpyaKtr3IQNl9DfLj';
$PAYPAL_URL       = "https://api-m.sandbox.paypal.com"; // للتيست استخدم: https://api-m.sandbox.paypal.com

// --- 2. إعدادات الحماية والتحويل ---
$MIN_POINTS       = 50000;      // الحد الأدنى للسحب (5000 نقطة)
$DAILY_LIMIT_USD  = 50.0;      // الحد الأقصى للسحب اليومي (50 دولار)
$POINTS_TO_USD    = 0.001;     // كل 1000 نقطة = 1 دولار

// --- 3. الاتصال بقاعدة البيانات (Railway) ---
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die(json_encode(["error" => "Database connection failed"]));
}

// --- 4. استلام ومعالجة البيانات ---
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['user_id']) || !isset($data['paypal_email']) || !isset($data['amount'])) {
    http_response_code(400);
    die(json_encode(["error" => "Missing parameters"]));
}

$uid = $conn->real_escape_string($data['user_id']);
$receiver_email = $conn->real_escape_string($data['paypal_email']);
$requested_points = (int)$data['amount'];

// أ- التحقق من الرصيد في القاعدة
$userQuery = $conn->query("SELECT coins FROM users WHERE user_id = '$uid'");
$userData = $userQuery->fetch_assoc();

if (!$userData || $userData['coins'] < $requested_points || $requested_points < $MIN_POINTS) {
    http_response_code(400);
    die(json_encode(["status" => "error", "message" => "ﻲﻓﺎﻛ ﺮﻴﻏ ﺪﻴﺻﺮﻟﺍ"])); // رصيد غير كافي
}

// ب- التحقق من الحد اليومي
$today = date('Y-m-d');
$limitCheck = $conn->query("SELECT SUM(amount) as total FROM withdraws WHERE user_id = '$uid' AND DATE(created_at) = '$today'");
$limitData = $limitCheck->fetch_assoc();
$current_spent = $limitData['total'] ?? 0;
$payout_amount_usd = $requested_points * $POINTS_TO_USD;

if (($current_spent + $payout_amount_usd) > $DAILY_LIMIT_USD) {
    http_response_code(400);
    die(json_encode(["status" => "error", "message" => "ﻲﻣﻮﻴﻟﺍ ﺪﺤﻟﺍ ﺯﻭﺎﺠﺗ"])); // تجاوز الحد اليومي
}

// --- 5. الحصول على PayPal Access Token ---
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $PAYPAL_URL . "/v1/oauth2/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, $PAYPAL_CLIENT_ID . ":" . $PAYPAL_SECRET);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
$token_result = curl_exec($ch);
$token_data = json_decode($token_result);

if (!isset($token_data->access_token)) {
    http_response_code(500);
    die(json_encode(["error" => "PayPal Auth Failed"]));
}
$access_token = $token_data->access_token;


$payout_data = [
    "sender_batch_header" => [
        "sender_batch_id" => uniqid("BitView_"),
        "email_subject" => "You have a payout!",
        "email_message" => "Thanks for using BitView. You received your reward!"
    ],
    "items" => [[
        "recipient_type" => "EMAIL",
        "amount" => [
            "value" => number_format($payout_amount_usd, 2, '.', ''),
            "currency" => "USD"
        ],
        "receiver" => $receiver_email
    ]]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $PAYPAL_URL . "/v1/payments/payouts");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . $access_token
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payout_data));
$payout_result = curl_exec($ch);
$payout_res_data = json_decode($payout_result);


if (isset($payout_res_data->batch_header)) {
    
    $conn->query("UPDATE users SET coins = coins - $requested_points WHERE user_id = '$uid'");
    $conn->query("INSERT INTO withdraws (user_id, paypal_email, amount, status) VALUES ('$uid', '$receiver_email', $payout_amount_usd, 'COMPLETED')");
    
    echo json_encode(["status" => "success", "message" => "ﺡﺎﺠﻨﺑ ﻝﺎﺳﺭﻹﺍ ﻢﺗ"]);
} else {
    // فشل من جهة بايبال
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "ﻝﺎﺳﺭﻹﺍ ﻲﻓ ﻞﺸﻓ"]);
}

curl_close($ch);
$conn->close();
?>
