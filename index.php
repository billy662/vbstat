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
	<!-- Latest compiled and minified CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

	<!-- Latest compiled JavaScript -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

	<style> 
		body{
			font-size: 2.3em;
		}

		.btn{
			font-size: 1em;
		}
		
		.table-responsive {
			margin-top: 20px;
		}
	
		#btnPlayers, #btnOpponents{
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
	        			<td class="mt-2">Date</td>
	        			<td class="mt-2">Type</td>
	        			<td class="mt-4">VS</td>
	        			<td class="mt-1">Sets</td>
	        			<td class="mt-1"></td>
	        		</tr>
	        	</thead>
	            <tbody>
	            	<?php
						$sql = "SELECT * FROM `matches` WHERE acid = " . $acid;
						$result = $conn->query($sql);
						if($result->num_rows > 0){
							while($row = $result->fetch_assoc()){
								echo "<tr>"; 
								echo '<td><a href="set.php?mid='. $row["mid"] . '">'. $row["date"] ."</a></td>"; 
								echo "<td>" . $row["type"] . "</td>"; 
								
								// Get the team name for this match
								$sql = "SELECT tname FROM team WHERE tid = " . $row["tid"];
								$team_result = $conn->query($sql);
								$team_row = $team_result->fetch_assoc();
								echo "<td>" . $team_row["tname"] . "</td>";

								// Count the number of sets for this match
								$sql = "SELECT COUNT(*) AS set_count FROM sets WHERE sets.mid = " . $row["mid"];
								$set_result = $conn->query($sql);
								if($set_result->num_rows > 0){
									$set_row = $set_result->fetch_assoc();
									echo "<td>" . $set_row["set_count"] . "</td>"; 
								}
								else{
									echo "<td>0</td>";
								}
								echo "<td><button type=\"button\" class=\"btn btn-outline-danger btn-large btnDelete\" data-bs-toggle=\"modal\" data-bs-target=\"#confirmDeleteModal\" data-bs-backdrop=\"false\" value=\"" . $row["mid"] . "\"><i class=\"fa-regular fa-trash-can\"></i></button></td>"; 
								echo "</tr>";
							}
						}
						else { 
							echo "<tr><td colspan='5'>No results found</td></tr>";
						}
						$conn->close();
					?>

					<!-- Modal -->
					<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
					  <div class="modal-dialog">
					    <div class="modal-content">
					      <div class="modal-header">
					        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					      </div>
					      <div class="modal-body">
					        Are you sure you want to delete?
					      </div>
					      <div class="modal-footer">
					        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					        <input type="submit" class="btn btn-danger" id="btnConfirm" value="Delete">
					      </div>
					    </div>
					  </div>
					</div>
	            </tbody>
	        </table>
	    </div>
    </div>
	<nav class="navbar navbar-dark navbar-bottom">
		<div class="container-fluid">
			<button class="btn btn-outline-danger" id="btnLogout">Logout</button>
		</div>
	</nav>
    <script> 
    	$(document).ready(function(){ 
    		var mid = 0;
			$("#confirmDeleteModal").prependTo("body");
			$('.btnDelete').click(function() {
				mid = $(this).val(); 
			});
			$('#btnConfirm').click(function() {
				window.location.href = 'matchHandler.php?action=delete&mid=' + mid;
			});
			$('#btnLogout').click(function() {
				document.cookie = "acid=; expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/;";
				window.location.href = 'login.php';
			});
		});
    </script>
</body>

</html>