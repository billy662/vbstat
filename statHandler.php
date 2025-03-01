<?php
include 'conn.php';
include 'functions.php';

function getMaxResid($conn){
    $sql = "SELECT `resid` FROM `result` WHERE `resid` = (SELECT MAX(`resid`) FROM `result`)";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    return $row["resid"];
}

$mid = $_GET['mid'];
$sid = $_GET['sid'];
$action = $_GET['action'];

if($action == "add"){
    $pid = $_GET['pid'];
    $rid = $_GET['rid'];
    $aid = $_GET['aid'];

    $fields = ["sid", "pid", "rid", "aid"];
    $values = [$sid, $pid, $rid, $aid];

    $sql = "INSERT INTO `result` (`sid`, `pid`, `rid`, `aid`) VALUES ($sid, $pid, $rid, $aid)";
    $result = $conn->query($sql);
    if(!$result){
        header("Location: stats.php?mid=$mid&sid=$sid&result=Error: could not add");
    }
    else{
        // Get the score for this action
        $sql = "SELECT * FROM `action` WHERE `aid` = $aid";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $score = $row["score"];

        // Update scoreboard
        $sql = "
            SELECT scoreboard.*
            FROM scoreboard
            JOIN result ON scoreboard.resid = result.resid
            JOIN sets ON result.sid = sets.sid
            WHERE sets.sid = $sid
            AND scoreboard.sbid = (
                SELECT MAX(scoreboard.sbid)
                FROM scoreboard
                JOIN result ON scoreboard.resid = result.resid
                JOIN sets ON result.sid = sets.sid
                WHERE sets.sid = $sid
            );";

        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $scored = $row["scored"];
        $lost = $row["lost"];

        $resid = getMaxResid($conn);
        $fields = ["resid", "scored", "lost"];
        $values = [$resid, $scored, $lost];

        if($score == -1){
            $values[2]++;
        }
        else{
            $values[1] += $score;
        }
        insert($conn, "scoreboard", $fields, $values, "stats.php?mid=$mid&sid=$sid&result=Successfully added");
    }
}
elseif($action == "undo"){
    $sql = "SELECT * FROM `result` WHERE `sid` = $sid";
    $result = $conn->query($sql);
    if($result->num_rows == 0){
        header("Location: stats.php?mid=$mid&sid=$sid&result=No actions to undo");
    }
    else{
        $resid = getMaxResid($conn);
        $sql = "DELETE FROM `scoreboard` WHERE `resid` = $resid";
        if (!$conn->query($sql)) {
            header("Location: stats.php?mid=$mid&sid=$sid&result=Error: could not undo");
            exit();
        }

        $sql = "DELETE FROM `result` WHERE `resid` = $resid";
        if (!delete($conn, "result", "resid", $resid, "stats.php?mid=$mid&sid=$sid&result=Successfully undone")) {
            header("Location: stats.php?mid=$mid&sid=$sid&result=Error: could not undo");
        }
    }
}


// Close the database connection
$conn->close();
?>

