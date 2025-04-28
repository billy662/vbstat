<?php
include 'conn.php';
// include 'functions.php';

// --- Input Handling & Validation ---
// ... (remains the same) ...
$match_id = isset($_GET['mid']) ? (int)$_GET['mid'] : 0;
if ($match_id <= 0) die("Invalid Match ID provided.");
$filter_set = isset($_GET['filter_set']) ? $_GET['filter_set'] : 'all';
$filter_player = isset($_GET['filter_player']) ? $_GET['filter_player'] : 'all';
$filter_category = isset($_GET['filter_category']) ? $_GET['filter_category'] : 'all';
$filter_role = isset($_GET['filter_role']) ? $_GET['filter_role'] : 'all';
$current_filters = ['filter_set' => $filter_set, 'filter_player' => $filter_player, 'filter_category' => $filter_category, 'filter_role' => $filter_role];


// --- Fetch Basic Match Info ---
// ... (remains the same) ...
$match_info = null; /* Code to fetch match info */
try {
    $sql_match = "SELECT m.date, m.type, t.tname AS opponent_team, m.tgrade, m.trate FROM matches m JOIN team t ON m.tid = t.tid WHERE m.mid = ?";
    $stmt_match = $conn->prepare($sql_match); $stmt_match->bind_param("i", $match_id); $stmt_match->execute(); $result_match = $stmt_match->get_result(); $match_info = $result_match->fetch_assoc(); $stmt_match->close();
} catch (Exception $e) { die("ERROR fetching match info: " . $e->getMessage()); }
if (!$match_info) die("Match with ID {$match_id} not found.");

// --- Fetch Data for Filters ---
// ... (remains the same - fetches sets, players, actions, categories, roles) ...
$available_sets = []; $available_players = []; $available_actions = []; $available_roles = []; $available_categories = []; $actions_by_category = []; // Also collect actions grouped by category
try {
    // Sets...
    $sql_sets = "SELECT DISTINCT s.setNo FROM sets s WHERE s.mid = ? ORDER BY s.setNo"; $stmt_sets = $conn->prepare($sql_sets); $stmt_sets->bind_param("i", $match_id); $stmt_sets->execute(); $result_sets = $stmt_sets->get_result(); while ($row = $result_sets->fetch_assoc()) { $available_sets[] = $row['setNo']; } $stmt_sets->close();
    // Players...
    $sql_players = "SELECT DISTINCT p.pid, p.pname FROM result r JOIN sets s ON r.sid = s.sid JOIN player p ON r.pid = p.pid WHERE s.mid = ? AND p.pid != 0 ORDER BY p.pname"; $stmt_players = $conn->prepare($sql_players); $stmt_players->bind_param("i", $match_id); $stmt_players->execute(); $result_players = $stmt_players->get_result(); while ($row = $result_players->fetch_assoc()) { $available_players[] = $row; } $stmt_players->close();
    // Actions, Categories, and group by category
    $sql_actions_cats = "SELECT DISTINCT a.aid, a.aname, a.category, a.sorting FROM result r JOIN sets s ON r.sid = s.sid JOIN action a ON r.aid = a.aid WHERE s.mid = ? ORDER BY a.category, a.sorting, a.aname"; $stmt_actions_cats = $conn->prepare($sql_actions_cats); $stmt_actions_cats->bind_param("i", $match_id); $stmt_actions_cats->execute(); $result_actions_cats = $stmt_actions_cats->get_result(); $categories_seen = [];
    while ($row = $result_actions_cats->fetch_assoc()) {
        $available_actions[] = $row; // Still useful
        $cat = $row['category'];
        if (!isset($categories_seen[$cat])) { $available_categories[] = $cat; $categories_seen[$cat] = true; }
        // Group for later use (pre-populating detailed stats)
        if (!isset($actions_by_category[$cat])) { $actions_by_category[$cat] = []; }
        $actions_by_category[$cat][] = $row;
    } $stmt_actions_cats->close(); sort($available_categories);
    // Roles...
    $sql_roles = "SELECT DISTINCT ro.rid, ro.rName FROM result r JOIN sets s ON r.sid = s.sid JOIN role ro ON r.rid = ro.rid WHERE s.mid = ? AND ro.rid != 7 ORDER BY ro.rName"; $stmt_roles = $conn->prepare($sql_roles); $stmt_roles->bind_param("i", $match_id); $stmt_roles->execute(); $result_roles = $stmt_roles->get_result(); while ($row = $result_roles->fetch_assoc()) { $available_roles[] = $row; } $stmt_roles->close();
} catch (Exception $e) { die("ERROR fetching filter data: " . $e->getMessage()); }


