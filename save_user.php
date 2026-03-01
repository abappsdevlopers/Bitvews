<?php
header("Access-Control-Allow-Origin: *"); // يسمح لـ Godot بالاتصال من أي مكان
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$host = getenv('MYSQLHOST');
// جلب البيانات تلقائياً من Railway
$host = getenv('MYSQLHOST') ?: getenv('DATABASE_URL');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');
$port = getenv('MYSQLPORT') ?: "3306";

// الاتصال مع تحديد المنفذ (مهم جداً في Railway)
// محاولة الاتصال
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    // هذا سيطبع الخطأ الحقيقي في سجلات Railway (Logs)
    error_log("Connection failed: " . $conn->connect_error);
    die(json_encode(["error" => "Database connection failed"]));
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed: " . $conn->connect_error]));
}

// --- (هذا الجزء سيقوم بإنشاء الجدول تلقائياً إذا لم يكن موجوداً) ---
$createTable = "CREATE TABLE IF NOT EXISTS users (
    user_id VARCHAR(100) PRIMARY KEY,
    user_name VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    pass VARCHAR(100),
    coins INT DEFAULT 0,
    is_verified BOOLEAN DEFAULT FALSE
)";
$conn->query($createTable);
// ------------------------------------------------------------------

$json = file_get_contents('php://input');
$data = json_decode($json, true);

@@ -31,13 +43,13 @@
    $sql = "INSERT INTO users (user_id, user_name, email, pass, coins, is_verified) 
            VALUES ('$uid', '$uname', '$email', '$upass', $coins, $verified) 
            ON DUPLICATE KEY UPDATE 
            user_name='$uname', pass='$upass', coins=$coins, is_verified=$verified";
            user_name='$uname', coins=$coins, is_verified=$verified";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
        http_response_code(400);
        echo json_encode(["error" => $conn->error]);
    }
}
$conn->close();
?>
