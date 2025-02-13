<?php
	include 'conn.php';
	include 'functions.php';

	$action = $_GET['action'];

	if($action == "add"){
		$date = $_GET['date'];
		$type = $_GET['type'];
		$tid = $_GET['tid'];

		$fields = ["date", "type", "tid"];
		$values = [$date, $type, $tid];

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

	// Close the database connection 
	$conn->close();
?>