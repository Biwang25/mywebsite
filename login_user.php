<?php
session_start();
include 'db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username='$username'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  $row = $result->fetch_assoc();
  if (password_verify($password, $row['password'])) {
    $_SESSION['username'] = $row['username'];
    $_SESSION['credits'] = $row['credits'];
    header("Location: dashboard.php");
    exit;
  } else {
    echo "<script>alert('Invalid password.');window.location.href='login.html';</script>";
  }
} else {
  echo "<script>alert('Username not found.');window.location.href='login.html';</script>";
}
$conn->close();
?>
