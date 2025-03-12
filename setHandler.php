<?php
	include 'conn.php';
	include 'functions.php';

	// Validate required parameters
    if (!isset($_POST['action']) || !isset($_POST['mid'])) {
        header("Location: index.php");
        exit;
    }

    $action = $_POST['action'];
    $mid = filter_input(INPUT_POST, 'mid', FILTER_VALIDATE_INT);
	
	if (!$mid) {
		header("Location: index.php");
		exit;
	}

	if ($action == "add") {
		if (!isset($_POST['setNo']) || !isset($_POST['points'])) {
			header("Location: set.php?mid=$mid");
			exit;
		}
		
		$setNo = filter_input(INPUT_POST, 'setNo', FILTER_VALIDATE_INT); 
        $points = filter_input(INPUT_POST, 'points', FILTER_VALIDATE_INT); 
		
		if (!$setNo || !$points) {
			header("Location: set.php?mid=$mid");
			exit;
		}

		$fields = ["mid", "setNo", "points"];
		$values = [$mid, $setNo, $points];

		insert($conn, "sets", $fields, $values, "set.php?mid=$mid");
	} 
	elseif ($action == "delete") {
		if (!isset($_POST['sid'])) {
			header("Location: set.php?mid=$mid");
			exit;
		}
		
		$sid = filter_input(INPUT_POST, 'sid', FILTER_VALIDATE_INT); 
		
		if (!$sid) {
			header("Location: set.php?mid=$mid");
			exit;
		}
		
		// Use prepared statements to prevent SQL injection
		$stmt = $conn->prepare("SELECT DISTINCT `resid` FROM `result` WHERE `sid` = ?");
		$stmt->bind_param("i", $sid);
		$stmt->execute();
		$result_result = $stmt->get_result();
		
		if ($result_result->num_rows > 0) {
			foreach ($result_result as $result) {
				$resid = $result["resid"];
				
				$stmt = $conn->prepare("SELECT DISTINCT `sbid` FROM `scoreboard` WHERE `resid` = ?");
				$stmt->bind_param("i", $resid);
				$stmt->execute();
				$scoreboard_result = $stmt->get_result();
				
				foreach ($scoreboard_result as $scoreboard) {
					delete($conn, "scoreboard", "`scoreboard`.`sbid`", $scoreboard["sbid"], "");
				}
				delete($conn, "result", "`result`.`resid`", $resid, "");
			}
		}
		delete($conn, "sets", "`sets`.`sid`", $sid, "set.php?mid=$mid");
	}
	else {
		// Invalid action
		header("Location: set.php?mid=$mid");
		exit;
	}

	// Close the database connection 
	$conn->close();			
?>