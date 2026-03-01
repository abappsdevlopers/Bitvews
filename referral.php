<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// 1. Database Connection (Using mysqli as it's proven to work on your server)
$host = getenv('MYSQLHOST') ?: getenv('DATABASE_URL');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT') ?: "3306";

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "DB Connection failed"]));
}

// 2. Getting Data from Godot
// We read the raw input because Godot sends JSON in the body
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check both JSON input and standard POST for compatibility
$referrer_id = $data['referrer_id'] ?? $_POST['referrer_id'] ?? null;
$reward = (int)($data['reward_amount'] ?? $_POST['reward_amount'] ?? 0);

if ($referrer_id && $reward > 0) {
    // Clean the ID
    $referrer_id = $conn->real_escape_string(trim($referrer_id));

    // 3. Update Query
    $sql = "UPDATE users SET coins = coins + $reward WHERE user_id = '$referrer_id'";

    if ($conn->query($sql) === TRUE) {
        if ($conn->affected_rows > 0) {
            echo json_encode([
                "status" => "success", 
                "message" => "Referrer rewarded successfully"
            ]);
        } else {
            echo json_encode([
                "status" => "error", 
                "message" => "Referrer ID not found in database",
                "debug_id" => $referrer_id
            ]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
} else {
    // This is where your previous error was triggered
    echo json_encode([
        "status" => "error", 
        "message" => "No data received",
        "raw_received" => $json // This will help us see what Godot actually sent
    ]);
}

$conn->close();
?>
