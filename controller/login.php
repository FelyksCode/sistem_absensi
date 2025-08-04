<?php
session_start();
require_once('database.php');

if (!isset($_POST['username'], $_POST['password'])) {
    header("Location: ../login.php?error=Please fill in both fields");
    exit();
}

$username = $_POST['username'];
$password = $_POST['password'];
// Prepare and execute the SQL statement

$stmt = $handle->prepare("SELECT id, username, password, name FROM users WHERE username = :username");
$stmt->bindParam(':username', $username);
$stmt->execute();

$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user && password_verify($password, $user['password'])) {
    // user found & password correct
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['name'] = $user['name'];
    echo "Login successful!";
    header("Location: ../index.php");
    exit();
} else {
    header("Location: ../login.php?error=Invalid credentials");
    exit();
}
