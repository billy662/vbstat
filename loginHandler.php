<?php
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acid'])) {
    $acid = filter_input(INPUT_POST, 'acid', FILTER_VALIDATE_INT);
    
    if($acid === false || $acid === null) {
        // Invalid acid value
        header('Location: login.php?error=invalid_acid');
        exit();
    }
    
    // Verify the acid exists in the database
    $stmt = $conn->prepare("SELECT acid FROM accounts WHERE acid = ?");
    $stmt->bind_param("i", $acid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows === 0) {
        // Account not found
        header('Location: login.php?error=account_not_found');
        exit();
    }
    
    // Set a cookie with the acid value, valid for 30 days
    setcookie('acid', $acid, time() + (86400 * 30), "/", "", false, true); // HttpOnly flag added
    
    // Redirect to index.php
    header('Location: index.php');
    exit();
} else {
    // Handle invalid access
    header('Location: login.php');
    exit();
}
?>