<?php
	include 'conn.php';
	include 'functions.php';

	// Initialize variables
	$acid = 0;
	$response = ['success' => false, 'message' => ''];
	
	// Check if the user is logged in
	if (!isset($_COOKIE['acid'])) {
		header('Location: login.php');
		exit();
	} else {
		$acid = intval($_COOKIE['acid']);
	}

	// Validate action parameter exists
	if (!isset($_POST['action']) && !isset($_GET['action'])) {
		header('Location: index.php');
		exit();
	}

	$action = isset($_POST['action']) ? $_POST['action'] : $_GET['action'];

	// Process based on action type
	if ($action == "add") {
		// Validate required parameters
		if (!isset($_POST['date']) || !isset($_POST['type']) || !isset($_POST['tid']) || 
			!isset($_POST['tgrade']) || !isset($_POST['trate'])) {
			header('Location: index.php?error=missing_parameters');
			exit();
		}
		
		$date = $_POST['date'];
		$type = $_POST['type'];
		$tid = intval($_POST['tid']);
		$tgrade = $_POST['tgrade'];
		$trate = intval($_POST['trate']);
		$youtube = empty($_POST['youtube']) ? NULL : htmlspecialchars($_POST['youtube'], ENT_QUOTES, 'UTF-8');

		$fields = ["acid", "date", "type", "tid", "tgrade", "trate", "youtube"];
		$values = [$acid, $date, $type, $tid, $tgrade, $trate, $youtube];

		try {
			insert($conn, "matches", $fields, $values, "index.php");
		} catch (Exception $e) {
			header('Location: index.php?error=insert_failed');
			exit();
		}
	} 
	elseif ($action == "delete") {
		// Validate mid parameter
		if (!isset($_GET['mid'])) {
			header('Location: index.php?error=missing_mid');
			exit();
		}
		
		$mid = intval($_GET['mid']);
		
		try {
			// Begin transaction for cascading deletes
			$conn->begin_transaction();
			
			// Get all sets for this match
			$sql = "SELECT DISTINCT `sid` FROM `sets` WHERE `mid` = ?";
			$stmt = $conn->prepare($sql);
			$stmt->bind_param("i", $mid);
			$stmt->execute();
			$sid_result = $stmt->get_result();
			
			if ($sid_result->num_rows > 0) {
				foreach ($sid_result as $set) {
					$sid = $set["sid"];
					
					// Get all results for this set
					$sql = "SELECT DISTINCT `resid` FROM `result` WHERE `sid` = ?";
					$stmt = $conn->prepare($sql);
					$stmt->bind_param("i", $sid);
					$stmt->execute();
					$result_result = $stmt->get_result();
					
					if ($result_result->num_rows > 0) {
						foreach ($result_result as $result) {
							$resid = $result["resid"];
							
							// Delete all scoreboard entries for this result
							$sql = "SELECT DISTINCT `sbid` FROM `scoreboard` WHERE `resid` = ?";
							$stmt = $conn->prepare($sql);
							$stmt->bind_param("i", $resid);
							$stmt->execute();
							$scoreboard_result = $stmt->get_result();
							
							foreach ($scoreboard_result as $scoreboard) {
								delete($conn, "scoreboard", "`scoreboard`.`sbid`", $scoreboard["sbid"], "");
							}
							
							// Delete the result
							delete($conn, "result", "`result`.`resid`", $resid, "");
						}
					}
					
					// Delete the set
					delete($conn, "sets", "`sets`.`sid`", $sid, "");
				}
			}
			
			// Delete the match
			delete($conn, "matches", "`matches`.`mid`", $mid, "");
			
			// Commit the transaction
			$conn->commit();
			header('Location: index.php?success=deleted');
			
		} catch (Exception $e) {
			// Rollback on error
			$conn->rollback();
			header('Location: index.php?error=delete_failed');
			exit();
		}
	}
	elseif ($action == "edit") {
		// Validate required parameters
		if (!isset($_POST['mid'])) {
			header('Location: index.php?error=missing_mid');
			exit();
		}
		
		$mid = intval($_POST['mid']);
		$youtube = isset($_POST['youtube']) ? htmlspecialchars($_POST['youtube'], ENT_QUOTES, 'UTF-8') : NULL;
		
		try {
			update($conn, "matches", "`youtube`", $youtube, "`matches`.`mid`", $mid, "set.php?mid=$mid");
		} catch (Exception $e) {
			header('Location: set.php?mid=' . $mid . '&error=update_failed');
			exit();
		}
	}
	else {
		// Invalid action
		header('Location: index.php?error=invalid_action');
		exit();
	}

	// Close the database connection 
	$conn->close();
?>