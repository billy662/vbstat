<!DOCTYPE html>
<html lang="en">
<?php 
	include 'conn.php'; 
?>

<head>
	<title>Login</title>
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
            color: white;
		}

        .btn{
            margin-top: 38px;
            font-size: 2em;
            font-weight: bold;
            width: 100%;
            height: 3em;
        }
        
	</style>

</head>
<body class="bg-dark">
    <div class="container-fluid mt-5">
        <div class="text-center">
            <b>Login as:</b><br>
        </div>
        <?php
            $sql = "SELECT * FROM `accounts`";
            $result = $conn->query($sql);
            if($result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                    echo '<form action="loginHandler.php" method="post">';
                    echo '<input type="hidden" name="acid" value="' . $row["acid"] . '">';
                    echo '<button class="btn btn-secondary">' . $row["username"] . '</button><br>';
                    echo '</form>';
                }
            }
        ?>
    </div>
    <script> 
    	
    </script>
</body>

</html>