<!DOCTYPE html>
<html>
	<head>
        <title>Add Actor to Movie: </title>
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
                    <label>Actor: </label>
                    <select class="form-control" name="actor">
                        <?php
                        if (isset($_GET["id_a"])) //came here from actor page
                        {
                            $id_a = $_GET["id_a"];
                            $name = $_GET["name"];
                            echo "<option value='$id_a'>$name</option>";
                        }
                        $query = "SELECT id, CONCAT_WS(' ', first, last) AS A, dob FROM Actor ORDER BY A ASC;";
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
                            $actor = $row[1] . " (" .  $row[2] . ") ";
                            echo "<option value='$row[0]'>$actor</option>"; //$row[0] is id
                        }
                        ?>
                    </select>
                </div></br>

                <div class="input-group">
                    <label>Role: </label>
                        <input type="text" name="role" class="form-control" placeholder="Character name">
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
            $aid = $db->real_escape_string($_POST['actor']);
            $role = $db->real_escape_string($_POST['role']);

            $insert = "INSERT INTO MovieActor VALUES ('$mid', '$aid', '$role')";
            if (!($result = $db->query($insert)))
            {
                $err = $db->error;
                echo "Error: $err<br />";
                exit(1);
            }
            else
            {
                echo "<h3>Success, actor/movie relation added</h3>";
            }
        }

    	$db->close();
	?>
    </div> <!-- closes main container div -->
	</body>
</html>
