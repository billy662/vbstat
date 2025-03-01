<?php
	function executeQuery($conn, $sql, $redirect){
		if ($conn->query($sql) === TRUE) { 
			if($redirect != ""){
				try {
					header('Location: ' . $redirect);
				} catch (Exception $e) {
					echo $e->printStackTrace();
				}
				exit();
			}
		} 
		else { 
			echo "Error: " . $sql . "<br>" . $conn->error; 
		}
	}

	function insert($conn, $table, $fields, $values, $redirect){
		if(!is_array($fields)){
			$fields = [$fields];
		}
		if(!is_array($values)){
			$values = [$values];
		}

		$fields = implode("`, `", $fields);
		$values = implode("', '", $values);
		$sql = "INSERT INTO `$table` (`$fields`) VALUES ('$values')";
		executeQuery($conn, $sql, $redirect);
	}

	function delete($conn, $table, $field, $value, $redirect){
		$sql = "DELETE FROM $table WHERE $field = $value";
		executeQuery($conn, $sql, $redirect);
	}

	function update($conn, $table, $field, $value, $whereField, $whereValue, $redirect){
		$sql = "UPDATE $table SET $field = $value WHERE $whereField = $whereValue";
		executeQuery($conn, $sql, $redirect);
	}

	function getScoreboard($conn, $sid){
		$scoreboard_sql = "
			SELECT 
				s.scored AS total_scored, 
				s.lost AS total_lost 
			FROM 
				scoreboard s 
			INNER JOIN 
				result r ON s.resid = r.resid 
			WHERE 
				r.sid = {$sid}
			ORDER BY 
				s.sbid DESC
		";
		
		$scoreboard_result = $conn->query($scoreboard_sql)->fetch_assoc();

		if($scoreboard_result == null){
			$scoreboard_result = array("total_scored" => 0, "total_lost" => 0);
		}
		else{
			$scored = $scoreboard_result["total_scored"];
			$lost = $scoreboard_result["total_lost"];
			if(substr($scored, -2) == ".0"){
				$scored = substr($scored, 0, -2);
			}
			if(substr($lost, -2) == ".0"){
				$lost = substr($lost, 0, -2);
			}
			$scoreboard_result = array("total_scored" => $scored, "total_lost" => $lost);
		}

		return $scoreboard_result;
	}

	function getAcid(){
		if(isset($_COOKIE['acid'])){
			return $_COOKIE['acid'];
		}
		else{
			header('Location: login.php');
			exit();
		}
	}
?>