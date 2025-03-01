<?php
	include 'conn.php';
	include 'functions.php';

	$acid = getAcid();
	$action = $_GET['action'];

	if($action == "add"){
		$player = $_GET['player'];

		$fields = ["acid", "pname"];
		$values = [$acid, $player];

		insert($conn, "player", $fields, $values, "players.php");
	} 
	elseif($action == "delete"){
		$pid = $_GET['pid'];
		delete($conn, "player", "`player`.`pid`", $pid, "players.php");
	}

	// Close the database connection 
	$conn->close();			
?>