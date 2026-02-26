<?php
// 1. استدعاء متغيرات البيئة من Railway
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

// مفتاح Firebase (يفضل أيضاً وضعه في Variables بـ Railway باسم FIREBASE_KEY)
$apiKey = getenv('FIREBASE_KEY'); 

header('Content-Type: application/json');

// 2. الاتصال بقاعدة البيانات للتأكد من وجود البريد الإلكتروني
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database Connection Failed"]);
    exit;
}

// 3. استلام البيانات من Godot
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$email = $data['email'] ?? '';

if (empty($email)) {
    echo json_encode(["status" => "error", "message" => "Email is required"]);
    exit;
}

// 4. التحقق من وجود الإيميل في جدول المستخدمين (مثلاً جدول اسمه users)
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

// 5. إذا وُجد الإيميل، نطلب من Firebase إرسال رابط إعادة التعيين
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

if ($httpCode === 200) {
    echo json_encode(["status" => "success", "message" => "success: email sent"]);
} else {
    echo json_encode(["status" => "error", "message" => "Firebase Error"]);
}

$stmt->close();
$conn->close();
?>
