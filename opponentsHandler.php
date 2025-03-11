<?php
	include 'conn.php';
	include 'functions.php';

	// Set default redirect
	$redirect = "opponents.php";
	
	// Initialize session if not already started
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	
	// Check if the request is POST or GET
	$requestMethod = $_SERVER['REQUEST_METHOD'];
	
	if ($requestMethod === 'POST') {
		$action = isset($_POST['action']) ? $_POST['action'] : '';
	} else {
		$action = isset($_GET['action']) ? $_GET['action'] : '';
	}

	// Handle different actions
	switch ($action) {
		case "add":
			// Basic team addition
			if ($requestMethod === 'POST' && isset($_POST['tname'])) {
				$tname = trim($_POST['tname']);
				
				// Validate team name
				if (empty($tname)) {
					$_SESSION['error_message'] = "Team name cannot be empty";
					header("Location: $redirect");
					exit;
				}
				
				// Check if team already exists
				$stmt = $conn->prepare("SELECT COUNT(*) as count FROM team WHERE tname = ?");
				$stmt->bind_param("s", $tname);
				$stmt->execute();
				$result = $stmt->get_result();
				$row = $result->fetch_assoc();
				
				if ($row['count'] > 0) {
					$_SESSION['error_message'] = "Team '$tname' already exists";
					header("Location: $redirect");
					exit;
				}
				
				// Insert the new team
				$stmt = $conn->prepare("INSERT INTO team (tname) VALUES (?)");
				$stmt->bind_param("s", $tname);
				
				if ($stmt->execute()) {
					$_SESSION['success_message'] = "Team '$tname' added successfully";
				} else {
					$_SESSION['error_message'] = "Error adding team: " . $conn->error;
				}
			} else {
				$_SESSION['error_message'] = "Invalid request";
			}
			break;
			
		case "addDetailed":
			// Detailed team addition
			if ($requestMethod === 'POST' && isset($_POST['tname'])) {
				$tname = trim($_POST['tname']);
				$trate = isset($_POST['trate']) ? intval($_POST['trate']) : 5;
				$tgrade = isset($_POST['tgrade']) ? $_POST['tgrade'] : '其他';
				$tnotes = isset($_POST['tnotes']) ? trim($_POST['tnotes']) : '';
				
				// Validate team name
				if (empty($tname)) {
					$_SESSION['error_message'] = "Team name cannot be empty";
					header("Location: $redirect");
					exit;
				}
				
				// Check if team already exists
				$stmt = $conn->prepare("SELECT COUNT(*) as count FROM team WHERE tname = ?");
				$stmt->bind_param("s", $tname);
				$stmt->execute();
				$result = $stmt->get_result();
				$row = $result->fetch_assoc();
				
				if ($row['count'] > 0) {
					$_SESSION['error_message'] = "Team '$tname' already exists";
					header("Location: $redirect");
					exit;
				}
				
				// Validate rating (1-10)
				if ($trate < 1 || $trate > 10) {
					$trate = 5; // Default to middle rating if invalid
				}
				
				// Insert the new team with details
				$stmt = $conn->prepare("INSERT INTO team (tname, trate, tgrade, tnotes) VALUES (?, ?, ?, ?)");
				$stmt->bind_param("siss", $tname, $trate, $tgrade, $tnotes);
				
				if ($stmt->execute()) {
					$_SESSION['success_message'] = "Team '$tname' added successfully with details";
				} else {
					$_SESSION['error_message'] = "Error adding team: " . $conn->error;
				}
			} else {
				$_SESSION['error_message'] = "Invalid request";
			}
			break;
			
		case "delete":
			// Team deletion
			if (isset($_GET['tid'])) {
				$tid = intval($_GET['tid']);
				
				// Check if team exists
				$stmt = $conn->prepare("SELECT tname FROM team WHERE tid = ?");
				$stmt->bind_param("i", $tid);
				$stmt->execute();
				$result = $stmt->get_result();
				
				if ($result->num_rows === 0) {
					$_SESSION['error_message'] = "Team not found";
					header("Location: $redirect");
					exit;
				}
				
				$teamName = $result->fetch_assoc()['tname'];
				
				// Check if team has matches
				$stmt = $conn->prepare("SELECT COUNT(*) as count FROM matches WHERE tid = ?");
				$stmt->bind_param("i", $tid);
				$stmt->execute();
				$result = $stmt->get_result();
				$row = $result->fetch_assoc();
				
				if ($row['count'] > 0) {
					$_SESSION['error_message'] = "Cannot delete team '$teamName' - it has match records";
					header("Location: $redirect");
					exit;
				}
				
				// Delete the team
				$stmt = $conn->prepare("DELETE FROM team WHERE tid = ?");
				$stmt->bind_param("i", $tid);
				
				if ($stmt->execute()) {
					$_SESSION['success_message'] = "Team '$teamName' deleted successfully";
				} else {
					$_SESSION['error_message'] = "Error deleting team: " . $conn->error;
				}
			} else {
				$_SESSION['error_message'] = "Invalid team ID";
			}
			break;
			
		case "edit":
			// Team editing (for future implementation)
			if ($requestMethod === 'POST' && isset($_POST['tid']) && isset($_POST['tname'])) {
				$tid = intval($_POST['tid']);
				$tname = trim($_POST['tname']);
				$trate = isset($_POST['trate']) ? intval($_POST['trate']) : 5;
				$tgrade = isset($_POST['tgrade']) ? $_POST['tgrade'] : '其他';
				$tnotes = isset($_POST['tnotes']) ? trim($_POST['tnotes']) : '';
				
				// Validate team name
				if (empty($tname)) {
					$_SESSION['error_message'] = "Team name cannot be empty";
					header("Location: $redirect");
					exit;
				}
				
				// Check if team with this name already exists (excluding current team)
				$stmt = $conn->prepare("SELECT COUNT(*) as count FROM team WHERE tname = ? AND tid != ?");
				$stmt->bind_param("si", $tname, $tid);
				$stmt->execute();
				$result = $stmt->get_result();
				$row = $result->fetch_assoc();
				
				if ($row['count'] > 0) {
					$_SESSION['error_message'] = "Another team with name '$tname' already exists";
					header("Location: $redirect");
					exit;
				}
				
				// Update the team
				$stmt = $conn->prepare("UPDATE team SET tname = ?, trate = ?, tgrade = ?, tnotes = ? WHERE tid = ?");
				$stmt->bind_param("sissi", $tname, $trate, $tgrade, $tnotes, $tid);
				
				if ($stmt->execute()) {
					$_SESSION['success_message'] = "Team '$tname' updated successfully";
				} else {
					$_SESSION['error_message'] = "Error updating team: " . $conn->error;
				}
			} else {
				$_SESSION['error_message'] = "Invalid request";
			}
			break;
			
		default:
			$_SESSION['error_message'] = "Unknown action";
			break;
	}

	// Redirect back to opponents page
	header("Location: $redirect");
	exit;
?>