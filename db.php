<?php
$servername = "localhost";
$username = "root"; // default for XAMPP
$password = "";     // default empty
$dbname = "scutter_casino_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
