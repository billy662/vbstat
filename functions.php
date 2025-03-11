<?php
    function executeQuery($conn, $sql, $redirect){
        if ($conn->query($sql) === TRUE) { 
            if($redirect != ""){
                try {
                    header('Location: ' . $redirect);
                } catch (Exception $e) {
                    echo $e->getMessage();
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

        // Use prepared statements for better security
        $placeholders = array_fill(0, count($values), '?');
        $fields_str = implode("`, `", $fields);
        $placeholders_str = implode(", ", $placeholders);
        
        $sql = "INSERT INTO `$table` (`$fields_str`) VALUES ($placeholders_str)";
        $stmt = $conn->prepare($sql);
        
        // Create type string for bind_param
        $types = '';
        foreach($values as $value) {
            if(is_int($value)) $types .= 'i';
            elseif(is_float($value)) $types .= 'd';
            else $types .= 's';
        }
        
        // Dynamically bind parameters
        $stmt->bind_param($types, ...$values);
        
        if($stmt->execute()) {
            if($redirect != "") {
                header('Location: ' . $redirect);
                exit();
            }
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    function delete($conn, $table, $field, $value, $redirect){
        // Use prepared statement for delete
        $sql = "DELETE FROM $table WHERE $field = ?";
        $stmt = $conn->prepare($sql);
        
        // Determine type of value
        $type = is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
        $stmt->bind_param($type, $value);
        
        if($stmt->execute()) {
            if($redirect != "") {
                header('Location: ' . $redirect);
                exit();
            }
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

    function update($conn, $table, $field, $value, $whereField, $whereValue, $redirect){
        // Use prepared statement for update
        $sql = "UPDATE $table SET $field = ? WHERE $whereField = ?";
        $stmt = $conn->prepare($sql);
        
        // Determine types
        $type1 = is_int($value) ? 'i' : (is_float($value) ? 'd' : 's');
        $type2 = is_int($whereValue) ? 'i' : (is_float($whereValue) ? 'd' : 's');
        $stmt->bind_param($type1 . $type2, $value, $whereValue);
        
        if($stmt->execute()) {
            if($redirect != "") {
                header('Location: ' . $redirect);
                exit();
            }
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }

	function getScoreboard($conn, $sid){
        // Use prepared statement for better security
        $scoreboard_sql = "
            SELECT 
                s.scored AS total_scored, 
                s.lost AS total_lost 
            FROM 
                scoreboard s 
            INNER JOIN 
                result r ON s.resid = r.resid 
            WHERE 
                r.sid = ?
            ORDER BY 
                s.sbid DESC
        ";
        
        $stmt = $conn->prepare($scoreboard_sql);
        $stmt->bind_param("i", $sid);
        $stmt->execute();
        $result = $stmt->get_result();
        $scoreboard_result = $result->fetch_assoc();
        $stmt->close();

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