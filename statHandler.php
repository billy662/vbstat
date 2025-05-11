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
} elseif ($action == "delete") {
    // Validate required parameter for delete action
    if (!isset($_GET['resid']) || !is_numeric($_GET['resid'])) {
        redirectToStats($mid, $sid, "Error: Invalid resid for delete");
    }

    $resid_to_delete = intval($_GET['resid']);

    // Begin transaction
    $conn->begin_transaction();

    try {
        // 1. Fetch necessary info BEFORE deleting
        $stmt_info = $conn->prepare("
            SELECT r.sid, r.aid, a.score, sb.sbid
            FROM result r
            JOIN action a ON r.aid = a.aid
            LEFT JOIN scoreboard sb ON r.resid = sb.resid
            WHERE r.resid = ?
        ");
        $stmt_info->bind_param("i", $resid_to_delete);
        $stmt_info->execute();
        $info_result = $stmt_info->get_result();

        if ($info_result->num_rows == 0) {
            throw new Exception("Action to delete (resid: $resid_to_delete) not found in result table.");
        }
        $action_info = $info_result->fetch_assoc();
        $stmt_info->close();

        $sid_of_deleted_action = intval($action_info['sid']);
        $score_value = floatval($action_info['score']);
        $sbid_of_deleted_scoreboard_entry = $action_info['sbid'] ? intval($action_info['sbid']) : null;

        if ($sbid_of_deleted_scoreboard_entry === null) {
             // This case implies the result existed but its scoreboard entry was missing.
             // This could happen if a previous operation failed or if not all results get scoreboard entries.
             // For robustness, if we can't find the sbid, we might have to fall back to rebuilding the whole set,
             // or log an error. For now, we'll throw an exception if sbid is crucial and missing.
             // Given that 'add' always creates a scoreboard entry, this should ideally not be null.
            throw new Exception("Scoreboard entry for resid $resid_to_delete not found. Cannot adjust subsequent scores accurately without it.");
        }

        // 2. Calculate score change caused by the deleted action
        $delta_scored_change = 0.0;
        $delta_lost_change = 0.0;

        if ($score_value == -1) {
            $delta_lost_change = 1.0;
        } elseif ($score_value > 0) { // Handles 1.0 and 0.5
            $delta_scored_change = $score_value;
        }
        // If score_value is 0, deltas remain 0, no score adjustment needed for subsequent entries.

        // 3. Delete from scoreboard for the specific resid
        $stmt_del_sb = $conn->prepare("DELETE FROM `scoreboard` WHERE `resid` = ?");
        $stmt_del_sb->bind_param("i", $resid_to_delete);
        $stmt_del_sb->execute(); // Execute and proceed; subsequent logic handles score adjustments
        $stmt_del_sb->close();

        // 4. Delete from result
        $stmt_del_res = $conn->prepare("DELETE FROM `result` WHERE `resid` = ?");
        $stmt_del_res->bind_param("i", $resid_to_delete);
        if (!$stmt_del_res->execute()) {
            throw new Exception("Could not delete action (resid: $resid_to_delete) from result table.");
        }
        if ($stmt_del_res->affected_rows == 0) {
            // This means the $stmt_info found it, but it was gone by the time we tried to delete.
            // This could indicate a race condition or prior error.
            throw new Exception("No action found with resid $resid_to_delete to delete from result table (was present moments ago).");
        }
        $stmt_del_res->close();

        // 5. Update subsequent scoreboard entries if there was a score impact
        if ($delta_scored_change != 0.0 || $delta_lost_change != 0.0) {
            $stmt_update_subsequent = $conn->prepare("
                UPDATE scoreboard sb
                JOIN result r ON sb.resid = r.resid
                SET
                    sb.scored = sb.scored - ?, 
                    sb.lost = sb.lost - ?
                WHERE
                    r.sid = ? 
                    AND sb.sbid > ?
            ");
            // Bind parameters: delta_scored, delta_lost, sid_of_action, sbid_of_deleted_scoreboard_entry
            $stmt_update_subsequent->bind_param("ddii", $delta_scored_change, $delta_lost_change, $sid_of_deleted_action, $sbid_of_deleted_scoreboard_entry);
            if (!$stmt_update_subsequent->execute()) {
                throw new Exception("Could not update subsequent scoreboard entries for set $sid_of_deleted_action.");
            }
            $stmt_update_subsequent->close();
        }
        
        // Commit transaction
        $conn->commit();
        redirectToStats($mid, $sid, "Successfully deleted action and adjusted scores.");

    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        redirectToStats($mid, $sid, "Error: could not delete action - " . $e->getMessage());
    }
} else {
    // Invalid action
    redirectToStats($mid, $sid, "Error: Invalid action type specified.");
}

// Close the database connection
$conn->close();
?>