// --- Build Main Data Query with Filters ---
// ... (remains the same) ...
$sql_data = "SELECT r.resid, r.sid, r.pid, r.rid, r.aid, s.setNo, p.pname, ro.rName, a.aname, a.category, a.score, a.sorting, sb.scored, sb.lost FROM result r JOIN sets s ON r.sid = s.sid JOIN player p ON r.pid = p.pid JOIN role ro ON r.rid = ro.rid JOIN action a ON r.aid = a.aid LEFT JOIN scoreboard sb ON r.resid = sb.resid WHERE s.mid = ?";
$params = [$match_id]; $types = "i";
if ($filter_set !== 'all') { $sql_data .= " AND s.setNo = ?"; $params[] = $filter_set; $types .= "i"; }
if ($filter_player !== 'all') { $sql_data .= " AND r.pid = ?"; $params[] = $filter_player; $types .= "i"; }
if ($filter_category !== 'all') { $sql_data .= " AND a.category = ?"; $params[] = $filter_category; $types .= "s"; }
if ($filter_role !== 'all') { $sql_data .= " AND r.rid = ?"; $params[] = $filter_role; $types .= "i"; }
$sql_data .= " ORDER BY s.setNo, sb.sbid, r.resid";

// --- Fetch Filtered Data ---
// ... (remains the same) ...
$results = []; try { $stmt_data = $conn->prepare($sql_data); if (!$stmt_data) die("Prepare failed: (" . $conn->errno . ") " . $conn->error); if (!empty($params)) $stmt_data->bind_param($types, ...$params); $stmt_data->execute(); $result_data = $stmt_data->get_result(); while ($row = $result_data->fetch_assoc()) $results[] = $row; $stmt_data->close(); } catch (Exception $e) { die("ERROR fetching results: " . $e->getMessage()); }


// --- Process Data for Reports ---
$action_category_stats = []; // Overall category stats
$dawu_base_structure = ['total' => 0, '到位' => 0, '唔到位' => 0, '失分' => 0]; // For DaWu stats
$dawu_stats = [ 'serve_receive' => $dawu_base_structure, 'spike_dig' => $dawu_base_structure, 'tip_dig' => $dawu_base_structure, 'freeball' => $dawu_base_structure, 'setting' => $dawu_base_structure, 'cover' => array_merge($dawu_base_structure, ['無跟' => 0]), ]; // DaWu specific categories
$score_by_player = []; // Player scores
$score_by_role = []; // Role scores

// --- NEW: Structure for detailed category breakdowns ---
$target_detail_categories = ['攔網', '發球', '進攻'];
$detailed_category_stats = [];
// Pre-populate with all possible actions for the target categories
foreach ($target_detail_categories as $cat_name) {
    $detailed_category_stats[$cat_name] = ['total_count' => 0, 'actions' => []];
    if (isset($actions_by_category[$cat_name])) {
        // Sort actions within the category based on 'sorting' now
        usort($actions_by_category[$cat_name], function($a, $b) {
             $sortA = $a['sorting'] ?? 999; $sortB = $b['sorting'] ?? 999; return $sortA <=> $sortB;
        });
        foreach ($actions_by_category[$cat_name] as $action_details) {
            // Use cleaned name (strip tags) for key and display
            $cleaned_aname = htmlspecialchars(strip_tags($action_details['aname']), ENT_QUOTES, 'UTF-8');
            // Store count AND original sorting order (though sorting is done above)
            $detailed_category_stats[$cat_name]['actions'][$cleaned_aname] = [
                'count' => 0,
                'aid' => $action_details['aid'] // Keep aid if needed later
            ];
        }
    }
}


