<!DOCTYPE html>
<html lang="en">
<?php 
	include 'conn.php'; 
	include 'functions.php';

    $mid = $_GET['mid'];
?>

<head>
	<title>Sets</title>
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

		.btn{
			font-size: inherit;
		}

		.btnDelete{
			font-size: smaller;
		}

		.navbar-brand{
			font-size: inherit;
		}

		.back{
			font-size: 1.2em;
		}

		.export-btn {
            width: 100%; /* Set the width of the button */
            display: flex;
            align-items: center;
            justify-content: center;
            background-color:rgb(32, 102, 176);
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            gap: 8px; /* Add space between text and icon */
			margin-bottom: 10px;
			padding: 6px 12px;
        }

        .export-btn:hover {
            background-color: #0056b3; /* Darker shade on hover */
        }

		.new-set-container { 
			width: 100%; 
			display: flex; 
			margin-top: 10px; 
		} 

		#points{ 
			width: 80%; 
			margin-right: 10px; 
			font-size: 1em;
		} 

		#add-set{ 
			width: 30%; 
		}

		.information-card {
			background-color: #2a2a2a;
			color: white;
			border-radius: 10px;
			padding: 20px;
			margin: 20px;
			text-align: center;
			box-shadow: 0px 0px 10px 0px rgba(255, 255, 255, 0.75);
			font-weight: bold;
		}

		#edit-match-form input{
			font-size: 0.8em;
			margin: 30px;
		}

	</style>

