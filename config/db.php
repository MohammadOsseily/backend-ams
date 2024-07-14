<?php
require __DIR__ . '/../vendor/autoload.php';


$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'flight_management_system';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
