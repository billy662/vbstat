<?php
include 'conn.php';
include 'functions.php';

/**
 * Gets the maximum result ID from the result table
 * @param mysqli $conn Database connection
 * @return int The maximum result ID
 */
function getMaxResid($conn) {
    $sql = "SELECT `resid` FROM `result` WHERE `resid` = (SELECT MAX(`resid`) FROM `result`)";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["resid"];
    }
    return 0;
}

/**
 * Validates and sanitizes input parameters
 * @param array $params Array of parameters to validate
 * @return bool True if all parameters are valid
 */
function validateParams($params) {
    foreach ($params as $param) {
        if (!isset($_GET[$param]) || !is_numeric($_GET[$param])) {
            return false;
        }
    }
    return true;
}

/**
 * Redirects to stats page with a result message
 * @param int $mid Match ID
 * @param int $sid Set ID
 * @param string $result Result message
 */
function redirectToStats($mid, $sid, $result) {
    header("Location: stats.php?mid=$mid&sid=$sid&result=$result");
    exit();
}

// Get and validate required parameters
if (!isset($_GET['mid']) || !isset($_GET['sid']) || !isset($_GET['action'])) {
    header("Location: index.php");
    exit();
}

$mid = intval($_GET['mid']);
$sid = intval($_GET['sid']);
$action = $_GET['action'];

// Process based on action type
if ($action == "add") {
    // Validate required parameters for add action
    if (!validateParams(['pid', 'rid', 'aid'])) {
        redirectToStats($mid, $sid, "Error: Invalid parameters");
    }
    
    $pid = intval($_GET['pid']);
    $rid = intval($_GET['rid']);
    $aid = intval($_GET['aid']);

    // Use prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO `result` (`sid`, `pid`, `rid`, `aid`) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiii", $sid, $pid, $rid, $aid);
    
    if (!$stmt->execute()) {
        redirectToStats($mid, $sid, "Error: could not add - " . $conn->error);
    }
    
    // Get the score for this action
    $stmt = $conn->prepare("SELECT * FROM `action` WHERE `aid` = ?");
    $stmt->bind_param("i", $aid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $score = $row["score"];
        
        // Update scoreboard
        $sql = "
            SELECT scoreboard.*
            FROM scoreboard
            JOIN result ON scoreboard.resid = result.resid
            JOIN sets ON result.sid = sets.sid
            WHERE sets.sid = $sid
            AND scoreboard.sbid = (
                SELECT MAX(scoreboard.sbid)
                FROM scoreboard
                JOIN result ON scoreboard.resid = result.resid
                JOIN sets ON result.sid = sets.sid
                WHERE sets.sid = $sid
            );";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $scored = $row["scored"];
            $lost = $row["lost"];
            
            $resid = getMaxResid($conn);
            $fields = ["resid", "scored", "lost"];
            $values = [$resid, $scored, $lost];
            
            if ($score == -1) {
                $values[2]++;
            } else {
                $values[1] += $score;
            }
            
            insert($conn, "scoreboard", $fields, $values, "stats.php?mid=$mid&sid=$sid&result=Successfully added");
        } else {
            // First entry for this set, create initial scoreboard
            $resid = getMaxResid($conn);
            $fields = ["resid", "scored", "lost"];
            $values = [$resid, ($score > 0 ? $score : 0), ($score == -1 ? 1 : 0)];
            
            insert($conn, "scoreboard", $fields, $values, "stats.php?mid=$mid&sid=$sid&result=Successfully added");
        }
    } else {
        redirectToStats($mid, $sid, "Error: Invalid action");
    }
} elseif ($action == "undo") {
    // Check if there are actions to undo for this set
    $stmt = $conn->prepare("SELECT * FROM `result` WHERE `sid` = ?");
    $stmt->bind_param("i", $sid);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 0) {
        redirectToStats($mid, $sid, "No actions to undo");
    } else {
        $resid = getMaxResid($conn);
        
        // Begin transaction to ensure both operations succeed or fail together
        $conn->begin_transaction();
        
        try {
            // Delete from scoreboard first (foreign key constraint)
            $stmt = $conn->prepare("DELETE FROM `scoreboard` WHERE `resid` = ?");
            $stmt->bind_param("i", $resid);
            if (!$stmt->execute()) {
                throw new Exception("Could not delete from scoreboard");
            }
            
            // Delete from result
            $stmt = $conn->prepare("DELETE FROM `result` WHERE `resid` = ?");
            $stmt->bind_param("i", $resid);
            if (!$stmt->execute()) {
                throw new Exception("Could not delete from result");
            }
            
            // Commit transaction
            $conn->commit();
            redirectToStats($mid, $sid, "Successfully undone");
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            redirectToStats($mid, $sid, "Error: could not undo - " . $e->getMessage());
        }
    }
} else {
    // Invalid action
    redirectToStats($mid, $sid, "Error: Invalid action");
}

// Close the database connection
$conn->close();
?>