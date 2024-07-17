<?php
require 'db.php';

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
        role VARCHAR(10) NOT NULL
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
        FOREIGN KEY (departure_airport_id) REFERENCES airports(id) ON DELETE CASCADE,
        FOREIGN KEY (arrival_airport_id) REFERENCES airports(id)ON DELETE CASCADE
    )",
    "CREATE TABLE bookings (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        flight_id INT NOT NULL,
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status VARCHAR(20) DEFAULT 'confirmed',
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (flight_id) REFERENCES flights(id) ON DELETE CASCADE
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
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (hotel_id) REFERENCES hotels(id) ON DELETE CASCADE
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
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (taxi_id) REFERENCES taxis(id) ON DELETE CASCADE
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

