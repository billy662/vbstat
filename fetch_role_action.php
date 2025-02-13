<?php
    include 'conn.php';

    $sql = "SELECT rid, aid FROM role_action";
    $result = $conn->query($sql);
    $ar = array();
    if($result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $ar[] = array("rid" => $row["rid"], "aid" => $row["aid"]);
        }
    }
    header('Content-Type: application/json');
    echo json_encode($ar);
?>