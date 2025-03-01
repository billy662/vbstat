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
		body, html { 
			margin: 0; 
			padding: 0; 
			height: 100%; 
			width: 100%;
			font-size: 1.5em;
		} 

		.main-container {
			padding: 10px; 
			color: white;
		}

		.navbar-brand {
			margin-right: 0;
		}

		.back{
			font-size: 1.4em;
		}

		table{
			width: 100%;
			height: 100%;
		}

		.player-container, .role-container, .in-play-container, .score-container, .error-container{
			margin-right: 0px;
			vertical-align: top;
			border-left: 1px solid white; 
			border-right: 1px solid white;
			text-align: -webkit-center;
			text-align: center;
			padding-left: 0;
			padding-right: 0;
		}

		/* Custom CSS for the scoreboard */
		#scoreboard {
            font-size: 3rem;
            font-weight: bold;
            text-align: center;
            background-color: #333;
            color: #fff;
            border-radius: 10px;
            width: 100%;
        }
		#scoreboard > span:first-child {
            color: #0f0;
        }
        #scoreboard > span:last-child {
            color: #f00;
        }

		.last-action-container{
			padding-right: 0;
		}

		.last-action-text {
            background-color: #343a40; /* Dark background color */
            color: #ffffff; /* White text color */
            border: 2px solid #ffffff; /* White border */
            padding: 5px; /* Padding inside the div */
            border-radius: 5px; /* Rounded corners */
			text-align: center;
			font-size: 0.85em;
        }

		#btnUndo{
			width: 100%;
		}

		.radio-container {
			margin: 5px;
		}

		.radio-input {
			display: none;
		}

		.radio-label {
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 100px;
			height: 100px;
			border: 3px solid #444444; /* Darker border */
			border-radius: 16px; /* Slightly larger radius */
			cursor: pointer;
			text-align: center;
			padding: 10px;
			transition: all 0.3s ease; /* Transition all properties */
			background-color: #2d2d2d; /* Dark background for button */
			color: #e0e0e0; /* Light gray text */
			font-weight: bold;
			font-size: 0.8em;
		}

		/* All player radio buttons */
		.label-pid{
			background-color :rgb(71, 107, 105);
		}

		/* All role radio buttons */
		.label-rid{
			background-color : #553e85;
		}

		/* All in-play radio buttons */
		.label-in-play{
			background-color : #003545;
		}

		/* All score radio buttons */
		.label-score{
			background-color : rgb(61, 109, 62);
		}

		/* All error radio buttons */
		.label-error{
			background-color : #ed6363;
		}
		
		.radio-input:checked + .radio-label {
			border-color: #4dabf7; /* Brighter blue for better contrast */
			background-color: rgba(77, 171, 247, 0.15); /* Subtle blue tint */
			color: #ffffff; /* Pure white when selected */
		}

		.radio-label:hover {
			border-color: #6c757d; /* Medium gray for hover */
			background-color: #373737; /* Slightly lighter background on hover */
		}

		/* Optional active state */
		.radio-input:active + .radio-label {
			transform: scale(0.98); /* Slight press effect */
		}

		.radio-container-grid {
			display: grid;
			grid-template-columns: repeat(2, 1fr);
			gap: 0px; /* Adjust the gap between columns as needed */
			width: fit-content;
		}

		.radio-container-grid .span-2-columns {
			grid-column: span 2;
		}

		.error-container .radio-label,
		.in-play-container .radio-label{
			width: 125px;
		}

		/* All disaled raido buttons */
		:disabled + .radio-label {
			background-color: #333;
		}

		.all-actions-container{
			width: 100%;
		}

		#allActionsTable{
			font-size: 0.69em;
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
	<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 11"> 
		<div id="toastBox" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000"> 
			<div class="toast-header"> 
				<strong class="me-auto"></strong> 
				<button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button> 
			</div> 
			<div class="toast-body"> 
				<?php echo (isset($_GET['result'])) ? $_GET['result'] : '';  ?> 
			</div> 
		</div>
	</div>

	<div id="scoreboard">
		<?php
			//Get scoreboard score
			$scoreboard = getScoreboard($conn, $sid);

			// Format the output
			echo "<span>{$scoreboard['total_scored']}</span> : <span>{$scoreboard['total_lost']}</span>";
		?>
	</div>
	
	<form action="statHandler.php" method="GET">
		<input type="hidden" name="mid" value="<?php echo $mid; ?>">
		<input type="hidden" name="sid" value="<?php echo $sid; ?>">
		<input type="hidden" name="action" value="add">
		<div class="main-container">
			<div class="last-action-container container-fluid row">
				<div class="col-9 justify-content-center">
					<div class="text-center">
						<!-- Dark themed div with border -->
						<div class="last-action-text">
							Last action:
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
									echo "{$row['pname']} ({$row['rName']}) - {$row['aname']}";
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
					<div class="col-3">
						<button type="button" class="btn btn-danger btn-custom bottom px-1" id="btnUndo" style="margin-left: 5px;" onclick="location.href='statHandler.php?action=undo&mid=<?php echo $mid; ?>&sid=<?php echo $sid; ?>'">Undo</button>
					</div>
				<?php
					endif;
				?>

			</div>

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
						<td class="player-container" style="text-align: -webkit-center;">
							<div class="radio-container-grid">
								<!-- create cookie that saves the player's last role -->
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
						<td class="role-container"  style="text-align: -webkit-center;">
							<div style="width: fit-content;">
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
						<td class="in-play-container" style="text-align: -webkit-center;">
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
						<td class="score-container" style="text-align: -webkit-center;">
							<div style="width: fit-content;">
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
						<td class="error-container" style="text-align: -webkit-center;">
							<div style="width: fit-content;">
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
	</form>

	<div class="all-actions-container mt-1">
			<button class="btn btn-secondary mb-2 ms-1" type="button" data-bs-toggle="collapse" data-bs-target="#allActionsTable" aria-expanded="false" aria-controls="allActionsTable">
			Show all actions
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
				
				echo "<span style=\"color: white\"><b>Total: $rowsCount</b></span><br>";
				
				if($rowsCount > 0){
			?>
			
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
							echo "<td>{$row['player_name']}</td>";
							echo "<td>{$row['role_name']}</td>";
							echo "<td>{$row['action_name']}</td>";
							echo "</tr>";
						}
					?>
				</tbody>
			</table>
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
				.then(response => response.json())
				.then(pairs => {
					validPairs = pairs; // Store valid pairs globally
					updateAidRadioButtons(); // Initial update based on the default selected rid
				})
				.catch(error => console.error('Error fetching valid pairs:', error));

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
					//get player id and role id
					const selectedPid = document.querySelector('input[name="pid"]:checked').value;
					const selectedRid = document.querySelector('input[name="rid"]:checked').value;

					selectedRidPidPair = getSelectedRidPidPair();

					// Check if selectedPid is in the array
					let pidFound = false;
					for (let i = 0; i < selectedRidPidPair.length; i++) {
						if (selectedRidPidPair[i][0] == selectedPid) {
							selectedRidPidPair[i][1] = selectedRid;
							pidFound = true;
							break;
						}
					}

					// If selectedPid is not found, add the pair to the array
					if (!pidFound) {
						selectedRidPidPair.push([selectedPid, selectedRid]);
					}

					// Update the cookie with the new array
					document.cookie = `selectedridpidpair=${JSON.stringify(selectedRidPidPair)}; path=/`;

					//submit the form
					document.querySelector('form').submit();
				});
			});

			// Function to update rid radio buttons based on the selected pid
			function updateRidRadioButtons() {
				const selectedPid = document.querySelector('input[name="pid"]:checked').value;
				const ridInputs = document.querySelectorAll('input[name="rid"]');

				selectedRidPidPair = getSelectedRidPidPair();

				// Check if selectedPid is in the array
				let pidFound = false;
				for (let i = 0; i < selectedRidPidPair.length; i++) {
					if (selectedRidPidPair[i][0] == selectedPid) {
						const selectedRid = selectedRidPidPair[i][1];
						const selectedRidInput = document.querySelector(`input[name="rid"][value="${selectedRid}"]`);
						if (selectedRidInput) {
							selectedRidInput.checked = true;
							selectedRidInput.dispatchEvent(new Event('change'));
						}
						break;
					}
				}	

				if (!pidFound) {
					ridInputs.forEach(ridInput => {
						const rid = parseInt(ridInput.value);
						if (selectedPid == 0) {
							ridInput.disabled = rid !== 7;
							ridInput.checked = rid === 7;
							ridInput.dispatchEvent(new Event('change'));
						} else {
							ridInput.disabled = rid === 7;
							if (rid === 7) {
								ridInput.checked = false;
								ridInput.dispatchEvent(new Event('change'));
							}
						}
					});
				}

				// Uncheck all aid buttons if no rid button is checked
				const aidInputs = document.querySelectorAll('input[name="aid"]');
				const isRidChecked = Array.from(ridInputs).some(input => input.checked);
				aidInputs.forEach(aidInput => {
					if (!isRidChecked) {
						aidInput.checked = false;
					}
				});
			}
			

			// Function to update aid radio buttons based on the selected rid
			function updateAidRadioButtons() {
				const isPidChecked = document.querySelector('input[name="pid"]:checked') !== null;
				const selectedRid = document.querySelector('input[name="rid"]:checked')?.value;
				const aidInputs = document.querySelectorAll('input[name="aid"]');

				aidInputs.forEach(aidInput => {
					const aid = parseInt(aidInput.value);

					// Disable the aid radio button if no pid radio button is checked
					aidInput.disabled = !isPidChecked;

					// Check if the pair (selectedRid, aid) exists in the valid pairs
					const isValid = validPairs.some(pair => pair.rid == selectedRid && pair.aid == aid);

					// Enable or disable the aid radio button based on validity
					aidInput.disabled = !isValid || !isPidChecked;

					// Uncheck the aid radio button if it is disabled
					if (aidInput.disabled && aidInput.checked) {
						aidInput.checked = false;
					}
				});
			}

			
			// Get cookies and parse the selectedridpidpair cookie
			function getSelectedRidPidPair() {
				const cookies = document.cookie.split(';').reduce((acc, cookie) => {
					const [key, value] = cookie.split('=').map(c => c.trim());
					acc[key] = value;
					return acc;
				}, {});
				return cookies.selectedridpidpair ? JSON.parse(cookies.selectedridpidpair) : [];
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
			toggleButton.textContent = 'Hide';
		});

		collapseTable.addEventListener('hide.bs.collapse', () => {
			toggleButton.textContent = 'Show all actions';
		});
	</script>
</body>
</html>