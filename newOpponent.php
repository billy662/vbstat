<!DOCTYPE html>
<html lang="en">
<head>
	<title>New Opponent</title>
	<meta charset="utf-8">
	<!-- Latest compiled and minified CSS -->
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

	<!-- Latest compiled JavaScript -->
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

	<style> 
		body, html{
			margin: 0; 
			padding: 5px; 
			height: 100%; 
			width: 100%;
			font-family: Arial, sans-serif; 
			font-size: 1.5em;
		} 

		.navbar-brand{
			font-size: inherit;
		}
	</style>

</head>
<body class="bg-dark">
	<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
	<div class="container-fluid">
		<a class="back navbar-brand" href="opponents.php">⬅</a>
	</div>
	</nav>

	<div class="container-fluid">
		<form action="opponentsHandler.php">
			<input name="action" value="add" style="display: none;">
			<table class="table table-striped table-dark"> 
				<thead>
					<tr>
						<th>Team Name</th>
						<th>Team Rating</th>
						<th>Team Grade</th>
					</tr>
				</thead>
				<tbody>
					<tr> 
						<td>
							<input type="text" id="tname" class="form-control" placeholder="Team Name" name="tname" required>
						</td>
						<td>
                            <select name="trate" class="form-control">
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10">10</option>
                            </select>
						</td>
						<td>
							<select name="tgrade" class="form-control">
								<option value="甲一">甲一</option>
								<option value="甲二">甲二</option>
								<option value="乙組">乙組</option>
								<option value="丙組">丙組</option>
								<option value="其他">其他</option>
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
