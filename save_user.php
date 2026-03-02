<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// جلب البيانات تلقائياً من Railway
$host = getenv('MYSQLHOST') ?: getenv('DATABASE_URL');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

// محاولة الاتصال باستخدام mysqli
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// --- (تعديل: إنشاء الجدول أو تحديثه لإضافة الأعمدة الناقصة) ---
$createTable = "CREATE TABLE IF NOT EXISTS users (
    user_id VARCHAR(100) PRIMARY KEY,
    user_name VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    pass VARCHAR(100),
    coins INT DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE
)";
$conn->query($createTable);

// فحص يدوي لعمود impressions (لأن الجدول موجود مسبقاً)
$checkImp = $conn->query("SHOW COLUMNS FROM users LIKE 'impressions'");
if ($checkImp->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN impressions INT DEFAULT 0 AFTER coins");
}
// ------------------------------------------------------------------

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    $uid = $conn->real_escape_string($data['user_id']);
    $uname = $conn->real_escape_string($data['user_name']);
    $email = $conn->real_escape_string($data['email']);
    $upass = $conn->real_escape_string($data['pass']);
    $coins = (int)$data['coins'];
    $impressions = (int)($data['impressions'] ?? 0); 
    $verified = isset($data['is_verified']) && $data['is_verified'] ? 1 : 0;

    // --- الخطوة الجديدة: التحقق من وجود الإيميل لمستخدم آخر ---
    $checkEmail = $conn->query("SELECT user_id FROM users WHERE email = '$email' AND user_id != '$uid'");
    
    if ($checkEmail && $checkEmail->num_rows > 0) {
        // الإيميل موجود مسبقاً لمستخدم بـ ID مختلف
        http_response_code(400);
        echo json_encode([
            "status" => "error", 
            "message" => "ﻞﻌﻔﻟﺎﺑ ﻡﺪﺨﺘﺴﻣ ﻞﻴﻤﻴﺠﻟﺍ ﺍﺬﻫ" // "هذا الجيمايل مستخدم بالفعل"
        ]);
    } else {
        // إذا كان الإيميل جديداً أو يخص نفس المستخدم الحالي، نقوم بالإدخال أو التحديث
        $sql = "INSERT INTO users (user_id, user_name, email, pass, coins, impressions, is_verified) 
                VALUES ('$uid', '$uname', '$email', '$upass', $coins, $impressions, $verified) 
                ON DUPLICATE KEY UPDATE 
                user_name='$uname', coins=$coins, impressions=$impressions, is_verified=$verified";

        if ($conn->query($sql) === TRUE) {
            echo json_encode(["status" => "success"]);
        } else {
            http_response_code(400);
            echo json_encode(["error" => $conn->error]);
        }
    }
} else {
    echo json_encode(["error" => "No data received"]);
}

$conn->close();
?>
