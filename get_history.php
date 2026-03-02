<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);

$uid = $_GET['user_id'] ?? '';

if (!$uid) die(json_encode([]));

$sql = "SELECT amount, paypal_email, status, created_at FROM withdraws WHERE user_id = '$uid' ORDER BY created_at DESC";
$result = $conn->query($sql);

$history = [];
while($row = $result->fetch_assoc()) {
    $history[] = $row;
}

echo json_encode($history);
$conn->close();
?>
