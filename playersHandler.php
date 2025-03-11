<?php
	include 'conn.php';
	include 'functions.php';

	$acid = getAcid();
	
	// Redirect function with status and message
	function redirectWithStatus($status, $message = '') {
		$url = "players.php?status=" . urlencode($status);
		if ($message) {
			$url .= "&message=" . urlencode($message);
		}
		header("Location: " . $url);
		exit();
	}
	
	// Validate and sanitize input
	function sanitizeInput($conn, $input) {
		return $conn->real_escape_string(trim($input));
	}

	// Handle POST request for adding players (more secure than GET)
	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == "add") {
		if (empty($_POST['player'])) {
			redirectWithStatus('error', 'Player name cannot be empty');
		}
		
		$player = sanitizeInput($conn, $_POST['player']);
		
		// Check if player already exists for this account
		$stmt = $conn->prepare("SELECT 1 FROM player WHERE acid = ? AND pname = ?");
		$stmt->bind_param("is", $acid, $player);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if ($result->num_rows > 0) {
			$stmt->close();
			redirectWithStatus('error', 'Player already exists');
		}
		
		$stmt->close();
		
		// Insert new player
		$stmt = $conn->prepare("INSERT INTO player (acid, pname) VALUES (?, ?)");
		$stmt->bind_param("is", $acid, $player);
		
		if ($stmt->execute()) {
			redirectWithStatus('success', 'Player added successfully');
		} else {
			redirectWithStatus('error', 'Failed to add player: ' . $conn->error);
		}
		
		$stmt->close();
	} 
	// Handle GET request for deleting players
	elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] == "delete") {
		if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
			redirectWithStatus('error', 'Invalid player ID');
		}
		
		$pid = (int)$_GET['pid'];
		
		// Check if player belongs to this account
		$stmt = $conn->prepare("SELECT 1 FROM player WHERE pid = ? AND acid = ?");
		$stmt->bind_param("ii", $pid, $acid);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if ($result->num_rows == 0) {
			$stmt->close();
			redirectWithStatus('error', 'Player not found or access denied');
		}
		
		$stmt->close();
		
		// Check if player has any results
		$stmt = $conn->prepare("SELECT 1 FROM result WHERE pid = ? LIMIT 1");
		$stmt->bind_param("i", $pid);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if ($result->num_rows > 0) {
			$stmt->close();
			redirectWithStatus('error', 'Cannot delete player with existing records');
		}
		
		$stmt->close();
		
		// Delete the player
		$stmt = $conn->prepare("DELETE FROM player WHERE pid = ? AND acid = ?");
		$stmt->bind_param("ii", $pid, $acid);
		
		if ($stmt->execute()) {
			redirectWithStatus('success', 'Player deleted successfully');
		} else {
			redirectWithStatus('error', 'Failed to delete player: ' . $conn->error);
		}
		
		$stmt->close();
	} else {
		// Invalid request
		redirectWithStatus('error', 'Invalid request');
	}

	// Close the database connection 
	$conn->close();			
?>