<!DOCTYPE html>
<html lang="en">
<?php 
	include 'conn.php'; 
	include 'functions.php';

	$acid = getAcid();
?>

<head>
	<title>Players</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Latest compiled and minified CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

	<!-- Latest compiled JavaScript -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

	<style> 
		body, html{
			margin: 0; 
			padding: 5px; 
			height: 100%; 
			width: 100%; 
			background-color: #1f1f1f;
		}

		body{
			font-size: 1.3em;
		}

		.btn{
			font-size: 1em;
		}

		.navbar-brand{
			font-size: inherit;
		}
		
		.back{
			font-size: 1.2em;
		}

		.notice-container{
			font-size: smaller;
		}

		.new-player-container { 
			width: 100%; 
			display: flex; 
			margin-top: 10px; 
		} 

		#player-name{ 
			width: 70%; 
			margin-right: 10px; 
			font-size: 1em;
		} 

		#add-player{ 
			width: 30%; 
		}

		.player-row:hover {
			background-color: #2a2a2a !important;
		}

		.alert {
			margin-top: 10px;
		}
	</style>

</head>
<body class="bg-dark text-light">
	<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
	<div class="container-fluid">
		<a class="back navbar-brand" href="index.php">⬅</a>
	</div>
	</nav>
		<div class="notice-container text-danger">
			注意:己有紀錄的不可刪除
		</div>

		<?php
		// Display success or error messages if they exist in the URL
		if(isset($_GET['status'])) {
			$status = $_GET['status'];
			$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
			
			if($status === 'success') {
				echo '<div class="alert alert-success alert-dismissible fade show">';
				echo $message ? $message : 'Operation completed successfully.';
				echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
			} else if($status === 'error') {
				echo '<div class="alert alert-danger alert-dismissible fade show">';
				echo $message ? $message : 'An error occurred.';
				echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
			}
		}
		?>

		<table class="table table-striped table-dark"> 
			<thead>
				<tr>
					<th style="width:80%;">Player</th>
					<th style="width:10%;"></th>
				</tr>
			</thead>
			<tbody>
				<?php
					// Use prepared statement to prevent SQL injection
					$stmt = $conn->prepare("SELECT * FROM `player` WHERE acid = ?");
					$stmt->bind_param("i", $acid);
					$stmt->execute();
					$result = $stmt->get_result();
					
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							if($row["pid"] == 0){
								continue;
							}
							echo "<tr class=\"player-row\">"; 
							echo "<td>" . htmlspecialchars($row["pname"]) . "</td>"; 

							// Check if player has any results
							$stmt2 = $conn->prepare("SELECT 1 FROM `result` WHERE pid = ? LIMIT 1");
							$stmt2->bind_param("i", $row["pid"]);
							$stmt2->execute();
							$result2 = $stmt2->get_result();
							
							if($result2->num_rows > 0){
								echo "<td><button type=\"button\" class=\"btn btn-outline-secondary btn-lg\" disabled title=\"Cannot delete player with existing records\"><i class=\"fa-regular fa-trash-can\"></i></button></td>";
							} else {
								echo "<td><button type=\"button\" class=\"btn btn-outline-danger btn-lg btnDelete\" value=\"" . $row["pid"] . "\" data-player-name=\"" . htmlspecialchars($row["pname"]) . "\" data-bs-toggle=\"modal\" data-bs-target=\"#confirmDeleteModal\" data-bs-backdrop=\"false\"><i class=\"fa-regular fa-trash-can\"></i></button></td>"; 
							}
							echo "</tr>";
							$stmt2->close();
						}
					}
					else { 
						echo "<tr><td colspan='2' class='text-center'>No players found</td></tr>";
					}
					$stmt->close();
				?>
			</tbody>
		</table>
	<div class="d-grid"> 
		<button type="button" class="btn btn-success btn-block" data-bs-toggle="collapse" data-bs-target="#new-player-form">
			<i class="fas fa-plus-circle me-2"></i>Add new player
		</button>
	</div>

	<form action="playersHandler.php" method="POST">
		<input name="action" value="add" type="hidden">
		<div id="new-player-form" class="collapse">
			<div class="new-player-container"> 
				<input type="text" id="player-name" class="form-control input-new-player" placeholder="Player" name="player" required maxlength="50"> 
				<input type="submit" id="add-player" class="btn btn-outline-success" value="Add" />
			</div>
		</div>
	</form>

	<!-- Modal -->
	<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content bg-dark text-light">
				<div class="modal-header">
					<h5 class="modal-title">Confirm Deletion</h5>
					<button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					Are you sure you want to delete <span id="playerNameToDelete" class="fw-bold"></span>?
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
					<button type="button" class="btn btn-danger" id="btnConfirm">Delete</button>
				</div>
			</div>
		</div>
	</div>

	<script> 
    	$(document).ready(function(){ 
    		var pid = 0;
			var playerName = '';
			
			$("#confirmDeleteModal").prependTo("body");
			
			$('.btnDelete').click(function() {
				pid = $(this).val();
				playerName = $(this).data('player-name');
				$('#playerNameToDelete').text(playerName);
			});
			
			$('#btnConfirm').click(function() {
				window.location.href = 'playersHandler.php?action=delete&pid=' + pid;
			});
			
			// Auto-dismiss alerts after 5 seconds
			setTimeout(function() {
				$('.alert').alert('close');
			}, 5000);
		});
    </script>
</body>
</html>