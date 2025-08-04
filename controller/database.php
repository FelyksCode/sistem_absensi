<?php

// Database connection settings
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'sistem_absensi';

// Create a connection
try {
    $handle = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
