<?php
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

echo "Host: " . ($host ? "✓" : "✗ مفقود") . "<br>";
echo "User: " . ($user ? "✓" : "✗ مفقود") . "<br>";
echo "Pass: " . ($pass ? "✓" : "✗ مفقود") . "<br>";
echo "DB: " . ($db ? "✓" : "✗ مفقود") . "<br>";
echo "Port: " . ($port ? "✓" : "✗ مفقود") . "<br>";

$conn = new mysqli($host, $user, $pass, $db, $port);
if ($conn->connect_error) {
    die("❌ خطأ في الاتصال: " . $conn->connect_error);
}
echo "✅ اتصال ناجح!";
$conn->close();
?>