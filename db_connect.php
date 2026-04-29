<?php
// db_connect.php
$servername = "127.0.0.1";
$username = "root";
$password = ""; // default for homebrew mysql
$dbname = "civic_clarity";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
// Note: Errors might show if $dbname doesn't exist yet! We will create it.
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
