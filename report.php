<?php
// =============================================================================
// Configuration & Includes
// =============================================================================
include 'conn.php'; // Database connection
// include 'functions.php'; // Keep commented out as requested

// =============================================================================
// Helper Functions
// =============================================================================

/**
 * Calculates the percentage of a part relative to a total.
 *
 * @param float|int $part The part value.
 * @param float|int $total The total value.
 * @return float The calculated percentage, rounded to 1 decimal place. Returns 0 if total is 0.
 */
function calculate_percentage($part, $total): float {
    return ($total > 0) ? round(($part / $total) * 100, 1) : 0;
}

/**
 * Builds a URL query string for filter links.
 *
 * @param int $match_id The current match ID.
 * @param string $key The filter key to modify (e.g., 'filter_set').
 * @param mixed $value The new value for the filter key.
 * @param array $current_filters An array of the currently active filters.
 * @return string The generated URL with query parameters.
 */
function build_filter_link(int $match_id, string $key, $value, array $current_filters): string {
    $params = ['mid' => $match_id];
    $link_filters = $current_filters;
    $link_filters[$key] = $value;
    foreach ($link_filters as $filter_key => $filter_value) {
        if ($filter_value !== 'all') {
            $params[$filter_key] = $filter_value;
        }
    }
    return 'report.php?' . http_build_query($params);
}

/**
 * Checks if an action ID corresponds to a DaWu category and updates the stats.
 *
 * @param array &$stats_array The specific DaWu stats array to update (passed by reference).
 * @param int $aid The action ID to check.
 * @param array $map The mapping of DaWu outcomes (到位, 唔到位, etc.) to action IDs.
 * @return bool True if the action ID matched and stats were updated, false otherwise.
 */
function check_and_update_dawu(array &$stats_array, int $aid, array $map): bool {
    if (!empty($map['到位']) && in_array($aid, $map['到位'])) {
        $stats_array['到位']++; $stats_array['total']++; return true;
    }
    if (!empty($map['唔到位']) && in_array($aid, $map['唔到位'])) {
        $stats_array['唔到位']++; $stats_array['total']++; return true;
    }
    if (!empty($map['失分']) && in_array($aid, $map['失分'])) {
        $stats_array['失分']++; $stats_array['total']++; return true;
    }
    // Handle '無跟' specifically for cover
    if (isset($map['無跟']) && !empty($map['無跟']) && in_array($aid, $map['無跟'])) {
        $stats_array['無跟']++; $stats_array['total']++; return true;
    }
    return false;
}

/**
 * Fetches basic information about the match.
 *
 * @param mysqli $conn The database connection object.
 * @param int $match_id The ID of the match.
 * @return array|null An associative array with match info or null if not found/error.
 */
function fetchMatchInfo(mysqli $conn, int $match_id): ?array {
    try {
        $sql = "SELECT m.date, m.type, t.tname AS opponent_team, m.tgrade, m.trate
                FROM matches m
                JOIN team t ON m.tid = t.tid
                WHERE m.mid = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        $stmt->bind_param("i", $match_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $match_info = $result->fetch_assoc();
        $stmt->close();
        return $match_info;
    } catch (Exception $e) {
        // In a real app, log this error instead of dying
        error_log("ERROR fetching match info for mid {$match_id}: " . $e->getMessage());
        die("ERROR fetching match info: " . htmlspecialchars($e->getMessage())); // Keep die for now as per original
    }
}

/**
 * Fetches data needed for the filter dropdowns/buttons.
 *
 * @param mysqli $conn The database connection object.
 * @param int $match_id The ID of the match.
 * @return array An array containing available sets, players, actions, categories, roles, and actions grouped by category.
 */
function fetchFilterData(mysqli $conn, int $match_id): array {
    $data = [
        'available_sets' => [],
        'available_players' => [],
        'available_actions' => [],
        'available_categories' => [],
        'available_roles' => [],
        'actions_by_category' => []
    ];

    try {
        // Sets
        $sql_sets = "SELECT DISTINCT s.setNo FROM sets s WHERE s.mid = ? ORDER BY s.setNo";
        $stmt_sets = $conn->prepare($sql_sets); $stmt_sets->bind_param("i", $match_id); $stmt_sets->execute(); $result_sets = $stmt_sets->get_result();
        while ($row = $result_sets->fetch_assoc()) { $data['available_sets'][] = $row['setNo']; } $stmt_sets->close();

        // Players
        $sql_players = "SELECT DISTINCT p.pid, p.pname FROM result r JOIN sets s ON r.sid = s.sid JOIN player p ON r.pid = p.pid WHERE s.mid = ? AND p.pid != 0 ORDER BY p.pname";
        $stmt_players = $conn->prepare($sql_players); $stmt_players->bind_param("i", $match_id); $stmt_players->execute(); $result_players = $stmt_players->get_result();
        while ($row = $result_players->fetch_assoc()) { $data['available_players'][] = $row; } $stmt_players->close();

        // Actions, Categories, and group by category
        $sql_actions_cats = "SELECT DISTINCT a.aid, a.aname, a.category, a.sorting FROM result r JOIN sets s ON r.sid = s.sid JOIN action a ON r.aid = a.aid WHERE s.mid = ? ORDER BY a.category, a.sorting, a.aname";
        $stmt_actions_cats = $conn->prepare($sql_actions_cats); $stmt_actions_cats->bind_param("i", $match_id); $stmt_actions_cats->execute(); $result_actions_cats = $stmt_actions_cats->get_result();
        $categories_seen = [];
        while ($row = $result_actions_cats->fetch_assoc()) {
            $data['available_actions'][] = $row;
            $cat = $row['category'];
            if (!isset($categories_seen[$cat])) { $data['available_categories'][] = $cat; $categories_seen[$cat] = true; }
            if (!isset($data['actions_by_category'][$cat])) { $data['actions_by_category'][$cat] = []; }
            $data['actions_by_category'][$cat][] = $row;
        }
        $stmt_actions_cats->close();
        sort($data['available_categories']);

        // Roles
        $sql_roles = "SELECT DISTINCT ro.rid, ro.rName FROM result r JOIN sets s ON r.sid = s.sid JOIN role ro ON r.rid = ro.rid WHERE s.mid = ? AND ro.rid != 7 ORDER BY ro.rName";
        $stmt_roles = $conn->prepare($sql_roles); $stmt_roles->bind_param("i", $match_id); $stmt_roles->execute(); $result_roles = $stmt_roles->get_result();
        while ($row = $result_roles->fetch_assoc()) { $data['available_roles'][] = $row; } $stmt_roles->close();

    } catch (Exception $e) {
        error_log("ERROR fetching filter data for mid {$match_id}: " . $e->getMessage());
        die("ERROR fetching filter data: " . htmlspecialchars($e->getMessage()));
    }
    return $data;
}

/**
 * Fetches the main result data based on applied filters.
 *
 * @param mysqli $conn The database connection object.
 * @param int $match_id The ID of the match.
 * @param array $filters An array containing the current filter values.
 * @return array An array of result rows.
 */
function fetchFilteredResults(mysqli $conn, int $match_id, array $filters): array {
    $sql = "SELECT r.resid, r.sid, r.pid, r.rid, r.aid, s.setNo, p.pname, ro.rName, a.aname, a.category, a.score, a.sorting, sb.scored, sb.lost
            FROM result r
            JOIN sets s ON r.sid = s.sid
            JOIN player p ON r.pid = p.pid
            JOIN role ro ON r.rid = ro.rid
            JOIN action a ON r.aid = a.aid
            LEFT JOIN scoreboard sb ON r.resid = sb.resid
            WHERE s.mid = ?";
    $params = [$match_id];
    $types = "i";

    if ($filters['filter_set'] !== 'all') { $sql .= " AND s.setNo = ?"; $params[] = $filters['filter_set']; $types .= "i"; }
    if ($filters['filter_player'] !== 'all') { $sql .= " AND r.pid = ?"; $params[] = $filters['filter_player']; $types .= "i"; }
    if ($filters['filter_category'] !== 'all') { $sql .= " AND a.category = ?"; $params[] = $filters['filter_category']; $types .= "s"; }
    if ($filters['filter_role'] !== 'all') { $sql .= " AND r.rid = ?"; $params[] = $filters['filter_role']; $types .= "i"; }

    $sql .= " ORDER BY s.setNo, sb.sbid, r.resid";

    $results = [];
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) throw new Exception("Prepare failed: (" . $conn->errno . ") " . $conn->error);
        if (!empty($params)) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result_data = $stmt->get_result();
        while ($row = $result_data->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
    } catch (Exception $e) {
        error_log("ERROR fetching results for mid {$match_id} with filters " . json_encode($filters) . ": " . $e->getMessage());
        die("ERROR fetching results: " . htmlspecialchars($e->getMessage()));
    }
    return $results;
}

