<?php
	include 'conn.php';
	include 'functions.php';
?>


<!DOCTYPE html>
<html lang="en">
<head>
	<title>Opponents</title>
	<meta charset="utf-8">
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
			font-size: 2.3em;
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
	</style>

</head>
<body class="bg-dark">
	<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
	<div class="container-fluid">
		<a class="back navbar-brand" href="index.php">⬅</a>
	</div>
	</nav>
		<div class="notice-container text-danger">
			注意:己有紀錄的不可刪除
		</div>
		<table class="table table-striped table-dark"> 
			<thead>
				<tr>
					<th style="width:90%;">Opponent</th>
					<th style="width:10%;"></th>
				</tr>
			</thead>
			<tbody>
				<?php
					$sql = "SELECT * FROM `team`";
					$result = $conn->query($sql);
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							echo "<tr>"; 
							echo "<td>" . $row["tname"] . "</td>"; 

							// Check if this team has any matches
                            $sql = "SELECT * FROM `matches` WHERE `tid` = " . $row["tid"];
                            $result2 = $conn->query($sql);
                            if($result2->num_rows > 0){
                                echo "<td></td>";
                            }
                            else {
                                echo "<td><button type=\"button\" class=\"btn btn-outline-danger btn-lg btnDelete\" value=\"" . $row["tid"] . "\" data-bs-toggle=\"modal\" data-bs-target=\"#confirmDeleteModal\" data-bs-backdrop=\"false\"><i class=\"fa-regular fa-trash-can\"></i></button></td>"; 
                            }
							echo "</tr>";
						}
					}
					else { 
						echo "<tr><td colspan='3'>No results found</td></tr>";
					}
					$conn->close();
				?>
			</tbody>
		</table>
		
		<div class="d-grid"> 
			<button type="button" class="btn btn-success btn-block" data-bs-toggle="collapse" data-bs-target="#new-opponent-form">Add new opponent</button>
		</div>
		
		<form action="opponentsHandler.php">
			<input name="action" value="add" style="display: none;">
			<div id="new-opponent-form" class="collapse">
				<div class="new-opponent-container"> 
					<input type="text" id="opponent-name" class="form-control input-new-opponent" placeholder="Opponent" name="tname" required> 
					<input type="submit" id="add-opponent" class="btn btn-outline-success" value="Add" />
				</div>
			</div>
		</form>

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

		<script> 
			$(document).ready(function(){ 
				var tid = 0;
				$("#confirmDeleteModal").prependTo("body");
				$('.btnDelete').click(function() {
					tid = $(this).val(); 
				});
				$('#btnConfirm').click(function() {
					window.location.href = 'opponentsHandler.php?action=delete&tid=' + tid;
				});
			});
		</script>
</body>
</html>
