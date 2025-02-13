<!DOCTYPE html>
<html lang="en">
<?php 
	include 'conn.php';	
?>
<head>
	<title>New Match</title>
	<meta charset="utf-8">
	<!-- Latest compiled and minified CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

	<!-- Latest compiled JavaScript -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

	<style> 
		body, html { 
			margin: 0; 
			padding: 0; 
			height: 100%; 
			width: 100%;
			font-size: 1.5em;
		} 

		.navbar-brand{
			font-size: inherit;
		}

		.btn{
			font-size: 1em;
		}
		
		.back{
			font-size: 1.2em;
		}
	</style>

</head>
<body class="bg-dark">
	<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
	<div class="container-fluid">
		<a class="back navbar-brand" href="javascript:history.back()">⬅</a>
	</div>
	</nav>
    <div class="container-fluid">
    	<form action="matchHandler.php">
    		<input name="action" value="add" style="display: none;">
	        <table class="table table-striped table-dark">
	            <tbody>
	                <tr>
	                    <td class="col">
	                    	Date
	                	</td>
	                    <td class="col-7">
	                    	<input type="date" id="date" name="date" required>
	                    </td>
	                </tr>
	                <tr>
	                    <td>
	                    	Type of match
	                    </td>
	                    <td>
	                    	<select name="type" class="form-control">
	                    		<option>Friendly</option>
	                    		<option>聯賽</option>
	                    		<option>錦標賽</option>
	                    		<option>區賽</option>
	                    		<option>私league</option>
	                    		<option>港運</option>
	                    	</select>
	                    </td>
	                </tr>
	                <tr>
	                    <td>
	                    	Opponent
	                	</td>
	                    <td>
							<select name="tid" class="form-control">
								<?php
									$sql = "SELECT * FROM `team`";
									$result = $conn->query($sql);
									if($result->num_rows > 0){
										while($row = $result->fetch_assoc()){
											echo '<option value="' . $row["tid"] . '">' . $row["tname"] . '</option>';
										}
									}
								?>
							</select>
	                    </td>
	                </tr>

	            </tbody>
	        </table>

	        <div class="d-grid"> 
		    	<input type="submit" class="btn btn-success btn-block" value="Add"> 
		    </div>
		</form>
    </div>
    <script> 
    	document.getElementById('sets').addEventListener('input', function (e) { 
    		this.value = this.value.replace(/[^0-9]/g, ''); 
    	});
    </script>
</body>

</html>