/**
 * Processes the fetched results to calculate various statistics.
 *
 * @param array $results The array of result rows from fetchFilteredResults.
 * @param array $actions_by_category Actions grouped by category from fetchFilterData.
 * @return array An array containing all calculated statistics.
 */
function processResults(array $results, array $actions_by_category): array {
    // --- Initialization ---
    $stats = [
        'action_category_stats' => [],
        'dawu_stats' => [
            'serve_receive' => ['total' => 0, '到位' => 0, '唔到位' => 0, '失分' => 0],
            'spike_dig'     => ['total' => 0, '到位' => 0, '唔到位' => 0, '失分' => 0],
            'tip_dig'       => ['total' => 0, '到位' => 0, '唔到位' => 0, '失分' => 0],
            'freeball'      => ['total' => 0, '到位' => 0, '唔到位' => 0, '失分' => 0],
            'setting'       => ['total' => 0, '到位' => 0, '唔到位' => 0, '失分' => 0],
            'cover'         => ['total' => 0, '到位' => 0, '唔到位' => 0, '失分' => 0, '無跟' => 0],
        ],
        'score_by_player' => [],
        'score_by_role' => [],
        'detailed_category_stats' => [],
        'player_attack_stats' => [], // Added for Attack Efficiency
        'player_serve_stats' => [],  // Added for Serve Performance
        'player_block_stats' => [],  // Added for Block Performance
        'team_scoring_sources' => ['total_scored' => 0, 'attack_kills' => 0, 'block_points' => 0, 'aces' => 0, 'opponent_errors' => 0],
        'team_error_sources' => ['total_lost' => 0, 'attack_errors' => 0, 'serve_errors' => 0, 'reception_errors' => 0, 'block_errors' => 0, 'setting_errors' => 0, 'cover_errors' => 0, 'other_errors' => 0]
    ];

    // DaWu Maps
    $dawu_map_serve_receive = ['到位' => [13], '唔到位' => [14], '失分' => [15]];
    $dawu_map_spike_dig = ['到位' => [35], '唔到位' => [36], '失分' => [16]];
    $dawu_map_tip_dig = ['到位' => [37], '唔到位' => [38], '失分' => [39]];
    $dawu_map_freeball = ['到位' => [31], '唔到位' => [32], '失分' => [40]];
    $dawu_map_setting = ['到位' => [25], '唔到位' => [26], '失分' => [27]];
    $dawu_map_cover = ['到位' => [17], '唔到位' => [18], '失分' => [19], '無跟' => [20]];

    // Pre-populate detailed stats structure
    $target_detail_categories = ['攔網', '發球', '進攻'];
    foreach ($target_detail_categories as $cat_name) {
        $stats['detailed_category_stats'][$cat_name] = ['total_count' => 0, 'actions' => []];
        if (isset($actions_by_category[$cat_name])) {
            // Sort actions within the category based on 'sorting'
            usort($actions_by_category[$cat_name], function($a, $b) {
                 $sortA = $a['sorting'] ?? 999; $sortB = $b['sorting'] ?? 999; return $sortA <=> $sortB;
            });
            foreach ($actions_by_category[$cat_name] as $action_details) {
                $cleaned_aname = htmlspecialchars(strip_tags($action_details['aname']), ENT_QUOTES, 'UTF-8');
                $stats['detailed_category_stats'][$cat_name]['actions'][$cleaned_aname] = [
                    'count' => 0,
                    'aid' => $action_details['aid']
                ];
            }
        }
    }

    // --- Main Processing Loop ---
    foreach ($results as $row) {
        $category = $row['category'];
        $player = htmlspecialchars(strip_tags($row['pname']), ENT_QUOTES, 'UTF-8');
        $role = htmlspecialchars(strip_tags($row['rName']), ENT_QUOTES, 'UTF-8');
        $score = (float)$row['score'];
        $aid = (int)$row['aid'];
        $aname = $row['aname']; // Original action name
        $cleaned_aname = htmlspecialchars(strip_tags($aname), ENT_QUOTES, 'UTF-8'); // Cleaned name

        // 1. Action Category Stats (Overall)
        if (!isset($stats['action_category_stats'][$category])) {
            $stats['action_category_stats'][$category] = ['count' => 0, 'score_gain' => 0, 'score_loss' => 0, 'net_score' => 0];
        }
        $stats['action_category_stats'][$category]['count']++;
        if ($score > 0) $stats['action_category_stats'][$category]['score_gain'] += $score;
        elseif ($score < 0) $stats['action_category_stats'][$category]['score_loss'] += $score;
        $stats['action_category_stats'][$category]['net_score'] += $score;

        // 2. DaWu Stats (Success/Failure)
        if (check_and_update_dawu($stats['dawu_stats']['serve_receive'], $aid, $dawu_map_serve_receive)) {}
        elseif (check_and_update_dawu($stats['dawu_stats']['spike_dig'], $aid, $dawu_map_spike_dig)) {}
        elseif (check_and_update_dawu($stats['dawu_stats']['tip_dig'], $aid, $dawu_map_tip_dig)) {}
        elseif (check_and_update_dawu($stats['dawu_stats']['freeball'], $aid, $dawu_map_freeball)) {}
        elseif (check_and_update_dawu($stats['dawu_stats']['setting'], $aid, $dawu_map_setting)) {}
        elseif (check_and_update_dawu($stats['dawu_stats']['cover'], $aid, $dawu_map_cover)) {}

        // 3. Score by Player
        if ($player !== '對方<br>球員') { // Exclude opponent
            if (!isset($stats['score_by_player'][$player])) {
                $stats['score_by_player'][$player] = ['score_gain' => 0, 'score_loss' => 0, 'net_score' => 0];
            }
            if ($score > 0) $stats['score_by_player'][$player]['score_gain'] += $score;
            elseif ($score < 0) $stats['score_by_player'][$player]['score_loss'] += $score;
            $stats['score_by_player'][$player]['net_score'] += $score;
        }

        // 4. Score by Role
         if ($role !== '對方<br>球員') { // Exclude opponent role
            if (!isset($stats['score_by_role'][$role])) {
                $stats['score_by_role'][$role] = ['score_gain' => 0, 'score_loss' => 0, 'net_score' => 0];
            }
            if ($score > 0) $stats['score_by_role'][$role]['score_gain'] += $score;
            elseif ($score < 0) $stats['score_by_role'][$role]['score_loss'] += $score;
            $stats['score_by_role'][$role]['net_score'] += $score;
         }

        // 5. Detailed Category Stats
        if (in_array($category, $target_detail_categories)) {
            $stats['detailed_category_stats'][$category]['total_count']++;
            if (isset($stats['detailed_category_stats'][$category]['actions'][$cleaned_aname])) {
                $stats['detailed_category_stats'][$category]['actions'][$cleaned_aname]['count']++;
            } else {
                // Log potential issue: Action found in results but not pre-populated
                error_log("Warning: Action '{$cleaned_aname}' (AID: {$aid}) in category '{$category}' was not found in pre-populated detailed stats for mid {$GLOBALS['match_id']}.");
                // Optionally add it dynamically, though pre-population should handle this
                // $stats['detailed_category_stats'][$category]['actions'][$cleaned_aname] = ['count' => 1, 'aid' => $aid];
            }
        }

        // --- Player Specific Stats Calculation (Inside Loop) ---
        if ($player !== '對方<br>球員') { // Exclude opponent
            // Initialize player arrays if not set
            if (!isset($stats['player_attack_stats'][$player])) {
                $stats['player_attack_stats'][$player] = ['kills' => 0, 'errors' => 0, 'attempts' => 0];
            }
            if (!isset($stats['player_serve_stats'][$player])) {
                $stats['player_serve_stats'][$player] = ['aces' => 0, 'errors' => 0, 'attempts' => 0];
            }
            if (!isset($stats['player_block_stats'][$player])) {
                $stats['player_block_stats'][$player] = ['points' => 0, 'errors' => 0, 'effective' => 0, 'attempts' => 0]; // Added attempts for context
            }

            // Attack Stats
            if ($category === '進攻') {
                if (in_array($aid, [1, 2, 3])) { // Kills: 殺波, Tip波, 二段
                    $stats['player_attack_stats'][$player]['kills']++;
                    $stats['player_attack_stats'][$player]['attempts']++;
                } elseif ($aid === 5) { // Error: 進攻失分
                    $stats['player_attack_stats'][$player]['errors']++;
                    $stats['player_attack_stats'][$player]['attempts']++;
                } elseif (in_array($aid, [6, 34])) { // Other attempts: 破壞性進攻, 無效進攻
                    $stats['player_attack_stats'][$player]['attempts']++;
                }
            }

            // Serve Stats
            if ($category === '發球') {
                if ($aid === 21) { // Ace
                    $stats['player_serve_stats'][$player]['aces']++;
                    $stats['player_serve_stats'][$player]['attempts']++;
                } elseif ($aid === 23) { // Error: 發球失分
                    $stats['player_serve_stats'][$player]['errors']++;
                    $stats['player_serve_stats'][$player]['attempts']++;
                } elseif (in_array($aid, [22, 30])) { // Other attempts: 發球, 發球破壞一傳
                    $stats['player_serve_stats'][$player]['attempts']++;
                }
            }

            // Block Stats
            if ($category === '攔網') {
                 if ($aid === 7) { // Point: 攔網+
                    $stats['player_block_stats'][$player]['points']++;
                    $stats['player_block_stats'][$player]['attempts']++;
                 } elseif ($aid === 9) { // Error: 攔網-
                    $stats['player_block_stats'][$player]['errors']++;
                    $stats['player_block_stats'][$player]['attempts']++;
                 } elseif ($aid === 10) { // Effective: 有效攔網
                    $stats['player_block_stats'][$player]['effective']++;
                    $stats['player_block_stats'][$player]['attempts']++;
                 } elseif (in_array($aid, [8, 11, 33])) { // Other block involvement: 多人攔網, 無效攔網 etc. - count as attempt? Debatable, let's count for now.
                    $stats['player_block_stats'][$player]['attempts']++;
                 }
            }
        } // End player specific stats

        // --- Team Stats Calculation (Inside Loop) ---
        if ($score > 0) {
            $stats['team_scoring_sources']['total_scored'] += $score;
            if (in_array($aid, [1, 2, 3])) { // Attack Kills
                $stats['team_scoring_sources']['attack_kills'] += $score;
            } elseif ($aid === 7) { // Block Points (assuming aid 7 is the primary block point)
                 // Handle potential half points from multi-blocks (aid 8) if needed - currently only counting aid 7
                 $stats['team_scoring_sources']['block_points'] += $score;
            } elseif ($aid === 21) { // Aces
                $stats['team_scoring_sources']['aces'] += $score;
            } elseif ($aid === 29) { // Opponent Error
                $stats['team_scoring_sources']['opponent_errors'] += $score;
            }
            // Note: Points from actions like '一傳過網' (aid 12) might need specific categorization if they should be counted separately. Currently implicitly included via score > 0.
        } elseif ($score < 0) {
            $stats['team_error_sources']['total_lost'] += abs($score); // Use absolute value for lost points count
            if ($aid === 5) { // Attack Error
                $stats['team_error_sources']['attack_errors'] += abs($score);
            } elseif ($aid === 23) { // Serve Error
                $stats['team_error_sources']['serve_errors'] += abs($score);
            } elseif (in_array($aid, [15, 16, 39, 40])) { // Reception Errors (接發失分, 守殺失分, 守tip失分, Free波失分)
                $stats['team_error_sources']['reception_errors'] += abs($score);
            } elseif ($aid === 9) { // Block Error
                $stats['team_error_sources']['block_errors'] += abs($score);
            } elseif ($aid === 27) { // Setting Error
                $stats['team_error_sources']['setting_errors'] += abs($score);
            } elseif (in_array($aid, [19, 20])) { // Cover Errors (Cover失分, 無跟cover)
                $stats['team_error_sources']['cover_errors'] += abs($score);
            } elseif ($aid === 28) { // Other Error
                $stats['team_error_sources']['other_errors'] += abs($score);
            }
            // Note: Need to ensure all negative score actions are categorized.
        }

    } // End main processing loop

    // --- Post-processing Calculations (Efficiency, Rates, Percentages) --- (Outside Loop)
    foreach ($stats['player_attack_stats'] as $player => &$p_stats) {
        $p_stats['efficiency'] = ($p_stats['attempts'] > 0)
            ? round((($p_stats['kills'] - $p_stats['errors']) / $p_stats['attempts']) * 100, 1)
            : 0;
    }
    unset($p_stats); // Unset reference

    foreach ($stats['player_serve_stats'] as $player => &$p_stats) {
        $p_stats['ace_rate'] = calculate_percentage($p_stats['aces'], $p_stats['attempts']);
        $p_stats['error_rate'] = calculate_percentage($p_stats['errors'], $p_stats['attempts']);
    }
    unset($p_stats);

    // Calculate Team Stat Percentages
    foreach ($stats['team_scoring_sources'] as $key => $value) {
        if ($key !== 'total_scored' && $stats['team_scoring_sources']['total_scored'] > 0) {
            $stats['team_scoring_sources'][$key . '_perc'] = calculate_percentage($value, $stats['team_scoring_sources']['total_scored']);
        } elseif ($key !== 'total_scored') {
             $stats['team_scoring_sources'][$key . '_perc'] = 0;
        }
    }
     foreach ($stats['team_error_sources'] as $key => $value) {
        if ($key !== 'total_lost' && $stats['team_error_sources']['total_lost'] > 0) {
            $stats['team_error_sources'][$key . '_perc'] = calculate_percentage($value, $stats['team_error_sources']['total_lost']);
        } elseif ($key !== 'total_lost') {
             $stats['team_error_sources'][$key . '_perc'] = 0;
        }
    }

    // --- Sorting --- (Outside Loop, After Calculations)
    ksort($stats['action_category_stats']); // Sort categories alphabetically
    uksort($stats['score_by_player'], function($a, $b) use ($stats) { // Sort players by net score desc
        return $stats['score_by_player'][$b]['net_score'] <=> $stats['score_by_player'][$a]['net_score'];
    });
    uksort($stats['score_by_role'], function($a, $b) use ($stats) { // Sort roles by net score desc
        return $stats['score_by_role'][$b]['net_score'] <=> $stats['score_by_role'][$a]['net_score'];
    });
    // Sort player performance stats (e.g., attack efficiency desc)
    uksort($stats['player_attack_stats'], function($a, $b) use ($stats) {
        return $stats['player_attack_stats'][$b]['efficiency'] <=> $stats['player_attack_stats'][$a]['efficiency'];
    });
     uksort($stats['player_serve_stats'], function($a, $b) use ($stats) {
        // Sort by Ace Rate desc, then Error Rate asc as tie-breaker
        $ace_diff = $stats['player_serve_stats'][$b]['ace_rate'] <=> $stats['player_serve_stats'][$a]['ace_rate'];
        if ($ace_diff !== 0) return $ace_diff;
        return $stats['player_serve_stats'][$a]['error_rate'] <=> $stats['player_serve_stats'][$b]['error_rate'];
    });
     uksort($stats['player_block_stats'], function($a, $b) use ($stats) {
        // Sort by Block Points desc, then Block Errors asc as tie-breaker
        $point_diff = $stats['player_block_stats'][$b]['points'] <=> $stats['player_block_stats'][$a]['points'];
        if ($point_diff !== 0) return $point_diff;
        return $stats['player_block_stats'][$a]['errors'] <=> $stats['player_block_stats'][$b]['errors'];
    });

    return $stats;
} // End function processResults


