<!DOCTYPE html>
<html lang="en">
<?php 
	include 'conn.php';	
	// Set default date to today
	$today = date("Y-m-d");
?>
<head>
	<title>New Match</title>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
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

		.page-title {
			color: white;
			margin: 15px 0;
			text-align: center;
		}

		@media (max-width: 768px) {
			body, html {
				font-size: 1.2em;
			}
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
    	<h2 class="page-title">Add New Match</h2>
    	<form action="matchHandler.php" method="POST" id="matchForm">
    		<input name="action" value="add" type="hidden">
	        <table class="table table-striped table-dark">
	            <tbody>
	                <tr>
	                    <td class="col">
	                    	Date
	                	</td>
	                    <td class="col-7">
	                    	<input type="date" id="date" name="date" value="<?php echo $today; ?>" required class="form-control">
	                    </td>
	                </tr>
	                <tr>
	                    <td>
	                    	Type of match
	                    </td>
	                    <td>
	                    	<select name="type" class="form-control" required>
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
							<select name="tid" class="form-control" required>
								<?php
									$sql = "SELECT * FROM `team` ORDER BY tname";
									$result = $conn->query($sql);
									if($result->num_rows > 0){
										while($row = $result->fetch_assoc()){
											echo '<option value="' . $row["tid"] . '">' . $row["tname"] . '</option>';
										}
									} else {
										echo '<option value="">No teams found</option>';
									}
								?>
							</select>
	                    </td>
	                </tr>
					<tr>
						<td>
							組別
						</td>
						<td>
							<select name="tgrade" class="form-control" required>
								<option value="甲一">甲一</option>
								<option value="甲二">甲二</option>
								<option value="乙組">乙組</option>
								<option value="丙組">丙組</option>
								<option value="其他">其他</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>
							Rating
						</td>
						<td>
							<select name="trate" class="form-control" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5" selected>5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>
						</td>
					</tr>
					<tr>
						<td>
							YouTube
						</td>
						<td>
							<input type="text" name="youtube" class="form-control" placeholder="Enter full YouTube URL">
						</td>
					</tr>

	            </tbody>
	        </table>

	        <div class="d-grid mb-4"> 
		    	<button type="submit" class="btn btn-success btn-block">Add Match</button> 
		    </div>
		</form>
    </div>
</body>
</html>