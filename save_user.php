<?php
include 'db.php';

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$phone = $_POST['phone'];

$sql = "INSERT INTO users (username, password, phone) VALUES ('$username', '$password', '$phone')";

if ($conn->query($sql) === TRUE) {
  echo "<script>alert('Registration successful! You can now login.');window.location.href='login.html';</script>";
} else {
  echo "<script>alert('Username already exists or error saving data.');window.location.href='register.html';</script>";
}
$conn->close();
?>