// =============================================================================
// HTML Rendering Functions
// =============================================================================

/**
 * Renders a group of filter buttons.
 *
 * @param string $label The label for the filter group (e.g., "Set:").
 * @param string $filter_key The URL parameter key for this filter (e.g., "filter_set").
 * @param array $items The array of available filter options.
 * @param mixed $current_value The currently selected value for this filter.
 * @param array $current_filters All current filter values.
 * @param int $match_id The current match ID.
 * @param string $item_id_key The key in the $items array holding the value for the link.
 * @param string $item_name_key The key in the $items array holding the display name.
 * @param string $btn_class The Bootstrap button outline class (e.g., "btn-outline-primary").
 * @param bool $is_simple_array If true, $items is a simple array of values (like sets or categories).
 */
function renderFilterGroup(string $label, string $filter_key, array $items, $current_value, array $current_filters, int $match_id, string $item_id_key, string $item_name_key, string $btn_class, bool $is_simple_array = false): void {
    echo '<div class="filter-group">';
    echo '<span class="filter-group-label">' . htmlspecialchars($label) . ':</span>';
    echo '<div class="btn-group" role="group">';

    // "All" button
    $link = build_filter_link($match_id, $filter_key, 'all', $current_filters);
    $active_class = ($current_value === 'all') ? 'active' : '';
    // Use rtrim to remove potential trailing ':' from the label for the "All" button text
    echo '<a href="' . $link . '" class="btn btn-sm ' . $btn_class . ' ' . $active_class . '">All ' . htmlspecialchars(rtrim($label, ':')) . '</a>';

    // Individual item buttons
    foreach ($items as $item) {
        $id = $is_simple_array ? $item : $item[$item_id_key];
        // Determine the name based on whether it's a simple array and the specific label
        if ($is_simple_array) {
            // Check the label to decide whether to prepend "Set "
            if ($label === 'Set') { // Check if the label is 'Set' specifically
                 $name = "Set " . $item;
            } else {
                 $name = $item; // For other simple arrays like Action Category, just use the value
            }
        } else {
            $name = $item[$item_name_key];
        }
        $display_name = htmlspecialchars(str_replace('<br>', ' ', $name), ENT_QUOTES, 'UTF-8'); // Clean name for display

        $link = build_filter_link($match_id, $filter_key, $id, $current_filters);
        $active_class = ($current_value == $id && $current_value !== 'all') ? 'active' : ''; // Ensure 'all' doesn't match id 0 etc.
        echo '<a href="' . $link . '" class="btn btn-sm ' . $btn_class . ' ' . $active_class . '">' . $display_name . '</a>';
    }
    echo '</div></div>'; // Close btn-group and filter-group
}