// DaWu Maps (remain the same)
$dawu_map_serve_receive = ['到位' => [13], '唔到位' => [14], '失分' => [15]];
$dawu_map_spike_dig = ['到位' => [35], '唔到位' => [36], '失分' => [16]];
$dawu_map_tip_dig = ['到位' => [37], '唔到位' => [38], '失分' => [39]];
$dawu_map_freeball = ['到位' => [31], '唔到位' => [32], '失分' => [40]];
$dawu_map_setting = ['到位' => [25], '唔到位' => [26], '失分' => [27]];
$dawu_map_cover = ['到位' => [17], '唔到位' => [18], '失分' => [19], '無跟' => [20]];

// DaWu helper function (remains the same)
function check_and_update_dawu(&$stats_array, $aid, $map) { if (!empty($map['到位']) && in_array($aid, $map['到位'])) { $stats_array['到位']++; $stats_array['total']++; return true; } if (!empty($map['唔到位']) && in_array($aid, $map['唔到位'])) { $stats_array['唔到位']++; $stats_array['total']++; return true; } if (!empty($map['失分']) && in_array($aid, $map['失分'])) { $stats_array['失分']++; $stats_array['total']++; return true; } if (isset($map['無跟']) && !empty($map['無跟']) && in_array($aid, $map['無跟'])) { $stats_array['無跟']++; $stats_array['total']++; return true; } return false; }


// Main processing loop
foreach ($results as $row) {
    $category = $row['category'];
    $player = htmlspecialchars(strip_tags($row['pname']), ENT_QUOTES, 'UTF-8');
    $role = htmlspecialchars(strip_tags($row['rName']), ENT_QUOTES, 'UTF-8');
    $score = (float)$row['score'];
    $aid = (int)$row['aid'];
    $aname = $row['aname']; // Original action name
    $cleaned_aname = htmlspecialchars(strip_tags($aname), ENT_QUOTES, 'UTF-8'); // Cleaned name

    // 1. Action Category Stats (Overall)
    if (!isset($action_category_stats[$category])) $action_category_stats[$category] = ['count' => 0, 'score_gain' => 0, 'score_loss' => 0, 'net_score' => 0];
    $action_category_stats[$category]['count']++;
    if ($score > 0) $action_category_stats[$category]['score_gain'] += $score; elseif ($score < 0) $action_category_stats[$category]['score_loss'] += $score; $action_category_stats[$category]['net_score'] += $score;

    // 2. DaWu Stats (Success/Failure)
    if (check_and_update_dawu($dawu_stats['serve_receive'], $aid, $dawu_map_serve_receive)) {}
    elseif (check_and_update_dawu($dawu_stats['spike_dig'], $aid, $dawu_map_spike_dig)) {}
    elseif (check_and_update_dawu($dawu_stats['tip_dig'], $aid, $dawu_map_tip_dig)) {}
    elseif (check_and_update_dawu($dawu_stats['freeball'], $aid, $dawu_map_freeball)) {}
    elseif (check_and_update_dawu($dawu_stats['setting'], $aid, $dawu_map_setting)) {}
    elseif (check_and_update_dawu($dawu_stats['cover'], $aid, $dawu_map_cover)) {}

    // 3. Score by Player
    if ($player !== '對方<br>球員') { if (!isset($score_by_player[$player])) $score_by_player[$player] = ['score_gain' => 0, 'score_loss' => 0, 'net_score' => 0]; if ($score > 0) $score_by_player[$player]['score_gain'] += $score; elseif ($score < 0) $score_by_player[$player]['score_loss'] += $score; $score_by_player[$player]['net_score'] += $score; }

    // 4. Score by Role
     if ($role !== '對方<br>球員') { if (!isset($score_by_role[$role])) $score_by_role[$role] = ['score_gain' => 0, 'score_loss' => 0, 'net_score' => 0]; if ($score > 0) $score_by_role[$role]['score_gain'] += $score; elseif ($score < 0) $score_by_role[$role]['score_loss'] += $score; $score_by_role[$role]['net_score'] += $score; }

    // --- NEW: Populate detailed category stats ---
    if (in_array($category, $target_detail_categories)) {
        $detailed_category_stats[$category]['total_count']++;
        // Increment the count for the specific action using the cleaned name
        if (isset($detailed_category_stats[$category]['actions'][$cleaned_aname])) {
            $detailed_category_stats[$category]['actions'][$cleaned_aname]['count']++;
        }
        // Note: No 'else' needed if pre-population worked correctly.
        // Could add an error log here if $cleaned_aname is not found, indicating a potential issue.
    }
}

