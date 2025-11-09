<?php
session_start();
include 'db.php';

if(isset($_POST['credits']) && isset($_SESSION['username'])){
    $credits = intval($_POST['credits']);
    $username = $_SESSION['username'];

    // Update session
    $_SESSION['credits'] = $credits;

    // Update database
    $stmt = $conn->prepare("UPDATE users SET credits = ? WHERE username = ?");
    $stmt->bind_param("is", $credits, $username);
    $stmt->execute();
    $stmt->close();

    echo 'OK';
}else{
    echo 'No credits sent or user not logged in';
}
$conn->close();
?>