/**
 * Renders the Action Stats by Category table.
 *
 * @param array $stats The action_category_stats array.
 */
function renderActionCategoryStatsTable(array $stats): void {
    echo '<h3 class="text-light">Action Stats by Category</h3>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-dark table-striped table-bordered table-sm">';
    echo '<thead><tr><th>Category</th><th class="numeric">Count</th><th class="numeric">Score Gain</th><th class="numeric">Score Loss</th><th class="numeric">Net Score</th></tr></thead>';
    echo '<tbody>';
    if (empty($stats)) {
        echo '<tr><td colspan="5">No data matching filters.</td></tr>';
    } else {
        foreach ($stats as $category => $data) {
            $net_class = ($data['net_score'] > 0) ? 'positive' : (($data['net_score'] < 0) ? 'negative' : 'neutral');
            echo '<tr>';
            echo '<td>' . htmlspecialchars($category) . '</td>';
            echo '<td class="numeric">' . $data['count'] . '</td>';
            echo '<td class="numeric positive">' . number_format($data['score_gain'], 1) . '</td>';
            echo '<td class="numeric negative">' . number_format($data['score_loss'], 1) . '</td>';
            echo '<td class="numeric ' . $net_class . '">' . number_format($data['net_score'], 1) . '</td>';
            echo '</tr>';
        }
    }
    echo '</tbody></table></div>';
}

/**
 * Renders a single row for the DaWu stats table.
 *
 * @param string $label The row label (e.g., "接發").
 * @param array $stats The stats array for this row.
 * @param bool $include_extra Whether to include the '無跟' column.
 */
function renderDaWuRow(string $label, array $stats, bool $include_extra = false): void {
    if ($stats['total'] == 0) return; // Don't render if no attempts

    echo "<tr>";
    echo "<td>{$label}</td>";
    echo "<td class='numeric'>{$stats['total']}</td>";
    echo "<td class='numeric'>{$stats['到位']}</td>";
    echo "<td class='numeric'>" . calculate_percentage($stats['到位'], $stats['total']) . "%</td>";
    echo "<td class='numeric'>{$stats['唔到位']}</td>";
    echo "<td class='numeric'>" . calculate_percentage($stats['唔到位'], $stats['total']) . "%</td>";
    echo "<td class='numeric'>{$stats['失分']}</td>";
    echo "<td class='numeric negative'>" . calculate_percentage($stats['失分'], $stats['total']) . "%</td>";

    if ($include_extra) {
        $mugan_count = $stats['無跟'] ?? ""; // Handle if '無跟' key doesn't exist (though it should for 'cover')
        $mugan_perc = is_numeric($mugan_count) ? calculate_percentage($mugan_count, $stats['total']) : "";
        echo "<td class='numeric'>{$mugan_count}</td>";
        echo "<td class='numeric negative'>" . ($mugan_perc !== "" ? "{$mugan_perc}%" : "") . "</td>";
    }
    echo "</tr>";
}

