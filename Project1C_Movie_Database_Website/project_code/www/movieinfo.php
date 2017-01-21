<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Movie Info</title>
		<!--<link rel="import" href="header.php"> <!-- here or use php?? FIX -->
	</head>

	<body>
	<?php
		include 'header.php';
	?> 

		<div class="container">
			<h2>Get Movie Info</h2>
			<label for="search_input">Search:</label>
			<form class="form-group" method="GET" role= "search" id="usrform" action="search.php">
				<input type="hidden" name="searchtype" value="movie">
				<div class="input-group">
					<input type="text" name="searchterm" class="form-control" placeholder="Search for movie">
				<div class="input-group-btn">
					<button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
          		</div>
			</form>
		</div>

	<?php
		if ($_GET["id_m"])
		{
			$db = new mysqli('localhost', 'cs143', '', 'CS143'); //username cs143, password empty, database CS143
	    	if($db->connect_errno > 0)
    			die('Unable to connect to database [' . $db->connect_error . ']');

    		$id = $_GET["id_m"];
    		$query = "SELECT title, year, GROUP_CONCAT(genre SEPARATOR ', '), rating, company FROM Movie, MovieGenre WHERE id =$id AND mid=$id;";
    		if (!($result = $db->query($query)))
    		{
    			$err = $db->error;
    			echo "Error: $err<br />";
    			exit(1);
    		}

    		echo "<h3>Basic Info: </h3>";

    		echo "<div class='table-responsive'><table class='table table-bordered table-condensed table-hover'>";

    		//fill first row of table with field names
    		echo "<thead><tr><td>Title</td><td>Year</td><td>Genre</td><td>MPAA Rating</td><td>Company</td></tr></thead><tbody>";

			//fill in rest of the table rows with tuples returned
    		$row = $result->fetch_row();
    		$title = urlencode($row[0]);
    		$url_id = urlencode($id);

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

			//get director info
			$query = "SELECT CONCAT_WS(' ', first, last) AS D, dob FROM MovieDirector, Director WHERE mid =$id AND id=did ORDER BY D ASC";

    		if (!($result = $db->query($query)))
    		{
    			$err = $db->error;
    			echo "Error: $err<br />";
    			exit(1);
    		}

    		echo "<h3>Directors: </h3>";

    		echo"<a href='adddirectormovie.php?id_m=$url_id&title=$title'><h5>Add Director to Movie</h5></a>";

    		echo "<div class='table-responsive'><table class='table table-bordered table-condensed table-hover'>";

    		//fill first row of table with field names
    		echo "<thead><tr><td>Director</td><td>Date of Birth</td></tr></thead><tbody>";

			//fill in rest of the table rows with tuples returned
    		while($row = $result->fetch_row())
    		{
    			echo "<tr>";
    			for($i = 0; $i < $result->field_count; $i++)
    			{
    				$val = $row[$i];
    				if ($val == NULL)
    					$val = "N/A";
    				echo "<td>$val</td>";
    			}
    			echo "</tr>";
    		}
			echo "</tbody></table></div>";

			//get actor info
			$query = "SELECT aid, CONCAT_WS(' ', first, last) AS A, role FROM MovieActor, Actor WHERE mid =$id AND id=aid ORDER BY A;";

    		if (!($result = $db->query($query)))
    		{
    			$err = $db->error;
    			echo "Error: $err<br />";
    			exit(1);
    		}

    		echo "<h3>Actors and Roles: </h3>";

    		echo"<a href='addactormovie.php?id_m=$url_id&title=$title'><h5>Add Actor to Movie</h5></a>";

    		echo "<div class='table-responsive'><table class='table table-bordered table-condensed table-hover'>";

    		//fill first row of table with field names
    		echo "<thead><tr><td>Actor</td><td>Role</td></tr></thead><tbody>";

			//fill in rest of the table rows with tuples returned
    		while($row = $result->fetch_row())
    		{
    			echo "<tr>";
    			$aid = $row[0];
    			echo "<td><a href='actorinfo.php?id_a=$aid'>$row[1]</a></td>";
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

			//rating/score
			$query = "SELECT AVG(rating), COUNT(rating) FROM Review WHERE mid=$id GROUP BY mid;";

    		if (!($result = $db->query($query)))
    		{
    			$err = $db->error;
    			echo "Error: $err<br />";
    			exit(1);
    		}

			echo "<h3>Average Score: </h3>";

			$row = $result->fetch_row();
			if ($row[0] == NULL)
				$row[0] = "N/A";
			if ($row[1] == 0)
				$row[1] = "0";
			echo "<p>$row[0] (based on $row[1] reviews)</p>";

			//comments
			$query = "SELECT name, time, rating, comment FROM Review WHERE mid=$id ORDER BY time DESC;";

    		if (!($result = $db->query($query)))
    		{
    			$err = $db->error;
    			echo "Error: $err<br />";
    			exit(1);
    		}

			echo "<h3>Comments: </h3>";
			if($result->num_rows == 0)
				echo "<p>N/A</p>";

			while($row = $result->fetch_row())
    		{
    			if ($row[0] == NULL)
    				$row[0] = "Anonymous";
    			for($i = 1; $i < $result->field_count; $i++)
    			{
    				if ($row[$i] == NULL)
    					$row[$i] = "N/A";
    			}
    			
    			echo "<div class='panel panel-default'>";
    			echo "<div class='panel-heading'>";
    			echo "<strong>$row[0]</strong> <span class='text-muted'>commented $row[1]</span>";
    			echo "</div>";

    			echo "<div class='panel body'>";
    			echo "<p>$row[3]</p>";
    			echo "</div>";
    			echo "</div>";
    		}
    		echo "<h3>Add Comment: </h3>";
			echo "<form class='form-group col-md-6 col-lg-6' method='POST'>" .
        "<div class='input-group'>" .
            "<label>Name: </label>" .
                "<row>" .
                    "<input type='text' name='name' class='form-control' placeholder='Name' required>" .
                "</row>" .
        "</div>" .
		"<div class='input-group'>" .
            "<label>Rate: </label>" .
                "<select class='form-control' name='rating'>" .
                    "<option value='1'>1</option>" .
                    "<option value='2'>2</option>" .
                    "<option value='3'>3</option>" .
                    "<option value='4'>4</option>" .
                    "<option value='5'>5</option>" .
                "</select>" .
        "</div>" .
        "<div class='input-group'>" .
            "<label>Comment: </label>" .
				"<row>" .
					"<textarea name='comment' class='form-control' cols='60' rows='8' placeholder='Comment'></textarea><br/>" .
                "</row>" .
        "</div></br>" .
		"<div class='input-group-btn'>" .
            "<button class='btn btn-default' type='submit' name='submit'>Submit</button>" .
        "</div>" .
    "</form>";
    		if (isset($_POST["submit"]))
	    	{
		        $name = $db->real_escape_string($_POST['name']);
		        $rating = $db->real_escape_string($_POST['rating']);
		        $comment = $db->real_escape_string($_POST['comment']);

		        $insert = "INSERT INTO Review VALUES ('$name', NOW(), '$id', '$rating', '$comment')";

		        if (!($result = $db->query($insert)))
		        {
		            $err = $db->error;
		            echo "Error: $err<br />";
		            exit(1);
		        }
		        echo "<h3>Success, comment added</h3>";
	    		$db->close();
	    	}
	    $db->close();
		}
	?> 

    <?php
    	
    ?>
	</body>
</html>