</head>
<body class="bg-dark">
	<nav class="navbar navbar-expand-sm bg-dark navbar-dark">
		<div class="container-fluid">
			<a class="back navbar-brand" href="index.php">⬅</a>
			<a class="navbar-brand ms-auto">
				<?php
					$sql = "SELECT matches.date AS date, matches.type AS type, team.tname AS tname FROM matches, team WHERE matches.tid = team.tid AND matches.mid = $mid;";
					$result = $conn->query($sql);
					if($result->num_rows > 0){
						$row = $result->fetch_assoc();
						echo $row["date"] . " " . $row["type"] . " VS " . $row["tname"];
					}
					else{
						echo "ERROR";
					}
				?>
			</a>
		</div>
	</nav>

	<div class="information-card">
		<?php
			// Fetch match data
			$query = "SELECT tgrade, trate, youtube FROM matches WHERE mid = ?";
			$stmt = $conn->prepare($query);
			$stmt->bind_param("i", $mid);
			$stmt->execute();
			$result = $stmt->get_result();
			$match = $result->fetch_assoc();
		?>
		<p>組別: <?php echo htmlspecialchars($match['tgrade'] ?? 'N/A'); ?></p>
		<p>Rating: <?php echo htmlspecialchars($match['trate'] ?? 'N/A'); ?></p>
		<?php
			$youtube = "";
			if($match['youtube'] != null || $match['youtube'] == "")
				$youtube = $match['youtube'];
			echo "<a href=\"". $youtube ."\">YouTube</a>";
		?>
		<div style="position: relative;">
			<div style="position: absolute; bottom: 0; right: 0;">
				<button type="button" id="btnEditMatch" class="btn btn-primary" data-bs-toggle="collapse" data-bs-target="#edit-match-form">Edit</button>
			</div>
		</div>

		<form action="matchHandler.php">
			<input name="action" value="edit" style="display: none;">
			<div id="edit-match-form" class="collapse">
				<div class="edit-match-container d-flex align-items-center"> 
					<input type="text" name="youtube" class="form-control flex-grow-1 me-2" value="<?php echo $youtube; ?>">
					<input type="submit" id="add-set" class="btn btn-outline-success" value="Ok" />
					<input type="text" name="mid" value="<?php echo $mid; ?>" style="display: none;">
				</div>
			</div>
		</form>
	</div>

	<table class="table table-striped table-dark"> 
		<thead>
			<tr>
				<th style="width:40%;"></th>
				<th style="width:25%;">Score</th>
				<th style="width:15%;">總分</th>
				<th style="width:10%;"></th>
			</tr>
		</thead>
		<tbody>
			<?php
				$sql = "SELECT * FROM `sets` WHERE mid = $mid";
				$result = $conn->query($sql);
				if($result->num_rows > 0){
					while($row = $result->fetch_assoc()){
						echo "<tr>"; 
						echo '<td><a href="stats.php?mid='. $mid . '&sid='. $row["sid"] . '">Set '. $row["setNo"] ."</a></td>"; 
						$scoreboard = getScoreboard($conn, $row["sid"]);
						echo "<td class=\"fw-bold\">{$scoreboard["total_scored"]} : {$scoreboard["total_lost"]}</td>";
						echo "<td>" . $row["points"] . "</td>";
						echo "<td><button type=\"button\" class=\"btn btn-outline-danger btn-lg btnDelete\" value=\"" . $row["sid"] . "\" data-bs-toggle=\"modal\" data-bs-target=\"#confirmDeleteModal\" data-bs-backdrop=\"false\"><i class=\"fa-regular fa-trash-can\"></i></button></td>"; 
					}
				}
				else { 
					echo "<tr><td colspan='4'>No results found</td></tr>";
				}
			?>
		</tbody>
	</table>
	<div class="d-grid"> 
        <?php
            $sql = "SELECT COUNT(*) AS setNo FROM sets WHERE sets.mid = $mid";
            $set_result = $conn->query($sql);
            if($set_result->num_rows > 0){
                $set_row = $set_result->fetch_assoc();
                $setNo = $set_row["setNo"];
            }
            else{
                $setNo = 0;
            }
        ?>
		
		<?php
			// Show the export button only when there is data in the result table for this match
            $sql = "SELECT * FROM `result` JOIN `sets` ON `result`.`sid` = `sets`.`sid` JOIN `matches` ON `sets`.`mid` = `matches`.`mid` WHERE `matches`.`mid` = $mid";
            $result = $conn->query($sql);
            if($result->num_rows > 0){
                ?>
				<button class="export-btn">
					<span>Export</span>
					<i class="fas fa-download"></i> <!-- Bootstrap download icon -->
				</button>
				<?php
            }
		?>
		
		<button type="button" class="btn btn-success btn-block" data-bs-toggle="collapse" data-bs-target="#new-set-form">Add Set <?php  echo $setNo + 1; ?></button>
	</div>

    <form action="setHandler.php">
		<input name="action" value="add" style="display: none;">
		<div id="new-set-form" class="collapse">
			<div class="new-set-container"> 
				<div class="points-label text-white me-3">
					<label for="points">Points:</label>
				</div>
                <select name="points" class="form-control me-3">
                    <option value="25">25</option>
                    <option value="21">21</option>
                    <option value="15">15</option>
                    <option value="11">11</option>
                </select>
                <input type="submit" id="add-set" class="btn btn-outline-success" value="Add" />
                <input type="text" name="mid" value="<?php echo $mid; ?>" style="display: none;">
                <input type="text" name="setNo" value="<?php echo $setNo + 1; ?>" style="display: none;">
			</div>
		</div>

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
    <?php
        $conn->close();
    ?>
	<script> 
		function isValidHttpUrl(string) {
			let url;
			
			try {
				url = new URL(string);
			} catch (_) {
				return false;  
			}

			return url.protocol === "http:" || url.protocol === "https:";
		}

    	$(document).ready(function(){ 
    		var sid = 0;
			$("#confirmDeleteModal").prependTo("body");
			$('.btnDelete').click(function() {
				sid = $(this).val(); 
			});
			$('#btnConfirm').click(function() {
				window.location.href = 'setHandler.php?action=delete&sid=' + sid + '&mid=' + <?php echo $mid; ?>;
			});
			$('.export-btn').click(function() {
				window.location.href = 'exportToCSV.php?mid=' + <?php echo $mid; ?>;
			});
		});
    </script>
</body>
</html>