/**
 * Renders the DaWu (到位 / 唔到位) stats table.
 *
 * @param array $stats The dawu_stats array.
 */
function renderDaWuStatsTable(array $stats): void {
    echo '<h4 class="text-light">一傳</h4>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-dark table-striped table-bordered table-sm">';
    echo '<thead><tr>';
    echo '<th>Action Type</th>';
    echo '<th class="numeric">Attempts</th>';
    echo '<th class="numeric">到位</th><th class="numeric">到位 %</th>';
    echo '<th class="numeric">唔到位</th><th class="numeric">唔到位 %</th>';
    echo '<th class="numeric">失分</th><th class="numeric">失分 %</th>';

    // Check if '無跟' column is needed (only if cover has '無跟' > 0)
    $has_mugan = isset($stats['cover']['無跟']) && $stats['cover']['無跟'] > 0;
    if ($has_mugan) {
        echo '<th class="numeric">無跟 Cover</th><th class="numeric">無跟 Cover %</th>';
    }
    echo '</tr></thead>';
    echo '<tbody>';

    renderDaWuRow('接發', $stats['serve_receive'], $has_mugan);
    renderDaWuRow('守殺', $stats['spike_dig'], $has_mugan);
    renderDaWuRow('守tip', $stats['tip_dig'], $has_mugan);
    renderDaWuRow('Free波', $stats['freeball'], $has_mugan);
    renderDaWuRow('Setting', $stats['setting'], $has_mugan);
    renderDaWuRow('Cover', $stats['cover'], $has_mugan); // Always pass true for cover

    // Check if any DaWu data exists
    $no_dawu_data = true;
    foreach ($stats as $type_stats) {
        if ($type_stats['total'] > 0) {
            $no_dawu_data = false;
            break;
        }
    }
    if ($no_dawu_data) {
        $colspan = $has_mugan ? 10 : 8;
        echo '<tr><td colspan="' . $colspan . '">No relevant actions matching filters.</td></tr>';
    }

    echo '</tbody></table></div>';
}

/**
 * Renders the DaWu table and detailed action breakdowns (攔網, 發球, 進攻).
 *
 * @param array $dawu_stats The dawu_stats array.
 * @param array $detailed_stats The detailed_category_stats array.
 * @param string $filter_category The currently selected category filter.
 */
function renderDetailedCategoryStatsTables(array $dawu_stats, array $detailed_stats, string $filter_category): void {
    echo '<h3 class="text-light">Detailed Action Breakdowns</h3>';
    renderDaWuStatsTable($dawu_stats); // Render DaWu table first under this heading
    $found_data = false;

    // Now render the individual category tables
    foreach ($detailed_stats as $category_name => $category_data) {
        if ($category_data['total_count'] > 0) {
            $found_data = true;
            echo '<h4 class="text-light">' . htmlspecialchars($category_name) . '</h4>';
            echo '<div class="table-responsive">';
            echo '<table class="table table-dark table-striped table-bordered table-sm">';
            echo '<thead><tr><th>Action</th><th class="numeric">Count</th><th class="numeric">Percentage</th></tr></thead>';
            echo '<tbody>';
            foreach ($category_data['actions'] as $action_name => $action_data) {
                // $action_name is already htmlspecialchars encoded during processing
                echo '<tr>';
                echo '<td>' . $action_name . '</td>';
                echo '<td class="numeric">' . $action_data['count'] . '</td>';
                echo '<td class="numeric">' . calculate_percentage($action_data['count'], $category_data['total_count']) . '%</td>';
                echo '</tr>';
            }
            echo '<tr class="table-group-divider">';
            echo '<th>Total</th>';
            echo '<td class="numeric"><strong>' . $category_data['total_count'] . '</strong></td>';
            echo '<td class="numeric"><strong>100.0%</strong></td>';
            echo '</tr>';
            echo '</tbody></table></div>';
        } elseif ($filter_category === 'all' || $filter_category === $category_name) {
            // Show message only if filtering for all or this specific category
             echo '<h4 class="text-light">' . htmlspecialchars($category_name) . '</h4>';
             echo '<p class="text-light">No \'' . htmlspecialchars($category_name) . '\' actions recorded matching the current filters.</p>';
             $found_data = true; // Indicate we showed something for this section
        }
    }
     if (!$found_data && $filter_category !== 'all') {
         // If filtering by a category NOT in the detailed list, show a generic message
         echo '<p class="text-light">Detailed breakdown not available for the selected category: ' . htmlspecialchars($filter_category) . '</p>';
     } elseif (!$found_data && $filter_category === 'all') {
         // If filtering 'all' and still no data (unlikely if target cats exist), show generic message
         echo '<p class="text-light">No data available for detailed breakdowns (攔網, 發球, 進攻) matching the current filters.</p>';
     }
}

/**
 * Renders a score contribution table (by Player or Role).
 *
 * @param string $title The title for the table (e.g., "Score Contribution by Player").
 * @param array $stats The score_by_player or score_by_role array.
 * @param string $entity_label The label for the first column (e.g., "Player", "Role").
 */
function renderScoreTable(string $title, array $stats, string $entity_label): void {
    echo '<h3 class="text-light">' . htmlspecialchars($title) . '</h3>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-dark table-striped table-bordered table-sm">';
    echo '<thead><tr><th>' . htmlspecialchars($entity_label) . '</th><th class="numeric">Score Gain</th><th class="numeric">Score Loss</th><th class="numeric">Net Score</th></tr></thead>';
    echo '<tbody>';
    if (empty($stats)) {
        echo '<tr><td colspan="4">No ' . strtolower(htmlspecialchars($entity_label)) . ' scoring data matching filters.</td></tr>';
    } else {
        // Sorting is done in processResults
        foreach ($stats as $entity => $data) {
            $net_class = ($data['net_score'] > 0) ? 'positive' : (($data['net_score'] < 0) ? 'negative' : 'neutral');
            echo '<tr>';
            echo '<td>' . $entity . '</td>'; // Already escaped in processResults
            echo '<td class="numeric positive">' . number_format($data['score_gain'], 1) . '</td>';
            echo '<td class="numeric negative">' . number_format($data['score_loss'], 1) . '</td>';
            echo '<td class="numeric ' . $net_class . '">' . number_format($data['net_score'], 1) . '</td>';
            echo '</tr>';
        }
    }
    echo '</tbody></table></div>';
}

/**
 * Renders the Player Attack Efficiency table.
 *
 * @param array $stats The player_attack_stats array.
 */
