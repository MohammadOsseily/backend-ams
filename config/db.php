<?php
require __DIR__ . '/../vendor/autoload.php';


<<<<<<< HEAD
// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "flight_management_system";
=======
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'flight_management_system';
>>>>>>> d5c45b36545aafee4de0de8eb5b20bcfaed336ec

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

