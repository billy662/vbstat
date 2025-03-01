<?php
include 'conn.php';

$mid = isset($_GET['mid']) && is_numeric($_GET['mid']) ? $_GET['mid'] : 0;
if ($mid == 0) 
    die("Error: no match selected");

// Database connection
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Set CSV headers
header('Content-Type: text/csv; charset=utf-8');

// Open output stream
$output = fopen('php://output', 'w');

$sql = "    
    SELECT 
        matches.date AS match_date,
        matches.type AS match_type,
        team.tname AS team_name,
        matches.tgrade AS team_grade,
        matches.trate AS team_rate,
        sets.setNo AS set_number,
        sets.points AS set_points,
        scoreboard.scored AS scoreboard_scored,
        scoreboard.lost AS scoreboard_lost,
        player.pname AS player_name,
        role.rName AS role_name,
        action.category AS action_category,
        action.aname AS action_name,
        action.score AS action_score
    FROM 
        result
    JOIN 
        sets ON result.sid = sets.sid
    JOIN 
        matches ON sets.mid = matches.mid
    JOIN 
        team ON matches.tid = team.tid
    JOIN 
        player ON result.pid = player.pid
    JOIN 
        role ON result.rid = role.rid
    JOIN 
        action ON result.aid = action.aid
    JOIN 
        scoreboard ON result.resid = scoreboard.resid
    WHERE 
        matches.mid = $mid
    ";

// Get table data
$result = $conn->query($sql);

// Write column headers
$fields = $result->fetch_fields();
$headers = array();
foreach ($fields as $field) {
    $headers[] = $field->name;
}
fputcsv($output, $headers);

// Write data rows, if it is the first row, get match date, match type and team name
$isFirstRow = true;
while ($row = $result->fetch_assoc()) {
    if ($isFirstRow) {
        $isFirstRow = false;
        $match_date = $row['match_date'];
        $match_type = $row['match_type'];
        $team_name = $row['team_name'];
    }
    fputcsv($output, $row);
}

header('Content-Disposition: attachment; filename='.$match_date.'_'.$match_type.'_'.$team_name.'.csv');

// Add empty row between tables
fputcsv($output, []);


fclose($output);
$conn->close();
?>