function renderPlayerAttackEfficiencyTable(array $stats): void {
    echo '<h3 class="text-light">Player Attack Efficiency</h3>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-dark table-striped table-bordered table-sm">';
    echo '<thead><tr><th>Player</th><th class="numeric">Attempts</th><th class="numeric">Kills</th><th class="numeric">Errors</th><th class="numeric">Efficiency %</th></tr></thead>';
    echo '<tbody>';
    if (empty($stats)) {
        echo '<tr><td colspan="5">No attack data matching filters.</td></tr>';
    } else {
        // Sorting is done in processResults
        foreach ($stats as $player => $data) {
            if ($data['attempts'] == 0) continue; // Skip players with no attempts
            $eff_class = ($data['efficiency'] > 0) ? 'positive' : (($data['efficiency'] < 0) ? 'negative' : 'neutral');
            echo '<tr>';
            echo '<td>' . $player . '</td>'; // Already escaped
            echo '<td class="numeric">' . $data['attempts'] . '</td>';
            echo '<td class="numeric positive">' . $data['kills'] . '</td>';
            echo '<td class="numeric negative">' . $data['errors'] . '</td>';
            echo '<td class="numeric ' . $eff_class . '">' . number_format($data['efficiency'], 1) . '%</td>';
            echo '</tr>';
        }
    }
    echo '</tbody></table></div>';
}

/**
 * Renders the Player Serve Performance table.
 *
 * @param array $stats The player_serve_stats array.
 */
function renderPlayerServePerformanceTable(array $stats): void {
    echo '<h3 class="text-light">Player Serve Performance</h3>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-dark table-striped table-bordered table-sm">';
    echo '<thead><tr><th>Player</th><th class="numeric">Attempts</th><th class="numeric">Aces</th><th class="numeric">Ace %</th><th class="numeric">Errors</th><th class="numeric">Error %</th></tr></thead>';
    echo '<tbody>';
    if (empty($stats)) {
        echo '<tr><td colspan="6">No serve data matching filters.</td></tr>';
    } else {
        // Sorting is done in processResults
        foreach ($stats as $player => $data) {
             if ($data['attempts'] == 0) continue; // Skip players with no attempts
            echo '<tr>';
            echo '<td>' . $player . '</td>'; // Already escaped
            echo '<td class="numeric">' . $data['attempts'] . '</td>';
            echo '<td class="numeric positive">' . $data['aces'] . '</td>';
            echo '<td class="numeric positive">' . number_format($data['ace_rate'], 1) . '%</td>';
            echo '<td class="numeric negative">' . $data['errors'] . '</td>';
            echo '<td class="numeric negative">' . number_format($data['error_rate'], 1) . '%</td>';
            echo '</tr>';
        }
    }
    echo '</tbody></table></div>';
}

/**
 * Renders the Player Block Performance table.
 *
 * @param array $stats The player_block_stats array.
 */
function renderPlayerBlockPerformanceTable(array $stats): void {
    echo '<h3 class="text-light">Player Block Performance</h3>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-dark table-striped table-bordered table-sm">';
    // Including 'Attempts' and 'Effective' for more context, though primary focus is Points vs Errors
    echo '<thead><tr><th>Player</th><th class="numeric">Attempts</th><th class="numeric">得分</th><th class="numeric">失分</th><th class="numeric">有效攔網</th></tr></thead>';
    echo '<tbody>';
    if (empty($stats)) {
        echo '<tr><td colspan="5">No block data matching filters.</td></tr>';
    } else {
        // Sorting is done in processResults
        foreach ($stats as $player => $data) {
             if ($data['attempts'] == 0) continue; // Skip players with no attempts
            echo '<tr>';
            echo '<td>' . $player . '</td>'; // Already escaped
            echo '<td class="numeric">' . $data['attempts'] . '</td>';
            echo '<td class="numeric positive">' . $data['points'] . '</td>';
            echo '<td class="numeric negative">' . $data['errors'] . '</td>';
            echo '<td class="numeric neutral">' . $data['effective'] . '</td>'; // Effective blocks are neutral score-wise
            echo '</tr>';
        }
    }
    echo '</tbody></table></div>';
}

/**
 * Renders the Team Point Scoring Sources table.
 *
 * @param array $stats The team_scoring_sources array.
 */
function renderTeamScoringSourceTable(array $stats): void {
    echo '<h3 class="text-light">Team Point Scoring Sources</h3>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-dark table-striped table-bordered table-sm">';
    echo '<thead><tr><th>Source</th><th class="numeric">Points</th><th class="numeric">Percentage</th></tr></thead>';
    echo '<tbody>';
    if ($stats['total_scored'] == 0) {
        echo '<tr><td colspan="3">No points scored matching filters.</td></tr>';
    } else {
        echo '<tr><td>Attack Kills</td><td class="numeric">' . number_format($stats['attack_kills'], 1) . '</td><td class="numeric">' . number_format($stats['attack_kills_perc'] ?? 0, 1) . '%</td></tr>';
        echo '<tr><td>Block Points</td><td class="numeric">' . number_format($stats['block_points'], 1) . '</td><td class="numeric">' . number_format($stats['block_points_perc'] ?? 0, 1) . '%</td></tr>';
        echo '<tr><td>Service Aces</td><td class="numeric">' . number_format($stats['aces'], 1) . '</td><td class="numeric">' . number_format($stats['aces_perc'] ?? 0, 1) . '%</td></tr>';
        echo '<tr><td>Opponent Errors</td><td class="numeric">' . number_format($stats['opponent_errors'], 1) . '</td><td class="numeric">' . number_format($stats['opponent_errors_perc'] ?? 0, 1) . '%</td></tr>';
        // Add other sources if tracked separately
        echo '<tr class="table-group-divider"><th>Total Scored</th><td class="numeric"><strong>' . number_format($stats['total_scored'], 1) . '</strong></td><td class="numeric"><strong>100.0%</strong></td></tr>';
    }
    echo '</tbody></table></div>';
}

/**
 * Renders the Team Error Sources table.
 *
 * @param array $stats The team_error_sources array.
 */
function renderTeamErrorSourceTable(array $stats): void {
    echo '<h3 class="text-light">Team Error Sources (Points Lost)</h3>';
    echo '<div class="table-responsive">';
    echo '<table class="table table-dark table-striped table-bordered table-sm">';
    echo '<thead><tr><th>Source</th><th class="numeric">Points Lost</th><th class="numeric">Percentage</th></tr></thead>';
    echo '<tbody>';
     if ($stats['total_lost'] == 0) {
        echo '<tr><td colspan="3">No errors recorded matching filters.</td></tr>';
    } else {
        // Reception errors should be displayed first as they are most critical
        echo '<tr><td>Reception Errors</td><td class="numeric">' . number_format($stats['reception_errors'], 1) . '</td><td class="numeric">' . number_format($stats['reception_errors_perc'] ?? 0, 1) . '%</td></tr>';
        echo '<tr><td>Attack Errors</td><td class="numeric">' . number_format($stats['attack_errors'], 1) . '</td><td class="numeric">' . number_format($stats['attack_errors_perc'] ?? 0, 1) . '%</td></tr>';
        echo '<tr><td>Serve Errors</td><td class="numeric">' . number_format($stats['serve_errors'], 1) . '</td><td class="numeric">' . number_format($stats['serve_errors_perc'] ?? 0, 1) . '%</td></tr>';
        echo '<tr><td>Block Errors</td><td class="numeric">' . number_format($stats['block_errors'], 1) . '</td><td class="numeric">' . number_format($stats['block_errors_perc'] ?? 0, 1) . '%</td></tr>';
        echo '<tr><td>Setting Errors</td><td class="numeric">' . number_format($stats['setting_errors'], 1) . '</td><td class="numeric">' . number_format($stats['setting_errors_perc'] ?? 0, 1) . '%</td></tr>';
        echo '<tr><td>Cover Errors</td><td class="numeric">' . number_format($stats['cover_errors'], 1) . '</td><td class="numeric">' . number_format($stats['cover_errors_perc'] ?? 0, 1) . '%</td></tr>';
        echo '<tr><td>Other Errors</td><td class="numeric">' . number_format($stats['other_errors'], 1) . '</td><td class="numeric">' . number_format($stats['other_errors_perc'] ?? 0, 1) . '%</td></tr>';
        echo '<tr class="table-group-divider"><th>Total Lost</th><td class="numeric"><strong>' . number_format($stats['total_lost'], 1) . '</strong></td><td class="numeric"><strong>100.0%</strong></td></tr>';
    }
    echo '</tbody></table></div>';
}


