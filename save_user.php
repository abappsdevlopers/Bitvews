<?php
header("Access-Control-Allow-Origin: *"); // يسمح لـ Godot بالاتصال من أي مكان
header("Content-Type: application/json");

// Railway يعطي بيانات الاتصال عبر متغيرات البيئة (Environment Variables)
$host = getenv('MYSQLHOST') ?: "mysql.railway.internal";
$user = getenv('MYSQLUSER') ?: "root";
$pass = getenv('MYSQLPASSWORD') ?: "ECKBIDYVEBuQjPslIPgwwGWFsyUUoPao";
$db   = getenv('MYSQLDATABASE') ?: "railway";
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed"]));
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data) {
    $uid = $conn->real_escape_string($data['user_id']);
    $uname = $conn->real_escape_string($data['user_name']);
    $email = $conn->real_escape_string($data['email']);
    $upass = $conn->real_escape_string($data['pass']);
    $coins = (int)$data['coins'];
    $verified = $data['is_verified'] ? 1 : 0;

    $sql = "INSERT INTO users (user_id, user_name, email, pass, coins, is_verified) 
            VALUES ('$uid', '$uname', '$email', '$upass', $coins, $verified) 
            ON DUPLICATE KEY UPDATE 
            user_name='$uname', pass='$upass', coins=$coins, is_verified=$verified";

    if ($conn->query($sql) === TRUE) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}
$conn->close();
?>
