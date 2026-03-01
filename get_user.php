<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// جلب بيانات الاتصال من Railway
$host = getenv('MYSQLHOST') ?: getenv('DATABASE_URL');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed"]));
}

// --- التأكد من وجود عمود impressions قبل جلب البيانات ---
// هذه الخطوة تضمن عدم حدوث خطأ SQL إذا كان الجدول قديماً
$checkImp = $conn->query("SHOW COLUMNS FROM users LIKE 'impressions'");
if ($checkImp->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN impressions INT DEFAULT 0 AFTER coins");
}
// -------------------------------------------------------

// استلام الإيميل والباسورد من الرابط (GET) وتأمينهم
$email = $conn->real_escape_string($_GET['email'] ?? '');
$password = $conn->real_escape_string($_GET['pass'] ?? '');

if (empty($email) || empty($password)) {
    http_response_code(400);
    die(json_encode(["error" => "Email and password are required"]));
}

$sql = "SELECT * FROM users WHERE email = '$email' AND pass = '$password'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // تحويل البيانات لأنواعها الأصلية لسهولة التعامل معها في Godot
    $row['coins'] = (int)$row['coins'];
    $row['impressions'] = (int)($row['impressions'] ?? 0);
    $row['is_verified'] = (bool)$row['is_verified'];
    
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(["error" => "User not found"]);
}

$conn->close();
?>
