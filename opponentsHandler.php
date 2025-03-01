<?php
	include 'conn.php';
	include 'functions.php';

	$action = $_GET['action'];

	if($action == "add"){
		$tname = $_GET['tname'];

		$fields = ["tname"];
		$values = [$tname];

		insert($conn, "team", $fields, $values, "opponents.php");
	} 
	elseif($action == "delete"){
		$tid = $_GET['tid'];
		delete($conn, "team", "`team`.`tid`", $tid, "opponents.php");
	}

	// Close the database connection 
	$conn->close();
?>