// =============================================================================
// Main Script Logic
// =============================================================================

// --- Input Handling & Validation ---
$match_id = isset($_GET['mid']) ? (int)$_GET['mid'] : 0;
if ($match_id <= 0) {
    die("Invalid Match ID provided.");
}
$filter_set = isset($_GET['filter_set']) ? $_GET['filter_set'] : 'all';
$filter_player = isset($_GET['filter_player']) ? $_GET['filter_player'] : 'all';
$filter_category = isset($_GET['filter_category']) ? $_GET['filter_category'] : 'all';
$filter_role = isset($_GET['filter_role']) ? $_GET['filter_role'] : 'all';
$current_filters = ['filter_set' => $filter_set, 'filter_player' => $filter_player, 'filter_category' => $filter_category, 'filter_role' => $filter_role];

// --- Fetch Data ---
$match_info = fetchMatchInfo($conn, $match_id);
if (!$match_info) {
    die("Match with ID {$match_id} not found."); // fetchMatchInfo already died on error, this is for not found
}

$filter_data = fetchFilterData($conn, $match_id);
$available_sets = $filter_data['available_sets'];
$available_players = $filter_data['available_players'];
$available_categories = $filter_data['available_categories'];
$available_roles = $filter_data['available_roles'];
$actions_by_category = $filter_data['actions_by_category']; // Needed for processing

$results = fetchFilteredResults($conn, $match_id, $current_filters);

// --- Process Data ---
$processed_stats = processResults($results, $actions_by_category);
$action_category_stats = $processed_stats['action_category_stats'];
$dawu_stats = $processed_stats['dawu_stats'];
$detailed_category_stats = $processed_stats['detailed_category_stats'];
$score_by_player = $processed_stats['score_by_player'];
$score_by_role = $processed_stats['score_by_role'];
$player_attack_stats = $processed_stats['player_attack_stats'];
$player_serve_stats = $processed_stats['player_serve_stats'];
$player_block_stats = $processed_stats['player_block_stats'];
$team_scoring_sources = $processed_stats['team_scoring_sources'];
$team_error_sources = $processed_stats['team_error_sources'];


// --- Close Database Connection ---
$conn->close();

