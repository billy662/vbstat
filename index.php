<!DOCTYPE html>
<html lang="en">
<?php 
	include 'conn.php'; 
	include 'functions.php';

	$acid = getAcid();
?>

<head>
	<title>Matches</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Latest compiled and minified CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

	<!-- Latest compiled JavaScript -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

	<style> 
		body {
			font-size: 0.9em;
		}

		.btn {
			font-size: 1em;
		}

		.btnDelete {
			font-size: 0.8em;
		}
		
		.table-responsive {
			margin-top: 20px;
		}
	
		#btnPlayers, #btnOpponents {
			width: 50%;
		}

		.navbar-bottom {
			position: fixed;
			bottom: 0;
			width: 100%;
			background-color: #343a40;
			z-index: 1030;
			padding: 10px;
		}

		.action-buttons {
			display: flex;
			gap: 5px;
		}
	</style>

</head>
<body class="bg-dark">
    <div class="container-fluid mt-5">
	    <div class="d-grid">
	    	<button class="btn btn-success btn-block" onclick="location.href='newMatch.php'">Add New Match</button> 
	    	<div class="d-flex">
				<button class="btn btn-secondary btn-custom bottom mt-2 px-1" id="btnPlayers" style="margin-right: 5px;" onclick="location.href='players.php'">Players</button>
				<button class="btn btn-secondary btn-custom bottom mt-2 px-1" id="btnOpponents" style="margin-left: 5px;" onclick="location.href='opponents.php'">Opponents</button>
			</div>
	    </div>
    	<div class="table-responsive">
	        <table class="table table-striped table-dark">
	        	<thead>
	        		<tr>
	        			<th class="mt-2">Date</th>
	        			<th class="mt-2">Type</th>
	        			<th class="mt-4">VS</th>
	        			<th class="mt-1">Sets</th>
	        			<th class="mt-1"></th>
	        		</tr>
	        	</thead>
	            <tbody>
	            	<?php
						try {
							// Use prepared statement to prevent SQL injection
							$stmt = $conn->prepare("SELECT * FROM `matches` WHERE acid = ?");
							$stmt->bind_param("i", $acid);
							$stmt->execute();
							$result = $stmt->get_result();
							
							if($result->num_rows > 0){
								while($row = $result->fetch_assoc()){
									echo "<tr>"; 
									echo '<td><a href="set.php?mid='. htmlspecialchars($row["mid"], ENT_QUOTES, 'UTF-8') . '">'. htmlspecialchars($row["date"], ENT_QUOTES, 'UTF-8') ."</a></td>"; 
									echo "<td>" . htmlspecialchars($row["type"], ENT_QUOTES, 'UTF-8') . "</td>"; 
									
									// Get the team name for this match
									$team_stmt = $conn->prepare("SELECT tname FROM team WHERE tid = ?");
									$team_stmt->bind_param("i", $row["tid"]);
									$team_stmt->execute();
									$team_result = $team_stmt->get_result();
									$team_row = $team_result->fetch_assoc();
									echo "<td>" . htmlspecialchars($team_row["tname"] ?? 'Unknown', ENT_QUOTES, 'UTF-8') . "</td>";

									// Count the number of sets for this match
									$set_stmt = $conn->prepare("SELECT COUNT(*) AS set_count FROM sets WHERE sets.mid = ?");
									$set_stmt->bind_param("i", $row["mid"]);
									$set_stmt->execute();
									$set_result = $set_stmt->get_result();
									if($set_result->num_rows > 0){
										$set_row = $set_result->fetch_assoc();
										echo "<td>" . htmlspecialchars($set_row["set_count"], ENT_QUOTES, 'UTF-8') . "</td>"; 
									}
									else{
										echo "<td>0</td>";
									}
									echo "<td><button type=\"button\" class=\"btn btn-outline-danger btn-large btnDelete\" data-bs-toggle=\"modal\" data-bs-target=\"#confirmDeleteModal\" data-mid=\"" . htmlspecialchars($row["mid"], ENT_QUOTES, 'UTF-8') . "\"><i class=\"fa-regular fa-trash-can\"></i></button></td>"; 
									echo "</tr>";
								}
							}
							else { 
								echo "<tr><td colspan='5' class='text-center'>No matches found</td></tr>";
							}
						} catch (Exception $e) {
							echo "<tr><td colspan='5' class='text-center text-danger'>Error: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</td></tr>";
						} finally {
							$conn->close();
						}
					?>
	            </tbody>
	        </table>
	    </div>
    </div>
	
	<!-- Modal -->
	<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
	  <div class="modal-dialog">
	    <div class="modal-content bg-dark text-light">
	      <div class="modal-header">
	        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Delete</h5>
	        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
	      </div>
	      <div class="modal-body">
	        Are you sure you want to delete this match?
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
	        <button type="button" class="btn btn-danger" id="btnConfirm">
	          <i class="fas fa-trash-alt me-2"></i>Delete
	        </button>
	      </div>
	    </div>
	  </div>
	</div>
	
	
	<nav class="navbar navbar-dark navbar-bottom">
		<div class="container-fluid">
			<button class="btn btn-outline-danger" id="btnLogout">Logout</button>
		</div>
	</nav>
    <script> 
    	$(document).ready(function(){ 
    		let mid = 0;
			
			$('.btnDelete').click(function() {
				mid = $(this).data('mid'); 
			});
			
			$('#btnConfirm').click(function() {
				if (mid > 0) {
					window.location.href = 'matchHandler.php?action=delete&mid=' + mid;
				}
			});
			
			$('#btnLogout').click(function() {
				document.cookie = "acid=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/;";
				window.location.href = 'login.php';
			});
			
			// Reset modal data when hidden
			$('#confirmDeleteModal').on('hidden.bs.modal', function () {
				mid = 0;
			});
		});
    </script>
</body>
</html>