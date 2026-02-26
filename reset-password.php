<?php
header('Content-Type: application/json');

// إعدادات البيئة من ريلواي
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection Failed"]));
}

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
        // إرسال كلمة المرور لجودو
        echo json_encode([
            "status" => "success", 
            "password" => $row['password']
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Email not found"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Email is empty"]);
}

$conn->close();
?>
