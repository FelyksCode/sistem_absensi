<?php
session_start();
require_once("database.php");
require_once("check_user_auth.php");


// Check if the user has already clocked in
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM attendance WHERE user_id = :user_id AND type = 'in' AND DATE(created_at) = CURDATE()";
$stmt = $handle->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->execute();

$result = $stmt->fetch(PDO::FETCH_ASSOC);
if ($result) {
    // User has already clocked in today
    $_SESSION['error'] = "Anda sudah melakukan absen masuk hari ini.";
    header('Location: ../index.php');
    exit();
}

// Get the user's location from the request (assuming you have longitude and latitude in the request)
$longitude = $_POST['longitude'] ?? null; // Replace with actual method to get longitude
$latitude = $_POST['latitude'] ?? null; // Replace with actual method to get latitude

// Insert attendance record for clock in
$query = "INSERT INTO attendance (user_id, longitude, latitude, type, created_at) VALUES (:user_id, :longitude, :latitude, 'in', NOW())";
$stmt = $handle->prepare($query);
$stmt->bindParam(":user_id", $user_id);
$stmt->bindParam(":longitude", $longitude);
$stmt->bindParam(":latitude", $latitude);

if ($stmt->execute()) {
    // Successfully clocked in
    $_SESSION['success'] = "Absen masuk berhasil.";
} else {
    // Error occurred
    $_SESSION['error'] = "Gagal melakukan absen masuk. Silakan coba lagi.";
}

header('Location: ../index.php');
exit();
