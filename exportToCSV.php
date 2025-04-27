<?php
include 'conn.php';

$mid = isset($_GET['mid']) && is_numeric($_GET['mid']) ? $_GET['mid'] : 0;
if ($mid == 0) 
    die("Error: no match selected");

// Get the match info first for the filename
$stmt = $conn->prepare("SELECT matches.date, matches.type, team.tname 
        FROM matches 
        JOIN team ON matches.tid = team.tid 
        WHERE matches.mid = ?");
$stmt->bind_param("i", $mid);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    die("Error: match not found");
}

$matchInfo = $result->fetch_assoc();

// Sanitize filename components
$date = preg_replace('/[^a-zA-Z0-9_-]/', '_', $matchInfo['date']);
$type = preg_replace('/[^a-zA-Z0-9_-]/', '_', $matchInfo['type']);
$team = preg_replace('/[^a-zA-Z0-9_-]/', '_', $matchInfo['tname']);
$filename = $date . '_' . $type . '_' . $team . '.csv';

// Set headers in correct order
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename='.$matchInfo['date'].'_'.$matchInfo['type'].'_'.$matchInfo['tname'].'.csv');

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
    ORDER BY 
    	result.resid ASC
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
    
    // Strip HTML tags from all values in the row
    foreach ($row as $key => $value) {
        $row[$key] = strip_tags($value);
    }
    
    fputcsv($output, $row);
}

// Add empty row between tables
fputcsv($output, []);

fclose($output);
$conn->close();
?>