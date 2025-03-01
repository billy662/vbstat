<?php
	include 'conn.php';
	include 'functions.php';

	$acid = 0;
	// Check if the user is logged in
	if (!isset($_COOKIE['acid'])) {
		header('Location: login.php');
		exit();
	}
	else{
		$acid = $_COOKIE['acid'];
	}

	$action = $_GET['action'];

	if($action == "add"){
		$date = $_GET['date'];
		$type = $_GET['type'];
		$tid = $_GET['tid'];
		$tgrade = $_GET['tgrade'];
		$trate = $_GET['trate'];
		if(empty($_GET['youtube'])) $youtube = NULL;
		else
			$youtube = $_GET['youtube'];

		$fields = ["acid","date", "type", "tid", "tgrade", "trate", "youtube"];
		$values = [$acid, $date, $type, $tid, $tgrade, $trate, $youtube];

		insert($conn, "matches", $fields, $values, "index.php");
	} 
	elseif($action == "delete"){
		$mid = $_GET['mid'];

		$sql = "SELECT DISTINCT `sid` FROM `sets` WHERE `mid` = " . $mid;
		$sid_result = $conn->query($sql);
		if($sid_result->num_rows > 0){
			foreach($sid_result as $set){
				$sid = $set["sid"];
				$sql = "SELECT DISTINCT `resid` FROM `result` WHERE `sid` = " . $sid;
				$result_result = $conn->query($sql);
				if($result_result->num_rows > 0){
					foreach($result_result as $result){
						$resid = $result["resid"];
						$sql = "SELECT DISTINCT `sbid` FROM `scoreboard` WHERE `resid` = " . $resid;
						$scoreboard_result = $conn->query($sql);
						foreach($scoreboard_result as $scoreboard){
							delete($conn, "scoreboard", "`scoreboard`.`sbid`", $scoreboard["sbid"], "");
						}
						delete($conn, "result", "`result`.`resid`", $resid, "");
					}
				}
				delete($conn, "sets", "`sets`.`sid`", $sid, "");
			}
		}
		delete($conn, "matches", "`matches`.`mid`", $mid, "index.php");
	}
	elseif($action == "edit"){
		$mid = $_GET['mid'];
		$youtube = "'" . $_GET['youtube'] . "'";

		update($conn, "matches", "`youtube`", $youtube, "`matches`.`mid`", $mid, "set.php?mid=$mid");
	}

	// Close the database connection 
	$conn->close();
?>