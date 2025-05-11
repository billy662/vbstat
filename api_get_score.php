<?php
header('Content-Type: application/json');
include 'conn.php';
include 'functions.php';

$sid = isset($_GET['sid']) && is_numeric($_GET['sid']) ? (int)$_GET['sid'] : -1;

if ($sid == -1) {
    echo json_encode(['error' => 'Invalid or missing set ID', 'total_scored' => 0, 'total_lost' => 0]);
    exit();
}

$scoreboardData = getScoreboard($conn, $sid);

if ($scoreboardData === null) {
    // This case might occur if there are no actions yet for the set,
    // or if getScoreboard explicitly returns null on error/no data.
    // functions.php's getScoreboard returns ['total_scored' => 0, 'total_lost' => 0] if no results,
    // so this specific null check might be redundant if getScoreboard is robust.
    // However, it's good for defensive programming.
    echo json_encode(['error' => 'No scoreboard data found for this set', 'total_scored' => 0, 'total_lost' => 0]);
} else {
    echo json_encode($scoreboardData);
}

$conn->close();
?>