// --- Helper Functions ---
function calculate_percentage($part, $total) { return ($total > 0) ? round(($part / $total) * 100, 1) : 0; }
// build_filter_link function remains the same
function build_filter_link(int $match_id, string $key, $value, array $current_filters): string { $params = ['mid' => $match_id]; $link_filters = $current_filters; $link_filters[$key] = $value; foreach ($link_filters as $filter_key => $filter_value) { if ($filter_value !== 'all') $params[$filter_key] = $filter_value; } return 'report.php?' . http_build_query($params); }

// --- Close Database Connection ---
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Report - Match ID: <?php echo $match_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* ... CSS remains the same ... */
         body { padding: 20px; } h2, h3 { margin-top: 20px; border-bottom: 1px solid #ccc; padding-bottom: 5px; } .table th { white-space: nowrap; } .table td.numeric, .table th.numeric { text-align: right; } .positive { color: green; } .negative { color: red; } .neutral { color: grey; } th, td { word-wrap: break-word; white-space: normal !important; } .table th, .table td { vertical-align: middle; } .filter-group { margin-bottom: 15px; } .filter-group-label { font-weight: bold; margin-right: 10px; display: block; margin-bottom: 5px; } .filter-group .btn-group { display: flex; flex-wrap: wrap; gap: 5px; } .filter-group .btn { margin-bottom: 5px; } h4 { margin-top: 15px; font-weight: bold; color: #333; }

        /* Add navbar styles */
        .navbar {
            padding: 0px;
        }

        .navbar-brand {
            font-size: inherit;
        }

        .back {
            font-size: 1.2em;
            transition: all 0.3s ease;
        }
        
        .back:hover {
            transform: scale(1.1);
        }

        /* Adjust body padding for navbar */
        body {
            padding: 5px;
        }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
        <div class="container-fluid">
            <a class="back navbar-brand" href="set.php?mid=<?php echo $match_id; ?>">⬅</a>
            <a class="navbar-brand ms-auto">
                <?php
                    if($match_info) {
                        echo htmlspecialchars($match_info['date']) . " " . 
                             htmlspecialchars($match_info['type']) . " VS " . 
                             htmlspecialchars($match_info['opponent_team']);
                    }
                ?>
            </a>
        </div>
    </nav>

    <div class="container">
        <h1 class="text-light">Match Report</h1>
        <!-- Match Details -->
        <div class="alert alert-info bg-dark text-info border-info"><strong>Match Details:</strong> Date: <?php echo htmlspecialchars($match_info['date']); ?> | Type: <?php echo htmlspecialchars($match_info['type']); ?> | Opponent: <?php echo htmlspecialchars($match_info['opponent_team']); ?> (Grade: <?php echo htmlspecialchars($match_info['tgrade']); ?>, Rate: <?php echo htmlspecialchars($match_info['trate']); ?>)</div>

        <h2 class="text-light">Filters</h2>
        <!-- Filter Buttons (Set, Player, Category, Role) -->
        <!-- ... (Filter button HTML remains the same) ... -->
         <div class="filter-group"><span class="filter-group-label">Set:</span><div class="btn-group" role="group"><?php $link = build_filter_link($match_id, 'filter_set', 'all', $current_filters); $active_class = ($filter_set === 'all') ? 'active' : ''; ?><a href="<?php echo $link; ?>" class="btn btn-sm btn-outline-primary <?php echo $active_class; ?>">All Sets</a><?php foreach ($available_sets as $set_num): ?><?php $link = build_filter_link($match_id, 'filter_set', $set_num, $current_filters); $active_class = ($filter_set == $set_num) ? 'active' : ''; ?><a href="<?php echo $link; ?>" class="btn btn-sm btn-outline-primary <?php echo $active_class; ?>">Set <?php echo $set_num; ?></a><?php endforeach; ?></div></div>
         <div class="filter-group"><span class="filter-group-label">Player:</span><div class="btn-group" role="group"><?php $link = build_filter_link($match_id, 'filter_player', 'all', $current_filters); $active_class = ($filter_player === 'all') ? 'active' : ''; ?><a href="<?php echo $link; ?>" class="btn btn-sm btn-outline-warning <?php echo $active_class; ?>">All Players</a><?php foreach ($available_players as $player): ?><?php $link = build_filter_link($match_id, 'filter_player', $player['pid'], $current_filters); $active_class = ($filter_player == $player['pid']) ? 'active' : ''; ?><a href="<?php echo $link; ?>" class="btn btn-sm btn-outline-warning <?php echo $active_class; ?>"><?php echo htmlspecialchars(str_replace('<br>', ' ', $player['pname']), ENT_QUOTES, 'UTF-8'); ?></a><?php endforeach; ?></div></div>
         <div class="filter-group"><span class="filter-group-label">Action Category:</span><div class="btn-group" role="group"><?php $link = build_filter_link($match_id, 'filter_category', 'all', $current_filters); $active_class = ($filter_category === 'all') ? 'active' : ''; ?><a href="<?php echo $link; ?>" class="btn btn-sm btn-outline-info <?php echo $active_class; ?>">All Categories</a><?php foreach ($available_categories as $category_name): ?><?php $link = build_filter_link($match_id, 'filter_category', $category_name, $current_filters); $active_class = ($filter_category == $category_name) ? 'active' : ''; ?><a href="<?php echo $link; ?>" class="btn btn-sm btn-outline-info <?php echo $active_class; ?>"><?php echo htmlspecialchars($category_name); ?></a><?php endforeach; ?></div></div>
         <div class="filter-group"><span class="filter-group-label">Role:</span><div class="btn-group" role="group"><?php $link = build_filter_link($match_id, 'filter_role', 'all', $current_filters); $active_class = ($filter_role === 'all') ? 'active' : ''; ?><a href="<?php echo $link; ?>" class="btn btn-sm btn-outline-light <?php echo $active_class; ?>">All Roles</a><?php foreach ($available_roles as $role): ?><?php $link = build_filter_link($match_id, 'filter_role', $role['rid'], $current_filters); $active_class = ($filter_role == $role['rid']) ? 'active' : ''; ?><a href="<?php echo $link; ?>" class="btn btn-sm btn-outline-light <?php echo $active_class; ?>"><?php echo htmlspecialchars(str_replace('<br>', ' ', $role['rName']), ENT_QUOTES, 'UTF-8'); ?></a><?php endforeach; ?></div></div>
        <hr>

        <h2 class="text-light">Statistics Summary</h2>

        <!-- Action Stats by Category (Overall) -->
        <h3 class="text-light">Action Stats by Category</h3>
        <div class="table-responsive">
           <table class="table table-dark table-striped table-bordered table-sm">
               <thead><tr><th>Category</th><th class="numeric">Count</th><th class="numeric">Score Gain</th><th class="numeric">Score Loss</th><th class="numeric">Net Score</th></tr></thead>
               <tbody>
                   <?php ksort($action_category_stats); ?>
                   <?php foreach ($action_category_stats as $category => $stats): ?>
                   <tr><td><?php echo htmlspecialchars($category); ?></td><td class="numeric"><?php echo $stats['count']; ?></td><td class="numeric positive"><?php echo number_format($stats['score_gain'], 1); ?></td><td class="numeric negative"><?php echo number_format($stats['score_loss'], 1); ?></td><td class="numeric <?php echo ($stats['net_score'] > 0) ? 'positive' : (($stats['net_score'] < 0) ? 'negative' : 'neutral'); ?>"><?php echo number_format($stats['net_score'], 1); ?></td></tr>
                   <?php endforeach; ?>
                   <?php if (empty($action_category_stats)): ?><tr><td colspan="5">No data matching filters.</td></tr><?php endif; ?>
               </tbody>
           </table>
       </div>

        <!-- Success / Failure Rates (Detailed DaWu) -->
        <h3 class="text-light">到位 / 唔到位</h3>
        <div class="table-responsive">
            <table class="table table-dark table-striped table-bordered table-sm">
                <thead><tr><th>Action Type</th><th class="numeric">Total Attempts</th><th class="numeric">到位</th><th class="numeric">到位 %</th><th class="numeric">唔到位</th><th class="numeric">唔到位 %</th><th class="numeric">失分</th><th class="numeric">失分 %</th><?php $has_mugan = $dawu_stats['cover']['無跟'] > 0; if ($has_mugan) echo '<th class="numeric">無跟 Cover</th><th class="numeric">無跟 Cover %</th>'; $colspan = $has_mugan ? 10 : 8; ?></tr></thead>
                 <tbody>
                    <?php function renderDaWuRow($label, $stats, $include_extra = false) { if ($stats['total'] == 0) return; echo "<tr><td>{$label}</td><td class='numeric'>{$stats['total']}</td><td class='numeric'>{$stats['到位']}</td><td class='numeric'>" . calculate_percentage($stats['到位'], $stats['total']) . "%</td><td class='numeric'>{$stats['唔到位']}</td><td class='numeric'>" . calculate_percentage($stats['唔到位'], $stats['total']) . "%</td><td class='numeric'>{$stats['失分']}</td><td class='numeric negative'>" . calculate_percentage($stats['失分'], $stats['total']) . "%</td>"; if ($include_extra && isset($stats['無跟'])) { echo "<td class='numeric'>{$stats['無跟']}</td><td class='numeric negative'>" . calculate_percentage($stats['無跟'], $stats['total']) . "%</td>"; } elseif ($include_extra) { echo "<td class='numeric'>0</td><td class='numeric negative'>0%</td>"; } echo "</tr>"; } ?>
                    <?php renderDaWuRow('接發', $dawu_stats['serve_receive'], $has_mugan); renderDaWuRow('守殺', $dawu_stats['spike_dig'], $has_mugan); renderDaWuRow('守tip', $dawu_stats['tip_dig'], $has_mugan); renderDaWuRow('Free波', $dawu_stats['freeball'], $has_mugan); renderDaWuRow('Setting', $dawu_stats['setting'], $has_mugan); renderDaWuRow('Cover', $dawu_stats['cover'], $has_mugan); ?>
                    <?php $no_dawu_data = true; foreach ($dawu_stats as $stats) if ($stats['total'] > 0) $no_dawu_data = false; if ($no_dawu_data): ?><tr><td colspan="<?php echo $colspan; ?>">No relevant actions matching filters.</td></tr><?php endif; ?>
                 </tbody>
            </table>
        </div>

        <!-- --- NEW: Detailed Action Breakdowns --- -->
        <h3 class="text-light">Detailed Action Breakdowns</h3>
        <?php foreach ($detailed_category_stats as $category_name => $category_data): ?>
            <?php if ($category_data['total_count'] > 0): // Only show table if there are actions in this category ?>
                <h4 class="text-light"><?php echo htmlspecialchars($category_name); ?></h4>
                <div class="table-responsive">
                    <table class="table table-dark table-striped table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th class="numeric">Count</th>
                                <th class="numeric">Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($category_data['actions'] as $action_name => $action_data): ?>
                                <?php // $action_name is already htmlspecialchars encoded ?>
                                <tr>
                                    <td><?php echo $action_name; ?></td>
                                    <td class="numeric"><?php echo $action_data['count']; ?></td>
                                    <td class="numeric"><?php echo calculate_percentage($action_data['count'], $category_data['total_count']); ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="table-group-divider">
                                <th>Total</th>
                                <td class="numeric"><strong><?php echo $category_data['total_count']; ?></strong></td>
                                <td class="numeric"><strong>100.0%</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($filter_category === 'all' || $filter_category === $category_name ): ?>
                <h4 class="text-light"><?php echo htmlspecialchars($category_name); ?></h4>
                <p class="text-light">No '<?php echo htmlspecialchars($category_name); ?>' actions recorded matching the current filters.</p>
            <?php endif; ?>
        <?php endforeach; ?>
        <!-- End of Detailed Breakdowns -->


        <!-- Score by Player -->
        <h3 class="text-light">Score Contribution by Player</h3>
        <div class="table-responsive">
            <table class="table table-dark table-striped table-bordered table-sm">
                 <thead><tr><th>Player</th><th class="numeric">Score Gain</th><th class="numeric">Score Loss</th><th class="numeric">Net Score</th></tr></thead>
                 <tbody>
                    <?php uksort($score_by_player, function($a, $b) use ($score_by_player) { return $score_by_player[$b]['net_score'] <=> $score_by_player[$a]['net_score']; }); ?>
                    <?php foreach ($score_by_player as $player => $stats): ?>
                    <tr><td><?php echo $player; ?></td><td class="numeric positive"><?php echo number_format($stats['score_gain'], 1); ?></td><td class="numeric negative"><?php echo number_format($stats['score_loss'], 1); ?></td><td class="numeric <?php echo ($stats['net_score'] > 0) ? 'positive' : (($stats['net_score'] < 0) ? 'negative' : 'neutral'); ?>"><?php echo number_format($stats['net_score'], 1); ?></td></tr>
                    <?php endforeach; ?>
                    <?php if (empty($score_by_player)): ?><tr><td colspan="4">No player scoring data matching filters.</td></tr><?php endif; ?>
                 </tbody>
            </table>
        </div>

        <!-- Score by Role -->
        <h3 class="text-light">Score Contribution by Role</h3>
         <div class="table-responsive">
            <table class="table table-dark table-striped table-bordered table-sm">
                 <thead><tr><th>Role</th><th class="numeric">Score Gain</th><th class="numeric">Score Loss</th><th class="numeric">Net Score</th></tr></thead>
                 <tbody>
                     <?php uksort($score_by_role, function($a, $b) use ($score_by_role) { return $score_by_role[$b]['net_score'] <=> $score_by_role[$a]['net_score']; }); ?>
                    <?php foreach ($score_by_role as $role => $stats): ?>
                    <tr><td><?php echo $role; ?></td><td class="numeric positive"><?php echo number_format($stats['score_gain'], 1); ?></td><td class="numeric negative"><?php echo number_format($stats['score_loss'], 1); ?></td><td class="numeric <?php echo ($stats['net_score'] > 0) ? 'positive' : (($stats['net_score'] < 0) ? 'negative' : 'neutral'); ?>"><?php echo number_format($stats['net_score'], 1); ?></td></tr>
                    <?php endforeach; ?>
                     <?php if (empty($score_by_role)): ?><tr><td colspan="4">No role scoring data matching filters.</td></tr><?php endif; ?>
                 </tbody>
            </table>
        </div>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>