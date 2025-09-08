<?php
$host = "localhost";
$user = "root";
$pass = ""; // default for XAMPP/WAMP; change if you set password
$db   = "attendance_db";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
