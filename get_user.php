<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

$conn = new mysqli($host, $user, $pass, $db, $port);

// استلام الإيميل والباسورد من الرابط (GET)
$email = $_GET['email'] ?? '';
$password = $_GET['pass'] ?? '';

$sql = "SELECT * FROM users WHERE email = '$email' AND pass = '$password'";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    // تحويل البيانات لأنواعها الأصلية
    $row['coins'] = (int)$row['coins'];
    $row['is_verified'] = (bool)$row['is_verified'];
    echo json_encode($row);
} else {
    http_response_code(404);
    echo json_encode(["error" => "User not found"]);
}
$conn->close();
