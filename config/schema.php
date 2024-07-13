<?php
require 'vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];
$dbname = $_ENV['DB_NAME'];

// Create connection
$conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully\n";
} else {
    die("Error creating database: " . $conn->error . "\n");
}

// Select the database
$conn->select_db($dbname);

// Drop existing tables if they exist
$dropTables = [
    "DROP TABLE IF EXISTS trip_suggestions",
    "DROP TABLE IF EXISTS taxi_bookings",
    "DROP TABLE IF EXISTS taxis",
    "DROP TABLE IF EXISTS hotel_bookings",
    "DROP TABLE IF EXISTS hotels",
    "DROP TABLE IF EXISTS bookings",
    "DROP TABLE IF EXISTS flights",
    "DROP TABLE IF EXISTS airports",
    "DROP TABLE IF EXISTS users"
];

foreach ($dropTables as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table dropped successfully\n";
    } else {
        echo "Error dropping table: " . $conn->error . "\n";
    }
}

// Create tables
$tables = [
    "CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        role VARCHAR(10) NOT NULL,
        
    )",
    "CREATE TABLE airports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        location VARCHAR(100) NOT NULL
    )",
    "CREATE TABLE flights (
        id INT AUTO_INCREMENT PRIMARY KEY,
        flight_number VARCHAR(10) NOT NULL UNIQUE,
        departure_airport_id INT NOT NULL,
        arrival_airport_id INT NOT NULL,
        departure_time DATETIME NOT NULL,
        arrival_time DATETIME NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        capacity INT NOT NULL,
        FOREIGN KEY (departure_airport_id) REFERENCES airports(id),
        FOREIGN KEY (arrival_airport_id) REFERENCES airports(id)
    )",
    "CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        flight_id INT NOT NULL,
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(20) DEFAULT 'confirmed',
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (flight_id) REFERENCES flights(id)
    )",
    "CREATE TABLE hotels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        address VARCHAR(255) NOT NULL,
        available_rooms INT NOT NULL,
        price_per_night DECIMAL(10, 2) NOT NULL
    )",
    "CREATE TABLE hotel_bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        hotel_id INT NOT NULL,
        check_in_date DATE NOT NULL,
        check_out_date DATE NOT NULL,
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(20) DEFAULT 'confirmed',
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (hotel_id) REFERENCES hotels(id)
    )",
    "CREATE TABLE taxis (
        id INT AUTO_INCREMENT PRIMARY KEY,
        company_name VARCHAR(100) NOT NULL,
        city VARCHAR(100) NOT NULL,
        phone_number VARCHAR(20) NOT NULL,
        price_per_km DECIMAL(10, 2) NOT NULL
    )",
    "CREATE TABLE taxi_bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        taxi_id INT NOT NULL,
        pick_up_location VARCHAR(255) NOT NULL,
        drop_off_location VARCHAR(255) NOT NULL,
        pick_up_time DATETIME NOT NULL,
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(20) DEFAULT 'confirmed',
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (taxi_id) REFERENCES taxis(id)
    )",
    "CREATE TABLE trip_suggestions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        destination VARCHAR(100) NOT NULL,
        preferences TEXT NOT NULL,
        suggestions TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id)
    )"
];

foreach ($tables as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table created successfully\n";
    } else {
        echo "Error creating table: " . $conn->error . "\n";
    }
}

// Close connection
$conn->close();
