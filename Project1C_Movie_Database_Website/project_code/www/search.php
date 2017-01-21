<!DOCTYPE html>
<html>
	<head>
		<title>Search</title>
		<!-- <link rel="import" href="header.html"> <!-- here or use php?? FIX -->
	</head>

	<body>

	<?php
		include 'header.php';
	?>

		<div class="container">
			<h2>Search Results</h2>

	<?php
		//ini_set('display_errors', 'On'); //FIX
		//error_reporting(E_ALL | E_STRICT); //FIX

		$db = new mysqli('localhost', 'cs143', '', 'CS143'); //username cs143, password empty, database CS143
	    if($db->connect_errno > 0)
    		die('Unable to connect to database [' . $db->connect_error . ']');
    //FIX: if no results, output link to add content page
    
		//search for words on particular attribute of a table
    	function search($table, $attr, $words)
    	{
    		GLOBAL $db;

            if ($table == "Movie")
            {
                $head1 = "Title";
                $head2 = "Year";
                $ret = "title, year";
                $info = "movieinfo.php";
                $remoteid = "id_m";
            }
            else if ($table == "Actor")
            {
                $head1 = "Name";
                $head2 = "Date of Birth";
                $ret = "CONCAT_WS(' ', first, last), dob";
                $info = "actorinfo.php";
                $remoteid = "id_a";
            }

    		$query = "SELECT id, $ret FROM $table WHERE $attr LIKE '%$words[0]%'";

	    	for($i = 1; $i < count($words); $i++)
	    		$query .= " AND $attr LIKE '%$words[$i]%' ";
            $query .= "ORDER BY $ret ASC;";

	    	if (!($result = $db->query($query)))
    		{
    			$err = $db->error;
    			echo "Error: $err<br />";
    			exit(1);
    		}

    		$name = $table . "s";
    		echo "<h3>$name Found: </h3>";

    		echo "<div class='table-responsive'><table class='table table-bordered table-condensed table-hover'>";

    		//fill first row of table with field names
    		echo "<thead><tr><td>$head1</td><td>$head2</td></tr></thead><tbody>";
    		/*
    		while($field = $result->fetch_field())
    			echo "<td><b>$field->name</b></td>"; */

			//fill in rest of the table rows with tuples returned
			while($row = $result->fetch_row())
    		{
                $id = $row[0];

    			echo "<tr>";
    			for($i = 1; $i < $result->field_count; $i++)
    			{
    				if ($row[$i] == NULL)
    					$row[$i] = "N/A";
    			}
                echo "<td><a href='$info?$remoteid=$id'>$row[1]</a></td>";
                echo "<td>$row[2]</td>";
    			echo "</tr>";
    		}
			echo "</tbody></table></div>";

    		$result->free();
    	}

		if ($_GET["searchterm"])
	  	{
	    	$search = $_GET["searchterm"];
	    	$words = explode(" ", $search);

	    	switch($_GET["searchtype"]) //php has switch on string?? what??
	    	{
	    		case "actor":
	    			search("Actor", "CONCAT_WS(' ', first, last)", $words);
	    			break;
	    		case "movie":
	    			search("Movie", "title", $words);
	    			break;
	    		case "both":
	    			search("Actor", "CONCAT_WS(' ', first, last)", $words);
	    			search("Movie", "title", $words);
	    	}
    	}
    	$db->close();
	?>
		</div> <!-- closes main container div -->
	</body>
</html>