// =============================================================================
// HTML Output
// =============================================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Report - Match ID: <?php echo $match_id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Basic styles */
        body { padding: 5px; } /* Adjusted for navbar */
        h1, h2, h3, h4 { margin-top: 20px; }
        h2, h3 { border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        h4 { font-weight: bold; color: #ccc; } /* Lighter for dark mode */

        /* Table styles */
        .table th { white-space: nowrap; }
        .table td.numeric, .table th.numeric { text-align: right; }
        .table th, .table td { vertical-align: middle; word-wrap: break-word;}
        .table-dark { --bs-table-color: #dee2e6; --bs-table-bg: #212529; --bs-table-border-color: #373b3e; --bs-table-striped-bg: #2c3034; --bs-table-striped-color: #dee2e6; --bs-table-active-bg: #373b3e; --bs-table-active-color: #fff; --bs-table-hover-bg: #323539; --bs-table-hover-color: #fff; }

        /* Text colors */
        .positive { color: #198754; } /* Bootstrap success green */
        .negative { color: #dc3545; } /* Bootstrap danger red */
        .neutral { color: #adb5bd; } /* Bootstrap secondary grey */

        /* Filter styles */
        .filter-group { margin-bottom: 15px; }
        .filter-group-label { font-weight: bold; margin-right: 10px; display: block; margin-bottom: 5px; }
        .filter-group .btn-group { display: flex; flex-wrap: wrap; gap: 5px; }
        .filter-group .btn { margin-bottom: 5px; } /* Prevent double margin */

        /* Navbar styles */
        .navbar { padding: 0px; }
        .navbar-brand { font-size: inherit; }
        .back { font-size: 1.2em; transition: all 0.3s ease; text-decoration: none; }
        .back:hover { transform: scale(1.1); }

        /* Ensure high contrast for links/buttons on dark background */
        .btn-outline-primary { --bs-btn-color: #0d6efd; --bs-btn-border-color: #0d6efd; --bs-btn-hover-color: #fff; --bs-btn-hover-bg: #0d6efd; --bs-btn-hover-border-color: #0d6efd; --bs-btn-active-color: #fff; --bs-btn-active-bg: #0d6efd; --bs-btn-active-border-color: #0a58ca; --bs-btn-disabled-color: #0d6efd; --bs-btn-disabled-bg: transparent; }
        .btn-outline-warning { --bs-btn-color: #ffc107; --bs-btn-border-color: #ffc107; --bs-btn-hover-color: #000; --bs-btn-hover-bg: #ffc107; --bs-btn-hover-border-color: #ffc107; --bs-btn-active-color: #000; --bs-btn-active-bg: #ffc107; --bs-btn-active-border-color: #ffca2c; --bs-btn-disabled-color: #ffc107; --bs-btn-disabled-bg: transparent; }
        .btn-outline-info { --bs-btn-color: #0dcaf0; --bs-btn-border-color: #0dcaf0; --bs-btn-hover-color: #000; --bs-btn-hover-bg: #0dcaf0; --bs-btn-hover-border-color: #0dcaf0; --bs-btn-active-color: #000; --bs-btn-active-bg: #0dcaf0; --bs-btn-active-border-color: #3dd5f3; --bs-btn-disabled-color: #0dcaf0; --bs-btn-disabled-bg: transparent; }
        .btn-outline-light { --bs-btn-color: #f8f9fa; --bs-btn-border-color: #f8f9fa; --bs-btn-hover-color: #000; --bs-btn-hover-bg: #f8f9fa; --bs-btn-hover-border-color: #f8f9fa; --bs-btn-active-color: #000; --bs-btn-active-bg: #f8f9fa; --bs-btn-active-border-color: #f9fafb; --bs-btn-disabled-color: #f8f9fa; --bs-btn-disabled-bg: transparent; }

        /* Active state needs higher contrast */
        .btn-check:active+.btn-outline-primary, .btn-check:checked+.btn-outline-primary, .btn-outline-primary.active, .btn-outline-primary.dropdown-toggle.show, .btn-outline-primary:active { color: #fff; background-color: #0d6efd; border-color: #0d6efd; }
        .btn-check:active+.btn-outline-warning, .btn-check:checked+.btn-outline-warning, .btn-outline-warning.active, .btn-outline-warning.dropdown-toggle.show, .btn-outline-warning:active { color: #000; background-color: #ffc107; border-color: #ffc107; }
        .btn-check:active+.btn-outline-info, .btn-check:checked+.btn-outline-info, .btn-outline-info.active, .btn-outline-info.dropdown-toggle.show, .btn-outline-info:active { color: #000; background-color: #0dcaf0; border-color: #0dcaf0; }
        .btn-check:active+.btn-outline-light, .btn-check:checked+.btn-outline-light, .btn-outline-light.active, .btn-outline-light.dropdown-toggle.show, .btn-outline-light:active { color: #000; background-color: #f8f9fa; border-color: #f8f9fa; }

        /* Styles for collapsible tables */
        h3, h4 {
            cursor: pointer;
            user-select: none; /* Prevent text selection on click */
            position: relative; /* Needed for absolute positioning of indicator if desired */
        }
        .toggle-indicator {
            left: 5px;
            top: 50%;
            transform: translateY(-50%);
            font-weight: bold;
            font-size: 1.1em;
            width: 20px; /* Ensure consistent spacing */
            text-align: center;
        }
        .table-responsive {
            overflow-x: auto;
            overflow-y: hidden; /* Prevent vertical scrollbars */
            transition: max-height 0.3s ease-out, padding-top 0.3s ease-out, padding-bottom 0.3s ease-out;
            max-height: 2000px; /* Set a large max-height for expanded state */
            /* Add padding for smoother visual transition */
            padding-top: 10px !important;
            padding-bottom: 10px !important;
            box-sizing: border-box; /* Include padding in height calculation */
        }
        .table-responsive.collapsed {
            max-height: 0;
            padding-top: 0;
            padding-bottom: 0;
            /* Optionally add border collapse if needed */
             border-top: none;
             border-bottom: none;
             margin-top: -1px; /* Adjust to prevent double borders if needed */
        }
        /* Ensure tables inside don't have excessive margin causing jumpiness */
        .table-responsive > .table {
            margin-bottom: 0;
        }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-sm bg-dark navbar-dark">
        <div class="container-fluid">
            <a class="back navbar-brand" href="set.php?mid=<?php echo $match_id; ?>">⬅</a>
            <span class="navbar-brand ms-auto"> <!-- Use span instead of link -->
                <?php
                    echo htmlspecialchars($match_info['date']) . " " .
                         htmlspecialchars($match_info['type']) . " VS " .
                         htmlspecialchars($match_info['opponent_team']);
                ?>
            </span>
        </div>
    </nav>

    <div class="container">
        <h1 class="text-light">Match Report</h1>

        <!-- Match Details -->
        <div class="alert alert-info bg-dark text-info border-info">
            <strong>Match Details:</strong>
            Date: <?php echo htmlspecialchars($match_info['date']); ?> |
            Type: <?php echo htmlspecialchars($match_info['type']); ?> |
            Opponent: <?php echo htmlspecialchars($match_info['opponent_team']); ?>
            (Grade: <?php echo htmlspecialchars($match_info['tgrade']); ?>, Rate: <?php echo htmlspecialchars($match_info['trate']); ?>)
        </div>

        <h2 class="text-light">Filters</h2>
        <?php
        renderFilterGroup('Set', 'filter_set', $available_sets, $filter_set, $current_filters, $match_id, '', '', 'btn-outline-primary', true);
        renderFilterGroup('Player', 'filter_player', $available_players, $filter_player, $current_filters, $match_id, 'pid', 'pname', 'btn-outline-warning');
        renderFilterGroup('Action Category', 'filter_category', $available_categories, $filter_category, $current_filters, $match_id, '', '', 'btn-outline-info', true); // Simple array of names, corrected call
        renderFilterGroup('Role', 'filter_role', $available_roles, $filter_role, $current_filters, $match_id, 'rid', 'rName', 'btn-outline-light');
        ?>
        <hr class="border-secondary">

        <h2 class="text-light">Statistics Summary</h2>

        <?php renderActionCategoryStatsTable($action_category_stats); ?>

        <?php renderTeamScoringSourceTable($team_scoring_sources); ?>

        <?php renderTeamErrorSourceTable($team_error_sources); ?>

        <?php renderDetailedCategoryStatsTables($dawu_stats, $detailed_category_stats, $filter_category); ?>

        <?php renderScoreTable('Score Contribution by Player', $score_by_player, 'Player'); ?>

        <?php renderScoreTable('Score Contribution by Role', $score_by_role, 'Role'); ?>

        <hr class="border-secondary">
        <h2 class="text-light">Player Performance Metrics</h2>

        <?php renderPlayerAttackEfficiencyTable($player_attack_stats); ?>

        <?php renderPlayerServePerformanceTable($player_serve_stats); ?>

        <?php renderPlayerBlockPerformanceTable($player_block_stats); ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const headers = document.querySelectorAll('h3, h4');

            headers.forEach(header => {
                const tableContainer = header.nextElementSibling;

                if (tableContainer && tableContainer.classList.contains('table-responsive')) {
                    // Add indicator
                    const indicator = document.createElement('span');
                    indicator.classList.add('toggle-indicator');
                    // Assume initially expanded unless it already has 'collapsed' class (e.g., from server-side logic later)
                    indicator.textContent = tableContainer.classList.contains('collapsed') ? '[+]' : '[-]';
                    header.insertBefore(indicator, header.firstChild);

                    // Set initial max-height for animation if not collapsed
                    if (!tableContainer.classList.contains('collapsed')) {
                         // Calculate initial height based on content
                         // Use scrollHeight of the table itself for better accuracy
                         const tableElement = tableContainer.querySelector('table');
                         if (tableElement) {
                            // Add padding values to scrollHeight
                            const style = window.getComputedStyle(tableContainer);
                            const paddingTop = parseFloat(style.paddingTop);
                            const paddingBottom = parseFloat(style.paddingBottom);
                            tableContainer.style.maxHeight = (tableElement.scrollHeight + paddingTop + paddingBottom) + 'px';
                         } else {
                             tableContainer.style.maxHeight = tableContainer.scrollHeight + 'px'; // Fallback
                         }
                    } else {
                         tableContainer.style.maxHeight = '0px'; // Ensure collapsed starts at 0
                    }


                    header.addEventListener('click', function() {
                        const isCollapsed = tableContainer.classList.contains('collapsed');

                        if (isCollapsed) {
                            // Expand
                            tableContainer.classList.remove('collapsed');
                            indicator.textContent = '[-]';
                            // Calculate height based on content
                            const tableElement = tableContainer.querySelector('table');
                             if (tableElement) {
                                const style = window.getComputedStyle(tableContainer);
                                const paddingTop = parseFloat(style.paddingTop);
                                const paddingBottom = parseFloat(style.paddingBottom);
                                tableContainer.style.maxHeight = (tableElement.scrollHeight + paddingTop + paddingBottom) + 'px';
                             } else {
                                 tableContainer.style.maxHeight = tableContainer.scrollHeight + 'px'; // Fallback
                             }
                        } else {
                            // Collapse
                            // Set max-height to current height first to allow transition FROM current height
                            tableContainer.style.maxHeight = tableContainer.scrollHeight + 'px';
                            // Force reflow/repaint before setting to 0 - crucial for transition out
                            void tableContainer.offsetWidth;
                            // Now set to 0 to trigger collapse animation
                            tableContainer.classList.add('collapsed');
                            indicator.textContent = '[+]';
                            tableContainer.style.maxHeight = '0px';
                        }
                    });
                } else {
                    // No table follows, remove pointer cursor and padding
                    header.style.cursor = 'default';
                    header.style.paddingLeft = '0px'; // Remove padding if no indicator
                }
            });
        });
    </script>
</body>
</html>
