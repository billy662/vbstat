<?php
$servername = "192.168.1.102:3306";
$username = "root";
$password = "root";
$dbname = "vbstat";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Set charset to utf8
if (!$conn->set_charset("utf8")) {
    printf("Error loading character set utf8: %s\n", $mysqli->error);
}

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
?>
