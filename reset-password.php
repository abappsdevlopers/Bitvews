<?php
error_reporting(0);
ob_start();

// 1. إعدادات قاعدة البيانات من ريلواي
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

ob_clean();
header('Content-Type: application/json');

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection Failed"]));
}

// 2. استلام الإيميل من جودو
$input = file_get_contents('php://input');
$data = json_decode($input, true);
$email = isset($data['email']) ? trim($data['email']) : '';

if (empty($email)) {
    die(json_encode(["status" => "error", "message" => "Email required"]));
}

// 3. التحقق من وجود المستخدم في قاعدة بياناتك (MySQL)
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    die(json_encode(["status" => "error", "message" => "Email not found"]));
}

// 4. توليد كود عشوائي (مثلاً من 6 أرقام)
$reset_code = rand(100000, 999999);

// 5. حفظ الكود في قاعدة البيانات (تحتاج لإضافة عمود reset_code في جدول users)
$update = $conn->prepare("UPDATE users SET reset_code = ? WHERE email = ?");
$update->bind_param("is", $reset_code, $email);
$update->execute();

// 6. إرسال الإيميل (باستخدام دالة mail في PHP)
$to = $email;
$subject = "Your Password Reset Code";
$message = "Hello, your code to reset password is: " . $reset_code;
$headers = "From: support@your-app.com";

if (mail($to, $subject, $message, $headers)) {
    echo json_encode(["status" => "success", "message" => "success: code sent"]);
} else {
    // ملاحظة: أغلب السيرفرات السحابية تمنع دالة mail() العادية
    echo json_encode(["status" => "error", "message" => "Server cannot send email"]);
}

$conn->close();
?>
