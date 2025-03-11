<?php
	include 'conn.php';
	include 'functions.php';
	
	// Initialize session if not already started
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<title>Opponents</title>
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

		.btn{
			font-size: 1em;
		}

		table .btn{
			font-size: 0.8em;
		}

		.new-opponent-container { 
			width: 100%; 
			display: flex; 
			margin-top: 10px; 
		} 

		#opponent-name{ 
			width: 70%; 
			margin-right: 10px; 
			font-size: 1em;
		} 

		#add-opponent{ 
			width: 30%; 
		}
		
		.team-name {
			vertical-align: middle;
		}
		
		.team-actions {
			text-align: center;
		}
		
		.no-records {
			text-align: center;
			padding: 20px;
		}
		
		.alert {
			margin-top: 10px;
			margin-bottom: 15px;
		}
	</style>

</head>
<body class="bg-dark text-light">
	<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
		<div class="container-fluid">
			<a class="back navbar-brand" href="index.php">⬅</a>
		</div>
	</nav>
	
	<div class="container-fluid mt-3">
		<div class="notice-container text-danger mb-2">
			注意:己有紀錄的不可刪除
		</div>
		
		<?php
		// Display success or error messages from session
		if(isset($_SESSION['success_message'])) {
			echo '<div class="alert alert-success alert-dismissible fade show">';
			echo htmlspecialchars($_SESSION['success_message']);
			echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
			unset($_SESSION['success_message']);
		}
		
		if(isset($_SESSION['error_message'])) {
			echo '<div class="alert alert-danger alert-dismissible fade show">';
			echo htmlspecialchars($_SESSION['error_message']);
			echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
			unset($_SESSION['error_message']);
		}
		?>
		
		<table class="table table-striped table-dark"> 
			<thead>
				<tr>
					<th style="width:90%;">Opponent</th>
					<th style="width:10%;"></th>
				</tr>
			</thead>
			<tbody>
				<?php
					$sql = "SELECT * FROM `team` ORDER BY `tname` ASC";
					$result = $conn->query($sql);
					if($result && $result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							echo "<tr>"; 
							echo "<td class='team-name'>" . htmlspecialchars($row["tname"]) . "</td>"; 

							// Check if this team has any matches
							$stmt = $conn->prepare("SELECT COUNT(*) as count FROM `matches` WHERE `tid` = ?");
							$stmt->bind_param("i", $row["tid"]);
							$stmt->execute();
							$result2 = $stmt->get_result();
							$matchCount = $result2->fetch_assoc()["count"];
							
							if($matchCount > 0){
								echo "<td class='team-actions'><button type='button' class='btn btn-outline-secondary btn-lg' disabled title='Cannot delete - has match records'><i class='fa-regular fa-trash-can'></i></button></td>";
							}
							else {
								echo "<td class='team-actions'><button type='button' class='btn btn-outline-danger btn-lg btnDelete' value='" . $row["tid"] . "' data-team-name=\"" . htmlspecialchars($row["tname"]) . "\" data-bs-toggle='modal' data-bs-target='#confirmDeleteModal' data-bs-backdrop='static'><i class='fa-regular fa-trash-can'></i></button></td>"; 
							}
							echo "</tr>";
						}
					}
					else { 
						echo "<tr><td colspan='2' class='no-records'>No opponents found</td></tr>";
					}
				?>
			</tbody>
		</table>
		
		<div class="d-grid gap-2 mt-3"> 
			<button type="button" class="btn btn-success btn-block" data-bs-toggle="collapse" data-bs-target="#new-opponent-form">
				<i class="fas fa-plus-circle me-2"></i>Add new opponent
			</button>
		</div>
		
		<form action="opponentsHandler.php" method="POST" class="mt-3">
			<input name="action" value="add" type="hidden">
			<div id="new-opponent-form" class="collapse">
				<div class="new-opponent-container"> 
					<input type="text" id="opponent-name" class="form-control input-new-opponent" placeholder="Opponent name" name="tname" required maxlength="50"> 
					<button type="submit" id="add-opponent" class="btn btn-outline-success">
						<i class="fas fa-check me-2"></i>Add
					</button>
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
						Are you sure you want to delete <span id="teamNameToDelete" class="fw-bold"></span>?
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

		<script> 
			$(document).ready(function(){ 
				var tid = 0;
				var teamName = '';
				
				// Ensure modal is at the body level for proper display
				$("#confirmDeleteModal").prependTo("body");
				
				// Get the team ID when delete button is clicked
				$('.btnDelete').click(function() {
					tid = $(this).val();
					teamName = $(this).data('team-name');
					$('#teamNameToDelete').text(teamName);
				});
				
				// Handle confirmation of deletion
				$('#btnConfirm').click(function() {
					window.location.href = 'opponentsHandler.php?action=delete&tid=' + tid;
				});
				
				// Auto-focus the input field when form is shown
				$('#new-opponent-form').on('shown.bs.collapse', function () {
					$('#opponent-name').focus();
				});
				
				// Auto-dismiss alerts after 5 seconds
				setTimeout(function() {
					$('.alert').alert('close');
				}, 5000);
			});
		</script>
	</div>
</body>
</html>