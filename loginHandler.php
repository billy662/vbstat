<?php
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acid'])) {
    $acid = $_POST['acid'];
    
    // Set a cookie with the acid value, valid for 30 days
    setcookie('acid', $acid, time() + (86400 * 30), "/"); // 86400 = 1 day
    
    // Redirect to index.php
    header('Location: index.php');
    exit();
} else {
    // Handle invalid access
    header('Location: login.php');
    exit();
}
?>