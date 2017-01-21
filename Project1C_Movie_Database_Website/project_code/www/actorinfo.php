<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Actor Info</title>
		<!--<link rel="import" href="header.php"> <!-- here or use php?? FIX -->
	</head>

	<body>
	<?php
		include 'header.php';
	?> 

		<div class="container">
			<h2>Get Actor Info</h2>
			<label for="search_input">Search:</label>
			<form class="form-group" method="GET" role= "search" id="usrform" action="search.php">
				<input type="hidden" name="searchtype" value="actor">
				<div class="input-group">
					<input type="text" name="searchterm" class="form-control" placeholder="Search for actor">
				<div class="input-group-btn">
					<button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
          		</div>
			</form>
		</div>

	<?php
		if ($_GET["id_a"])
		{
			$db = new mysqli('localhost', 'cs143', '', 'CS143'); //username cs143, password empty, database CS143
	    	if($db->connect_errno > 0)
    			die('Unable to connect to database [' . $db->connect_error . ']');

    		$pid = $_GET["id_a"];
    		$query = "SELECT CONCAT_WS(' ', first, last), sex, dob, dod FROM Actor WHERE id =$pid;";
    		if (!($result = $db->query($query)))
    		{
    			$err = $db->error;
    			echo "Error: $err<br />";
    			exit(1);
    		}

    		echo "<h3>Basic Info: </h3>";

    		echo "<div class='table-responsive'><table class='table table-bordered table-condensed table-hover'>";

    		//fill first row of table with field names
    		echo "<thead><tr><td>Name</td><td>Sex</td><td>Date of Birth</td><td>Date of Death</td></tr></thead><tbody>";

			//fill in rest of the table rows with tuples returned
    		$row = $result->fetch_row();
    		$name = urlencode($row[0]);
    		$url_id = urlencode($pid);

    		echo "<tr>";
    		for($i = 0; $i < $result->field_count; $i++)
    		{
   				$val = $row[$i];
   				if ($val == NULL)
    				$val = "N/A";
    			echo "<td>$val</td>";
    		}
    		echo "</tr>";
			echo "</tbody></table></div>";

			//get more info
			$query = "SELECT mid, title, role, year FROM MovieActor, Movie WHERE aid =$pid AND mid=id ORDER BY title ASC;";

    		if (!($result = $db->query($query)))
    		{
    			$err = $db->error;
    			echo "Error: $err<br />";
    			exit(1);
    		}

    		echo "<h3>Movies and Roles: </h3>";

    		echo"<a href='addactormovie.php?id_a=$url_id&name=$name'><h5>Add Movie to Actor</h5></a>";

    		echo "<div class='table-responsive'><table class='table table-bordered table-condensed table-hover'>";

    		//fill first row of table with field names
    		echo "<thead><tr><td>Title</td><td>Role</td><td>Year</td></tr></thead><tbody>";

			//fill in rest of the table rows with tuples returned
    		while($row = $result->fetch_row())
    		{
    			echo "<tr>";
    			$mid = $row[0];
    			echo "<td><a href='movieinfo.php?id_m=$mid'>$row[1]</a></td>";
    			for($i = 2; $i < $result->field_count; $i++)
    			{
    				$val = $row[$i];
    				if ($val == NULL)
    					$val = "N/A";
    				echo "<td>$val</td>";
    			}
    			echo "</tr>";
    		}
			echo "</tbody></table></div>";

			$result->free();

    		$db->close();
		}
	?> 

	</body>
</html>