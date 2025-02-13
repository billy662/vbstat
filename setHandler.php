<?php
	include 'conn.php';
	include 'functions.php';

	$action = $_GET['action'];
    $mid = $_GET['mid'];

	if($action == "add"){
		$setNo = $_GET['setNo'];
		$points = $_GET['points'];

		$fields = ["mid", "setNo", "points"];
		$values = [$mid, $setNo, $points];

		insert($conn, "sets", $fields, $values, "set.php?mid=$mid");
	} 
	elseif($action == "delete"){
		$sid = $_GET['sid'];
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
		delete($conn, "sets", "`sets`.`sid`", $sid, "set.php?mid=$mid");
	}

	// Close the database connection 
	$conn->close();			
?>
