<?php
	include 'conn.php';
	include 'functions.php';

	$action = $_GET['action'];

	if($action == "add"){
		$tname = $_GET['tname'];
		$trate = $_GET['trate'];
		$tgrade = $_GET['tgrade'];

		$fields = ["tname", "trate", "tgrade"];
		$values = [$tname, $trate, $tgrade];

		insert($conn, "team", $fields, $values, "opponents.php");
	} 
	elseif($action == "delete"){
		$tid = $_GET['tid'];
		delete($conn, "team", "`team`.`tid`", $tid, "opponents.php");
	}

	// Close the database connection 
	$conn->close();			
?>