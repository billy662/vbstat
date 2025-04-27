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
    -- Use a Common Table Expression (CTE) to first gather all necessary data and calculate LAG values
    WITH ResultsWithContext AS (
        SELECT
            -- Base identifiers (useful for ordering and joining)
            r.resid,
            r.sid,
            s.mid,

            -- Columns A-I (from matches, team, sets, scoreboard)
            m.date AS match_date,
            m.type AS match_type,
            t.tname AS team_name,
            m.tgrade AS team_grade,
            m.trate AS team_rate,
            s.setNo AS set_number,
            s.points AS set_points,
            sb.scored AS scoreboard_scored,
            sb.lost AS scoreboard_lost,

            -- Columns J-N (from player, role, action)
            -- Use REPLACE to remove <br> tags if needed for clean CSV output
            REPLACE(p.pname, '<br>', ' ') AS player_name,
            REPLACE(ro.rName, '<br>', ' ') AS role_name,
            a.category AS action_category,
            REPLACE(a.aname, '<br>', ' ') AS action_name, -- Remove <br> here too
            a.score AS action_score,

            -- LAG functions to get previous rows' data within the same set (for O-Q calculation)
            LAG(a.category, 1) OVER (PARTITION BY r.sid ORDER BY r.resid) AS prev_action_category,
            LAG(REPLACE(a.aname, '<br>', ' '), 1) OVER (PARTITION BY r.sid ORDER BY r.resid) AS prev_action_name,
            LAG(REPLACE(p.pname, '<br>', ' '), 1) OVER (PARTITION BY r.sid ORDER BY r.resid) AS prev_player_name,
            LAG(a.category, 2) OVER (PARTITION BY r.sid ORDER BY r.resid) AS prev2_action_category,
            LAG(REPLACE(a.aname, '<br>', ' '), 2) OVER (PARTITION BY r.sid ORDER BY r.resid) AS prev2_action_name

        FROM
            result r
        -- Join related tables to get all necessary info
        JOIN sets s ON r.sid = s.sid
        JOIN matches m ON s.mid = m.mid
        JOIN team t ON m.tid = t.tid
        JOIN scoreboard sb ON r.resid = sb.resid -- Assumes a 1-to-1 link exists
        JOIN action a ON r.aid = a.aid
        JOIN player p ON r.pid = p.pid
        JOIN role ro ON r.rid = ro.rid
        -- OPTIONAL: Add a WHERE clause here to filter for specific matches, sets, teams, etc.
        -- Example: WHERE m.mid = 57 AND s.setNo = 1
    )
    -- Final SELECT statement to arrange columns and calculate O, P, Q
    SELECT
        -- Columns A-I
        rc.match_date,
        rc.match_type,
        rc.team_name,
        rc.team_grade,
        rc.team_rate,
        rc.set_number,
        rc.set_points,
        rc.scoreboard_scored,
        rc.scoreboard_lost,

        -- Columns J-N
        rc.player_name,
        rc.role_name,
        rc.action_category,
        rc.action_name,
        rc.action_score,

        -- Columns O-Q (Calculated using data prepared in the CTE)
        CASE
            WHEN rc.action_category = '進攻' THEN -- Attack
                CASE
                    WHEN rc.prev_action_category = 'Setting' AND rc.prev2_action_category = '一傳' THEN rc.prev2_action_name -- Receive -> Set -> Attack
                    WHEN rc.prev_action_category = '一傳' THEN rc.prev_action_name -- Receive -> Attack (less common)
                    ELSE 'NA'
                END
            WHEN rc.action_category = 'Setting' THEN -- Setting
                CASE
                    WHEN rc.prev_action_category = '一傳' THEN rc.prev_action_name -- Receive -> Set
                    ELSE 'NA'
                END
            ELSE 'NA'
        END AS receive_condition, -- Column O

        CASE
            WHEN rc.action_category = '進攻' AND rc.prev_action_category = 'Setting' THEN rc.prev_action_name -- Set -> Attack
            ELSE 'NA'
        END AS setting_condition, -- Column P

        CASE
            WHEN rc.action_category = '進攻' AND rc.prev_action_category = 'Setting' THEN rc.prev_player_name -- Set -> Attack
            ELSE 'NA'
        END AS setted_by -- Column Q

    FROM
        ResultsWithContext rc
    ORDER BY
        -- Define the final order of rows for the export
        rc.match_date, rc.mid, rc.set_number, rc.resid;
        -- Or simply ORDER BY rc.resid if you are always querying a single set.
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