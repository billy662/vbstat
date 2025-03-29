<!DOCTYPE html>
<html lang="en">
<?php 
	include 'conn.php'; 
	include 'functions.php';

	$acid = getAcid();

	$mid = isset($_GET['mid']) && is_numeric($_GET['mid']) ? $_GET['mid'] : -1;
	$sid = isset($_GET['sid']) && is_numeric($_GET['sid']) ? $_GET['sid'] : -1;

	if($mid == -1 || $sid == -1){
		header("Location: index.php");
		exit();
	}
?>
<head>
	<title>VB Stat</title>
	<meta charset="utf-8">
	<!-- Latest compiled and minified CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

	<!-- Latest compiled JavaScript -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

	<style> 
		:root {
			--secondary-bg: #1e1e1e;
			--accent-color: #4dabf7;
			--text-color: #e0e0e0;
			--player-color: rgb(71, 107, 105);
			--role-color: #553e85;
			--inplay-color: #003545;
			--score-color: rgb(61, 109, 62);
			--error-color:rgb(153, 49, 51);
			--border-radius: 16px;
		}

		body, html { 
			margin: 0; 
			padding: 0; 
			height: 100%; 
			width: 100%;
			font-size: 1.3em; /* Increased base font size for mobile */
			background-color: var(--primary-bg);
			color: var(--text-color);
		} 

		.main-container {
			padding: 10px; /* Reduced padding */
			color: white;
			max-width: 1400px;
			margin: 0 auto;
		}

		.navbar-brand {
			margin-right: 0;
		}

		.back{
			font-size: 1.4em;
			transition: transform 0.2s;
		}

		.back:hover {
			transform: translateX(-3px);
		}

		table{
			width: 100%;
			height: 100%;
			border-collapse: separate;
			border-spacing: 0;
		}

		.player-container, .role-container, .in-play-container, .score-container, .error-container{
			margin-right: 0px;
			vertical-align: top;
			border-left: 1px solid rgba(255, 255, 255, 0.1); 
			border-right: 1px solid rgba(255, 255, 255, 0.1);
			text-align: center;
			padding: 10px 5px;
		}

		/* Custom CSS for the scoreboard */
		#scoreboard {
			font-size: 3.5rem;
			font-weight: bold;
			text-align: center;
			background-color: var(--secondary-bg);
			color: #fff;
			border-radius: var(--border-radius);
			width: 100%;
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
			padding: 15px 0;
			position: relative;
			overflow: hidden;
		}

		#scoreboard::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			height: 3px;
			background: linear-gradient(90deg, rgba(255,255,255,0.1), rgba(255,255,255,0.5), rgba(255,255,255,0.1));
			z-index: 1;
		}

		#scoreboard::after {
			content: '';
			position: absolute;
			bottom: 0;
			left: 0;
			right: 0;
			height: 3px;
			background: linear-gradient(90deg, rgba(0,0,0,0.3), rgba(0,0,0,0.7), rgba(0,0,0,0.3));
			z-index: 1;
		}

		#scoreboard .score-digit {
			background-color: #000;
			border-radius: 8px;
			padding: 5px 15px;
			margin: 0 2px;
			box-shadow: inset 0 0 10px rgba(0,0,0,0.8);
			position: relative;
			overflow: hidden;
			min-width: 60px;
			perspective: 200px;
		}

		#scoreboard .score-digit::before {
			content: '';
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			height: 50%;
			background: linear-gradient(to bottom, rgba(255,255,255,0.15), rgba(255,255,255,0));
			border-bottom: 1px solid rgba(0,0,0,0.4);
			z-index: 1;
		}

		#scoreboard .score-separator {
			display: inline-block;
			font-size: 3rem;
			margin: 0 10px;
			color: #aaa;
			text-shadow: 0 0 5px rgba(0,0,0,0.5);
		}

		#scoreboard .home-score .score-digit {
			background-color: #003300;
			color: #0f0;
			text-shadow: 0 0 10px rgba(0,255,0,0.7);
		}

		#scoreboard .away-score .score-digit {
			background-color: #330000;
			color: #f00;
			text-shadow: 0 0 10px rgba(255,0,0,0.7);
		}

		.last-action-container{
			padding-right: 0;
			margin-bottom: 15px;
		}

		.last-action-text {
            background-color: var(--secondary-bg);
            color: #ffffff;
            border: 2px solid rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: var(--border-radius);
			text-align: center;
			font-size: 0.9em;
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

		#btnUndo{
			width: 100%;
			border-radius: var(--border-radius);
			transition: all 0.3s ease;
		}

		#btnUndo:hover {
			background-color: #c82333;
			transform: scale(1.02);
		}

		.radio-container {
			margin: 3px; /* Reduced margin for tighter spacing */
		}

		.radio-input {
			display: none;
		}

		.radio-label {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 95px; /* Slightly smaller width */
			height: 95px; /* Slightly smaller height */
			border: 3px solid rgba(255, 255, 255, 0.1);
			border-radius: var(--border-radius);
			cursor: pointer;
			text-align: center;
			padding: 5px; /* Reduced padding */
			transition: all 0.3s ease;
			background-color: var(--secondary-bg);
			color: var(--text-color);
			font-weight: bold;
			font-size: 0.85em; /* Increased font size for better readability on mobile */
			box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
		}

		/* All player radio buttons */
		.label-pid{
			background-color: var(--player-color);
		}

		/* All role radio buttons */
		.label-rid{
			background-color: var(--role-color);
		}

		/* All in-play radio buttons */
		.label-in-play{
			background-color: var(--inplay-color);
		}

		/* All score radio buttons */
		.label-score{
			background-color: var(--score-color);
		}

		/* All error radio buttons */
		.label-error{
			background-color: var(--error-color);
		}
		
		.radio-input:checked + .radio-label {
			border-color: var(--accent-color);
			background-color: rgba(77, 171, 247, 0.15);
			color: #ffffff;
			transform: scale(1.05);
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
		}

		.radio-label:hover {
			border-color: rgba(255, 255, 255, 0.3);
			background-color: rgba(255, 255, 255, 0.05);
			transform: translateY(-2px);
		}

		/* Optional active state */
		.radio-input:active + .radio-label {
			transform: scale(0.98);
		}

		.radio-container-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			width: fit-content;
			margin: 0 auto;
		}

		.radio-container-grid .span-2-columns {
			grid-column: span 2;
		}

		.role-container .radio-label,
		.score-container .radio-label,
		.error-container .radio-label {
			margin-bottom: 3px;
		}

		.error-container .radio-label,
		.in-play-container .radio-label{
			width: 120px;
		}

		/* All disabled radio buttons */
		:disabled + .radio-label {
			background-color: rgba(51, 51, 51, 0.5);
			cursor: not-allowed;
		}

		.disabled-action {
			opacity: 0.5;
			cursor: not-allowed;
			transform: none !important;
			box-shadow: none !important;
		}

		.all-actions-container{
			width: 100%;
			max-width: 1400px;
			margin: 0 auto;
			padding: 0 15px;
		}

		#allActionsTable{
			font-size: 0.75em; /* Increased font size for better readability */
			border-radius: var(--border-radius);
			overflow: hidden;
		}

		.table-dark {
			background-color: var(--secondary-bg);
			border-radius: var(--border-radius);
			overflow: hidden;
		}

		.table-dark thead th {
			background-color: rgba(0, 0, 0, 0.2);
			border-color: rgba(255, 255, 255, 0.1);
			padding: 12px 8px;
		}

		.table-dark tbody td {
			border-color: rgba(255, 255, 255, 0.05);
		}

		.btn-secondary {
			border-radius: var(--border-radius);
			transition: all 0.3s ease;
		}

		.btn-secondary:hover {
			transform: translateY(-2px);
			box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
		}

		.toast-container {
			z-index: 1100;
		}

		.toast {
			background-color: var(--secondary-bg);
			color: var(--text-color);
			border-radius: var(--border-radius);
			box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
		}

		.toast-header {
			background-color: rgba(0, 0, 0, 0.2);
			color: var(--text-color);
			border-bottom: 1px solid rgba(255, 255, 255, 0.1);
		}

		/* Responsive adjustments */
		@media (max-width: 992px) {
			body, html {
				font-size: 1.4em; /* Increased font size for mobile */
			}

			.radio-label {
				width: 100px;
				height: 100px;
				font-size: 0.85em; /* Increased font size for better readability */
			}

			.error-container .radio-label,
			.in-play-container .radio-label {
				width: 110px;
			}
		}

		@media (max-width: 768px) {
			body, html {
				font-size: 1.2em; /* Increased font size for mobile */
			}

			.radio-label {
				width: 85px;
				height: 85px;
				font-size: 0.75em; /* Increased font size for better readability */
				padding: 4px;
			}

			.error-container .radio-label,
			.in-play-container .radio-label {
				width: 105px;
			}

			#scoreboard {
				font-size: 2.8rem; /* Larger scoreboard on mobile */
			}
			
			.radio-container {
				margin: 2px; /* Even tighter spacing for mobile */
			}
			
			.radio-container-grid {
				gap: 3px; /* Tighter grid spacing for mobile */
			}
		}

		@media (max-width: 576px) {
			body, html {
				font-size: 1.1em; /* Adjusted for smallest screens */
			}
			
			.radio-label {
				width: 80px;
				height: 80px;
				font-size: 0.7em;
				padding: 3px;
			}

			.error-container .radio-label,
			.in-play-container .radio-label {
				width: 95px;
			}

			#scoreboard {
				font-size: 2.5rem;
				margin-bottom: 0px;
			}
			
			.radio-container {
				margin: 1px; /* Minimal spacing for smallest screens */
			}
			
			.radio-container-grid {
				gap: 2px; /* Minimal grid spacing for smallest screens */
			}

			.table-responsive {
				overflow-x: auto;
			}
			
			.last-action-text {
				font-size: 0.95em; /* Larger text for last action on mobile */
			}
			
			#allActionsTable {
				font-size: 0.8em; /* Larger text for action history on mobile */
			}
		}
	</style>

