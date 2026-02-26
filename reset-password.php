<?php
// منع خروج أي تحذيرات تفسد الـ JSON
error_reporting(0);
ob_start();

// 1. استدعاء متغيرات البيئة من Railway
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE'); // تم تصحيح الرموز هنا
$port = getenv('MYSQLPORT');
$apiKey = getenv('FIREBASE_KEY'); 

// تنظيف أي مخرجات سابقة لضمان JSON نظيف
ob_clean();
header('Content-Type: application/json');

// 2. الاتصال بقاعدة البيانات
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Database Connection Failed"]));
}

// 3. استلام البيانات من Godot
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$email = isset($data['email']) ? trim($data['email']) : '';

if (empty($email)) {
    die(json_encode(["status" => "error", "message" => "Email is required"]));
}

// 4. التحقق من وجود الإيميل
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Email not found in our records"]);
    $stmt->close();
    $conn->close();
    exit;
}

// 5. طلب إعادة التعيين من Firebase
$url = "https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key=" . $apiKey;
$payload = json_encode(["requestType" => "PASSWORD_RESET", "email" => $email]);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 6. الرد النهائي
if ($httpCode === 200) {
    echo json_encode(["status" => "success", "message" => "success: email sent"]);
} else {
    // إظهار تفاصيل الخطأ القادم من فيسبوك للمساعدة في الديباجينج
    $resData = json_decode($response, true);
    $msg = isset($resData['error']['message']) ? $resData['error']['message'] : "Firebase Error";
    echo json_encode(["status" => "error", "message" => $msg]);
}

$stmt->close();
$conn->close();
?>
