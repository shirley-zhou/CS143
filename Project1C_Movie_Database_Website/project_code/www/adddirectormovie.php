<!DOCTYPE html>
<html>
	<head>
        <title>Add Director to Movie: </title>
		<!-- <link rel="import" href="header.html"> here or use php?? FIX -->
	</head>

	<body>

	<?php
		include 'header.php';

        $db = new mysqli('localhost', 'cs143', '', 'CS143'); //username cs143, password empty, database CS143
        if($db->connect_errno > 0)
            die('Unable to connect to database [' . $db->connect_error . ']');
	?>

		<div class="container">
            <form class="form-group col-md-6 col-lg-6" method="POST">
                <div class="input-group">
                    <label>Movie: </label>
                    <select class="form-control" name="movie">
                        <?php
                        if (isset($_GET["id_m"])) //came here from movie page
                        {
                            $id_m = $_GET["id_m"];
                            $title = $_GET["title"];
                            echo "<option value='$id_m'>$title</option>";
                        }
                        $query = "SELECT id, title, year FROM Movie ORDER BY title ASC;";
                        if (!($result = $db->query($query)))
                        {
                            $err = $db->error;
                            echo "Error: $err<br />";
                            exit(1);
                        }

                        while($row = $result->fetch_row())
                        {
                            for($i = 0; $i < $result->field_count; $i++)
                            {
                                if ($row[$i] == NULL)
                                    $row[$i] = "N/A";
                            }
                            $movie = $row[1] . " (" . $row[2] . ") ";
                            echo "<option value='$row[0]'>$movie</option>"; //$row[0] is id
                        }
                        ?>
                    </select>
                </div></br>

                <div class="input-group">
                    <label>Director: </label>
                    <select class="form-control" name="director">
                        <?php
                        /*if (isset($_GET["id_a"])) //came here from actor page
                        {
                            $id_a = $_GET["id_a"];
                            $name = $_GET["name"];
                            echo "<option value='$id_a'>$name</option>";
                        }*/
                        $query = "SELECT id, CONCAT_WS(' ', first, last) AS D, dob FROM Director ORDER BY D ASC;";
                        if (!($result = $db->query($query)))
                        {
                            $err = $db->error;
                            echo "Error: $err<br />";
                            exit(1);
                        }

                        while($row = $result->fetch_row())
                        {
                            /*for($i = 0; $i < $result->field_count; $i++)
                            {
                                if ($row[$i] == NULL)
                                    $row[$i] = "N/A";
                            }*/
                            $director = $row[1] . " (" .  $row[2] . ") ";
                            echo "<option value='$row[0]'>$director</option>"; //$row[0] is id
                        }
                        ?>
                    </select>
                </div></br>

                <div class="input-group-btn">
                    <button class="btn btn-default" type="submit" name="submit">Submit</button>
                </div>
            </form>

	<?php
		//ini_set('display_errors', 'On'); //FIX
		//error_reporting(E_ALL | E_STRICT); //FIX

        if (isset($_POST["submit"]))
        {
            $mid = $db->real_escape_string($_POST['movie']);
            $did = $db->real_escape_string($_POST['director']);

            $insert = "INSERT INTO MovieDirector VALUES ('$mid', '$did')";
            if (!($result = $db->query($insert)))
            {
                $err = $db->error;
                echo "Error: $err<br />";
                exit(1);
            }
            else
            {
                echo "<h3>Success, director/movie relation added</h3>";
            }
        }

    	$db->close();
	?>
    </div> <!-- closes main container div -->
	</body>
</html>