</head>
<body class="bg-dark">
	<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
		<div class="container-fluid">
			<a class="back navbar-brand" href="set.php?mid=<?php echo $mid; ?>">⬅</a>
			<a class="navbar-brand ms-auto">
				<?php
					/* Get match date, type, team name */
					$sql = "
						SELECT 
							m.date AS date, 
							m.type AS type, 
							t.tname AS tname, 
							s.setNo AS setno
						FROM 
							matches m
						JOIN 
							team t ON m.tid = t.tid
						JOIN 
							sets s ON s.sid = $sid
						WHERE 
							m.mid = $mid;
					";

					$result = $conn->query($sql);
					if($result->num_rows > 0){
						$row = $result->fetch_assoc();
						echo $row["date"] . " " . $row["type"] . " VS " . $row["tname"] . " Set " . $row["setno"];
					} else {
						echo "ERROR";
					}
				?>
			</a>
		</div>
	</nav>

	<input type="hidden" id="result" value="<?php echo (isset($_GET['result'])) ? $_GET['result'] : '';  ?>">
	<div class="toast-container position-fixed bottom-0 end-0 p-3"> 
		<div id="toastBox" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000"> 
			<div class="toast-header"> 
				<strong class="me-auto">Notification</strong> 
				<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button> 
			</div> 
			<div class="toast-body"> 
				<?php echo (isset($_GET['result'])) ? $_GET['result'] : '';  ?> 
			</div> 
		</div>
	</div>

	<div class="container-fluid">
		<div id="scoreboard">
		<?php
			//Get scoreboard score
			$scoreboard = getScoreboard($conn, $sid);
			
			// Format the output with individual digits
			$homeScore = $scoreboard['total_scored'];
			$awayScore = $scoreboard['total_lost'];
			
			// Ensure scores are at least one digit (for 0)
			$homeDigits = $homeScore > 0 ? str_split($homeScore) : ['0'];
			$awayDigits = $awayScore > 0 ? str_split($awayScore) : ['0'];
			
			echo '<span class="home-score">';
			foreach ($homeDigits as $digit) {
				echo '<span class="score-digit">' . $digit . '</span>';
			}
			echo '</span>';
			
			echo '<span class="score-separator">:</span>';
			
			echo '<span class="away-score">';
			foreach ($awayDigits as $digit) {
				echo '<span class="score-digit">' . $digit . '</span>';
			}
			echo '</span>';
		?>
	</div>
	
		<form action="statHandler.php" method="GET">
			<input type="hidden" name="mid" value="<?php echo $mid; ?>">
			<input type="hidden" name="sid" value="<?php echo $sid; ?>">
			<input type="hidden" name="action" value="add">
			<div class="main-container">
				<div class="last-action-container container-fluid row">
					<div class="col-md-9 col-sm-8 justify-content-center">
						<div class="text-center">
							<div class="last-action-text">
								<i class="fas fa-history me-2"></i> Last action:
								<?php
									$sql = "
										SELECT 
											p.pname, 
											r.rName, 
											a.aname 
										FROM 
											result res
										INNER JOIN 
											player p ON res.pid = p.pid 
										INNER JOIN 
											action a ON res.aid = a.aid 
										INNER JOIN
											role r ON res.rid = r.rid 
										WHERE 
											res.resid = (SELECT MAX(resid) FROM result WHERE sid = {$sid}) 
											AND res.sid = {$sid}
									";

								$isLastActionNull = false;
								$result = $conn->query($sql);
								if($result->num_rows > 0){
                                    $row = $result->fetch_assoc();
                                    echo "<strong>" . strip_tags($row['pname']) . "</strong> (<em>" . strip_tags($row['rName']) . "</em>) - <strong>" . strip_tags($row['aname']) . "</strong>";
                                }
								else{
									$isLastActionNull = true;
									echo "N/A";
								}
							?>
						</div>
					</div>
				</div>
				
				<?php
					if(!$isLastActionNull):
				?>
					<div class="col-md-3 col-sm-4">
						<button type="button" class="btn btn-danger btn-custom" id="btnUndo" onclick="location.href='statHandler.php?action=undo&mid=<?php echo $mid; ?>&sid=<?php echo $sid; ?>'">
							<i class="fas fa-undo-alt me-1"></i> Undo
						</button>
					</div>
				<?php
					endif;
				?>

			</div>

			<div class="table-responsive">
				<table class="table table-striped table-dark">
					<thead>
						<tr>
							<th style="width: 25%;">Player</th>
							<th style="width: 15%;">Role</th>
							<th style="width: 25%;">In-play</th>
							<th style="width: 15%;">得分</th>
							<th style="width: 25%;">失分</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td class="player-container">
								<div class="radio-container-grid">
									<?php
										$sql = "SELECT * FROM `player` WHERE `acid` = $acid OR `acid` IS NULL ORDER BY `player`.`pid` ASC";
										$result = $conn->query($sql);
										if($result->num_rows > 0){
											while($row = $result->fetch_assoc()){
												echo "<div class=\"radio-container\">";
												echo "<input class=\"radio-input\" type=\"radio\" required name=\"pid\" id=\"player_" . $row["pname"] . "\" value=\"" . $row["pid"] . "\">";
												echo "<label class=\"radio-label label-pid\" for=\"player_" . $row["pname"] . "\">" . $row["pname"] . "</label>";
												echo "</div>";
											}
										}
									?>
								</div>
							</td>
							<td class="role-container">
								<div style="width: fit-content; margin: 0 auto;">
									<?php
										$sql = "SELECT * FROM `role`";
										$result = $conn->query($sql);
										if($result->num_rows > 0){
											while($row = $result->fetch_assoc()){
												echo "<div class=\"radio-container\">";
												echo "<input class=\"radio-input\" type=\"radio\" required name=\"rid\" id=\"role_" . $row["rName"] . "\" value=\"" . $row["rid"] . "\">";
												echo "<label class=\"radio-label label-rid\" for=\"role_" . $row["rName"] . "\">" . $row["rName"] . "</label>";
												echo "</div>";
											}
										}
									?>
								</div>
							</td>
							<td class="in-play-container">
								<div class="radio-container-grid">
									<?php
										$sql = "SELECT * FROM `action` WHERE `score` = 0 ORDER BY `action`.`sorting` ASC";
										$result = $conn->query($sql);
										if($result->num_rows > 0){
											while($row = $result->fetch_assoc()){
												$isMultiBlock = $row["aname"] == "多人攔網";
												$containerClass = $isMultiBlock ? "radio-container span-2-columns" : "radio-container";
												$labelStyle = $isMultiBlock ? " style=\"width: 100%;\"" : "";
												
												echo "<div class=\"{$containerClass}\">";
												echo "<input class=\"radio-input\" type=\"radio\" required name=\"aid\" 
													id=\"in_play_{$row['aname']}\" value=\"{$row['aid']}\">";
												echo "<label class=\"radio-label label-in-play\" for=\"in_play_{$row['aname']}\"{$labelStyle}>{$row['aname']}</label>";
												echo "</div>";
											}
										}
									?>
								</div>
							</td>
							<td class="score-container">
								<div style="width: fit-content; margin: 0 auto;">
									<?php
										$sql = "SELECT * FROM `action` WHERE `score` > 0";
										$result = $conn->query($sql);
										if($result->num_rows > 0){
											while($row = $result->fetch_assoc()){
												echo "<div class=\"radio-container\">";
												echo "<input class=\"radio-input\" type=\"radio\" name=\"aid\" id=\"score_" . $row["aname"] . "\" value=\"" . $row["aid"] . "\">";
												echo "<label class=\"radio-label label-score\" for=\"score_" . $row["aname"] . "\">" . $row["aname"] . "</label>";
												echo "</div>";
											}
										}
									?>
								</div>
							</td>
							<td class="error-container">
								<div style="width: fit-content; margin: 0 auto;">
									<?php
										$sql = "SELECT * FROM `action` WHERE `score` = -1";
										$result = $conn->query($sql);
										if($result->num_rows > 0){
											while($row = $result->fetch_assoc()){
												echo "<div class=\"radio-container\">";
												echo "<input class=\"radio-input\" type=\"radio\" name=\"aid\" id=\"error_" . $row["aname"] . "\" value=\"" . $row["aid"] . "\">";
												echo "<label class=\"radio-label label-error\" for=\"error_" . $row["aname"] . "\">" . $row["aname"] . "</label>";
												echo "</div>";
											}
										}
									?>
								</div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</form>

	<div class="all-actions-container mt-3">
		<button class="btn btn-secondary mb-2 ms-3" type="button" data-bs-toggle="collapse" data-bs-target="#allActionsTable" aria-expanded="false" aria-controls="allActionsTable">
			<i class="fas fa-history me-1"></i> Show all actions
		</button>
		<div class="collapse" id="allActionsTable">
			<?php
				$sql = "SELECT 
						player.pname AS player_name, 
						role.rName AS role_name,
						action.aname AS action_name
					FROM 
						result
					JOIN 
						player ON result.pid = player.pid
					JOIN 
						action ON result.aid = action.aid
					JOIN 
						sets ON result.sid = sets.sid	
					JOIN 
						role ON result.rid = role.rid
					JOIN 
						matches ON sets.mid = matches.mid
					WHERE 
						matches.mid = $mid
						AND sets.sid = $sid
					ORDER BY result.resid DESC;";
				$result = $conn->query($sql);
				$rowsCount = $result->num_rows;
				
				echo "<div class='ms-3 mb-2'><span style=\"color: white\"><b>Total actions: $rowsCount</b></span></div>";
				
				if($rowsCount > 0){
			?>
			
			<div class="table-responsive mx-3">
				<table class="table table-striped table-dark">
					<thead>
						<tr>
							<th style="width:15%">Player</th>
							<th style="width:15%">Role</th>
							<th style="width:70%">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php
							while($row = $result->fetch_assoc()){
								echo "<tr>";
								echo "<td>" . strip_tags($row['player_name']) . "</td>";
								echo "<td>" . strip_tags($row['role_name']) . "</td>";
								echo "<td>" . strip_tags($row['action_name']) . "</td>";
								echo "</tr>";
							}
						?>
					</tbody>
				</table>
			</div>
			<?php
				}
			?>
		</div>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			let validPairs = [];
			
			// Fetch valid pairs from the server
			fetch('fetch_role_action.php')
				.then(response => {
					if (!response.ok) {
						throw new Error('Network response was not ok');
					}
					return response.json();
				})
				.then(data => {
					if (data.status === 'success') {
						validPairs = data.data; // Store valid pairs globally
						
						// Auto-select player if only one is available
						const pidInputs = document.querySelectorAll('input[name="pid"]');
						if (pidInputs.length === 1) {
							pidInputs[0].checked = true;
							pidInputs[0].dispatchEvent(new Event('change'));
						} else if (document.querySelector('input[name="pid"]:checked')) {
							// If a player is already selected (e.g. by browser cache), trigger the change event
							document.querySelector('input[name="pid"]:checked').dispatchEvent(new Event('change'));
						}
						
						updateAidRadioButtons(); // Initial update based on the default selected rid
					} else {
						console.error('Error fetching valid pairs:', data.message);
					}
				})
				.catch(error => {
					console.error('Error fetching valid pairs:', error);
					alert('Failed to load role-action pairs. Please refresh the page.');
				});

			// Add event listeners to pid buttons
			const pidInputs = document.querySelectorAll('input[name="pid"]');
			pidInputs.forEach(pidInput => {
				pidInput.addEventListener('change', updateRidRadioButtons);
			});

			// Add event listeners to rid radio buttons
			const ridInputs = document.querySelectorAll('input[name="rid"]');
			ridInputs.forEach(ridInput => {
				ridInput.addEventListener('change', updateAidRadioButtons);
			});

			// Add event listeners to aid radio buttons
			const aidInputs = document.querySelectorAll('input[name="aid"]');
			aidInputs.forEach(aidInput => {
				aidInput.addEventListener('change', function(event) {
					// Check if required fields are selected
					const selectedPid = document.querySelector('input[name="pid"]:checked');
					const selectedRid = document.querySelector('input[name="rid"]:checked');
					
					if (!selectedPid || !selectedRid) {
						event.preventDefault();
						alert('Please select both player and role before selecting an action.');
						aidInput.checked = false;
						return;
					}
					
					const pidValue = selectedPid.value;
					const ridValue = selectedRid.value;
					
					selectedRidPidPair = getSelectedRidPidPair();

					// Check if selectedPid is in the array
					let pidFound = false;
					for (let i = 0; i < selectedRidPidPair.length; i++) {
						if (selectedRidPidPair[i][0] == pidValue) {
							selectedRidPidPair[i][1] = ridValue;
							pidFound = true;
							break;
						}
					}

					// If selectedPid is not found, add the pair to the array
					if (!pidFound) {
						selectedRidPidPair.push([pidValue, ridValue]);
					}

					// Update the cookie with the new array
					const expiryDate = new Date();
					expiryDate.setMonth(expiryDate.getMonth() + 1); // Cookie expires in 1 month
					document.cookie = `selectedridpidpair=${JSON.stringify(selectedRidPidPair)}; path=/; expires=${expiryDate.toUTCString()}`;

					// Submit the form
					document.querySelector('form').submit();
				});
			});

			// Function to update rid radio buttons based on the selected pid
			function updateRidRadioButtons() {
				const selectedPid = document.querySelector('input[name="pid"]:checked')?.value;
				if (!selectedPid) return; // Exit if no pid is selected
				
				const ridInputs = document.querySelectorAll('input[name="rid"]');
				const selectedRidPidPair = getSelectedRidPidPair();

				// First, apply the basic disabling rules based on selectedPid
				ridInputs.forEach(ridInput => {
					const rid = parseInt(ridInput.value);
					if (selectedPid == 0) {
						ridInput.disabled = rid !== 7;
					} else {
						ridInput.disabled = rid === 7;
						ridInput.checked = false;
					}

					// Update visual state of the label
					const label = document.querySelector(`label[for="${ridInput.id}"]`);
					if (label) {
						if (ridInput.disabled) {
							label.classList.add('disabled-action');
						} else {
							label.classList.remove('disabled-action');
						}
					}
				});

				// Then check if selectedPid is in the cookie array
				let pidFound = false;
				for (let i = 0; i < selectedRidPidPair.length; i++) {
					if (selectedRidPidPair[i][0] == selectedPid) {
						const selectedRid = selectedRidPidPair[i][1];
						const selectedRidInput = document.querySelector(`input[name="rid"][value="${selectedRid}"]`);
						if (selectedRidInput && !selectedRidInput.disabled) {
							selectedRidInput.checked = true;
							selectedRidInput.dispatchEvent(new Event('change'));
							pidFound = true;
						}
						break;
					}
				}

				if (!pidFound) {
					// Auto-select first available role if none is selected
					if (!document.querySelector('input[name="rid"]:checked')) {
						const firstAvailableRid = document.querySelector('input[name="rid"]:not(:disabled)');
						if (firstAvailableRid) {
							firstAvailableRid.checked = true;
							firstAvailableRid.dispatchEvent(new Event('change'));
						}
					}
				}

				updateAidRadioButtons();
			}

			// Function to update aid radio buttons based on the selected rid
			function updateAidRadioButtons() {
				const isPidChecked = document.querySelector('input[name="pid"]:checked') !== null;
				const selectedRid = document.querySelector('input[name="rid"]:checked')?.value;
				const aidInputs = document.querySelectorAll('input[name="aid"]');

				aidInputs.forEach(aidInput => {
					const aid = parseInt(aidInput.value);

					// Disable the aid radio button if no pid radio button is checked
					let isDisabled = !isPidChecked || !selectedRid;

					// Check if the pair (selectedRid, aid) exists in the valid pairs
					if (!isDisabled) {
						const isValid = validPairs.some(pair => pair.rid == selectedRid && pair.aid == aid);
						isDisabled = !isValid;
					}

					// Enable or disable the aid radio button based on validity
					aidInput.disabled = isDisabled;

					// Uncheck the aid radio button if it is disabled
					if (aidInput.disabled && aidInput.checked) {
						aidInput.checked = false;
					}
					
					// Update visual state of the label
					const label = document.querySelector(`label[for="${aidInput.id}"]`);
					if (label) {
						if (isDisabled) {
							label.classList.add('disabled-action');
						} else {
							label.classList.remove('disabled-action');
						}
					}
				});
			}

			// Get cookies and parse the selectedridpidpair cookie
			function getSelectedRidPidPair() {
				const cookies = document.cookie.split(';').reduce((acc, cookie) => {
					const [key, value] = cookie.split('=').map(c => c.trim());
					if (value) acc[key] = value;
					return acc;
				}, {});
				
				try {
					return cookies.selectedridpidpair ? JSON.parse(cookies.selectedridpidpair) : [];
				} catch (e) {
					console.error('Error parsing cookie:', e);
					return [];
				}
			}
		});

		$(document).ready(function(){ 
			if($("#result").val() != ""){
				var toastExample = new bootstrap.Toast(document.getElementById('toastBox')); 
				toastExample.show();
			}
		});

		const collapseTable = document.getElementById('allActionsTable');
		const toggleButton = document.querySelector('[data-bs-toggle="collapse"]');

		collapseTable.addEventListener('show.bs.collapse', () => {
			toggleButton.textContent = 'Hide all actions';
		});

		collapseTable.addEventListener('hide.bs.collapse', () => {
			toggleButton.textContent = 'Show all actions';
		});
	</script>
</body>
</html>
