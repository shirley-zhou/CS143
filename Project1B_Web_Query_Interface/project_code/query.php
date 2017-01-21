<html>
	<head><title>CS143 Project 1B</title></head>
	<body>
	
	<h1>CS143 Project 1B</h1>

	Type an SQL query in the following box: <p>
	Example: <tt>SELECT * FROM Actor WHERE id=10;</tt><br />

	<p>
		<form method="GET">
			<textarea name="query" cols="60" rows="8"></textarea><br />
			<input type="submit" value="Submit" />
		</form>
	</p>

	<?php
		//ini_set('display_errors', 'On');
		//error_reporting(E_ALL | E_STRICT);

		$db = new mysqli('localhost', 'cs143', '', 'CS143'); //username cs143, password empty, database CS143
	    if($db->connect_errno > 0)
    		die('Unable to connect to database [' . $db->connect_error . ']');

		if ($_GET["query"])
	  	{
	    	$query = $_GET["query"];

	    	//NOTE: at this stage, assume all user input queries are valid, no checking, just execute
	    	//in practice, dangerous
    		if (!($result = $db->query($query)))
    		{
    			$err = $db->error;
    			echo "Error: $err<br />";
    			exit(1);
    		}

    		echo "<h3>Results from MySQL:</h3>";

    		echo "<table border=1 cellspacing=1 cellpadding=2>";

    		//fill first row of table with field names
    		echo "<tr align=center>";
    		while($field = $result->fetch_field())
    			echo "<td><b>$field->name</b></td>";
			echo "</tr>";

			//fill in rest of the table rows with tuples returned
			while($row = $result->fetch_row())
    		{
    			echo "<tr align=center>";
    			for($i = 0; $i < $result->field_count; $i++)
    			{
    				$val = $row[$i];
    				if ($val == NULL)
    					$val = "N/A";
    				echo "<td>$val</td>";
    			}
    			echo "</tr>";
    		}
			echo "</table>";

    		$result->free();
    	}
    	$db->close(); 
	?>
	</body>
</html>
