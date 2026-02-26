<?php
// 1. منع أي أخطاء PHP من الظهور وإفساد الـ JSON
error_reporting(0);
ob_start();

// 2. إعدادات البيئة من ريلواي (تم تصحيح المسافات المخفية)
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

// 3. تنظيف أي مخرجات غريبة قبل إرسال الهيدر
ob_clean();
header('Content-Type: application/json; charset=utf-8');

// 4. الاتصال بقاعدة البيانات
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection Failed"]);
    exit;
}

// 5. استقبال البيانات من Godot
$input = json_decode(file_get_contents('php://input'), true);
$email = isset($input['email']) ? trim($input['email']) : '';

if (!empty($email)) {
    // جلب كلمة المرور بناءً على الإيميل
    $stmt = $conn->prepare("SELECT password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // إرسال البيانات (أضفت email_address لأن كود جودو يحتاجه لـ EmailJS)
        echo json_encode([
            "status" => "success", 
            "email_address" => $email,
            "password" => $row['password']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email not found"]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Email is empty"]);
}

$conn->close();
exit; // إنهاء السكربت لضمان عدم خروج أي شيء إضافي
?>
