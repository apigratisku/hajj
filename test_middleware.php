<?php
header("Content-Type: application/json");
echo json_encode([
    "status" => "success",
    "message" => "Test file accessible",
    "timestamp" => date("Y-m-d H:i:s"),
    "server" => isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : 'unknown'
]);
?>
