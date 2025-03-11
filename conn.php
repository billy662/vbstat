<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vbstat";

try {
    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Set charset to utf8
    if (!$conn->set_charset("utf8")) {
        printf("Error loading character set utf8: %s\n", $conn->error);
    }

    // Check connection error
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die($e->getMessage());
}
?>