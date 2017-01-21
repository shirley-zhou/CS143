<!DOCTYPE html>
<html>
	<head>
        <title>Add Content</title>
		<!-- <link rel="import" href="header.html"> here or use php?? FIX -->
	</head>

	<body>

	<?php
		include 'header.php';
	?>

		<div class="container">
            <form class="form-group col-md-6 col-lg-6" method="POST">
                <h2>Add Person</h2>
                <div class="input-group">
                    <label>Name: </label>
                    <row>
                        <input type="text" name="first" class="form-control" placeholder="First Name" required>
                        <input type="text" name="last" class="form-control" placeholder="Last Name" required>
                    </row>
                </div></br>

                <div class="input-group">
                    <label>Type: </label></br>
                    <label class="radio-inline">
                        <input type="radio" name="type" value="Actor" checked>Actor
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="type" value="Director">Director
                    </label>
                </div></br>

                <div class="input-group">
                    <label>Sex: </label></br>
                    <label class="radio-inline">
                        <input type="radio" name="sex" value="Female" checked>Female
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="sex" value="Male">Male
                    </label>
                </div></br>

                <div class="input-group">
                    <label>Dates: </label>
                    <row>
                        <input type="text" name="dob" class="form-control" placeholder="Birth: 1975-05-25" required>
                        <input type="text" name="dod" class="form-control" placeholder="Death: blank if NA">
                    </row>
                </div></br>

                <div class="input-group-btn">
                    <button class="btn btn-default" type="submit" name="addPerson">Submit</button>
                </div>
            </form>

            <form class="form-group col-md-6 col-lg-6" method="POST">
                <h2>Add Movie</h2>
                <div class="input-group">
                    <label>Title: </label>
                    <row>
                        <input type="text" name="title" class="form-control" placeholder="Title" required>
                    </row>
                </div></br>

                <div class="input-group">
                    <label>Year: </label>
                    <row>
                        <input type="text" name="year" class="form-control" placeholder="Year: 2005">
                    </row>
                </div></br>

                <div class="input-group">
                    <label>Company: </label>
                    <row>
                        <input type="text" name="company" class="form-control" placeholder="Company">
                    </row>
                </div></br>

                <div class="input-group">
                    <label>Rating: </label>
                    <select   class="form-control" name="rating">
                        <option value="G">G</option>
                        <option value="NC-17">NC-17</option>
                        <option value="PG">PG</option>
                        <option value="PG-13">PG-13</option>
                        <option value="R">R</option>
                    </select>
                </div></br>

                <div class="input-group">
                    <label >Genre:</label></br>
                    <input type="checkbox" name="genre[]" value="Action">Action</input>
                    <input type="checkbox" name="genre[]" value="Adult">Adult</input>
                    <input type="checkbox" name="genre[]" value="Adventure">Adventure</input>
                    <input type="checkbox" name="genre[]" value="Animation">Animation</input>
                    <input type="checkbox" name="genre[]" value="Comedy">Comedy</input>
                    <input type="checkbox" name="genre[]" value="Crime">Crime</input>
                    <input type="checkbox" name="genre[]" value="Documentary">Documentary</input>
                    <input type="checkbox" name="genre[]" value="Drama">Drama</input>
                    <input type="checkbox" name="genre[]" value="Family">Family</input>
                    <input type="checkbox" name="genre[]" value="Fantasy">Fantasy</input>
                    <input type="checkbox" name="genre[]" value="Horror">Horror</input>
                    <input type="checkbox" name="genre[]" value="Musical">Musical</input>
                    <input type="checkbox" name="genre[]" value="Mystery">Mystery</input>
                    <input type="checkbox" name="genre[]" value="Romance">Romance</input>
                    <input type="checkbox" name="genre[]" value="Sci-Fi">Sci-Fi</input>
                    <input type="checkbox" name="genre[]" value="Short">Short</input>
                    <input type="checkbox" name="genre[]" value="Thriller">Thriller</input>
                    <input type="checkbox" name="genre[]" value="War">War</input>
                    <input type="checkbox" name="genre[]" value="Western">Western</input>
                </div></br>

                <div class="input-group-btn">
                    <button class="btn btn-default" type="submit" name="addMovie">Submit</button>
                </div>
            </form>

            <div class="col-md-6 col-lg-6">
                <h2>Add Actor/Movie Relation</h2>
                <a href='addactormovie.php'><h5>Add</h5></a>
            </div>

            <div class="col-md-6 col-lg-6">
                <h2>Add Director/Movie Relation</h2>
                <a href='adddirectormovie.php'><h5>Add</h5></a>
            </div>

	<?php
		ini_set('display_errors', 'On'); //FIX
		error_reporting(E_ALL | E_STRICT); //FIX

        $db = new mysqli('localhost', 'cs143', '', 'CS143'); //username cs143, password empty, database CS143
        if($db->connect_errno > 0)
            die('Unable to connect to database [' . $db->connect_error . ']');

        //add person function
        function addPerson(){
            GLOBAL $db;
            $table = $_POST["type"];
            //first update id
            
            //get, don't immediately increment in case actual insert fails
            $r = $db->query("SELECT id FROM MaxPersonID;")->fetch_row(); //FIX error handle
            $id = $r[0]+1;

            //FIX: process string first? deal with null?
            //foreach ($_GET as $key => &$value)
            //{
            //    $value = $db->real_escape_string($value);
            //}

            $last = $db->real_escape_string($_POST['last']);
            $first = $db->real_escape_string($_POST['first']);
            $dob = $db->real_escape_string($_POST['dob']);
            $dod = $db->real_escape_string($_POST['dod']);

            if ($table=="Actor")
            {
                $sex = $db->real_escape_string($_POST['sex']);
                $insert = "INSERT INTO $table VALUES ('$id', '$last', '$first', '$sex', '$dob', '$dod')";
            }
            else
                $insert = "INSERT INTO $table VALUES ('$id', '$last', '$first', '$dob', '$dod')";

            if (!($result = $db->query($insert)))
            {
                $err = $db->error;
                echo "Error: $err<br />";
                exit(1);
            }
            else
            {
                echo "<h3>Success, person added</h3>";
                //update max id
                $db->query("UPDATE MaxPersonID SET id = id + 1");
            }
        }

        //add movie function
        function addMovie(){
            GLOBAL $db;
            
            //get, don't immediately increment in case actual insert fails
            $r = $db->query("SELECT id FROM MaxMovieID;")->fetch_row(); //FIX error handle
            $id = $r[0]+1;

            //FIX: process string first?

            $title = $db->real_escape_string($_POST['title']);
            $year = $db->real_escape_string($_POST['year']);
            $rating = $db->real_escape_string($_POST['rating']);
            $company = $db->real_escape_string($_POST['company']);
            $genres = $_POST['genre'];

            $insert = "INSERT INTO Movie VALUES ('$id', '$title', '$year', '$rating', '$company')";

            if (!($result = $db->query($insert)))
            {
                $err = $db->error;
                echo "Error: $err<br />";
                exit(1);
            }
            
            foreach($genres as $genre)
            {
                $insertgenre = "INSERT INTO MovieGenre VALUES ('$id', '$genre')";
                if (!($result = $db->query($insertgenre)))
                {
                    $err = $db->error;
                    echo "Error: $err<br />";
                    exit(1);
                }
            }
            
            echo "<h3>Success, movie added</h3>";
            //update max id
            $db->query("UPDATE MaxMovieID SET id = id + 1");
        }

        if (isset($_POST["addPerson"]))
            addPerson();
        else if (isset($_POST["addMovie"]))
            addMovie();

    	$db->close();
	?>
    </div> <!-- closes main container div -->
	</body>
</html>
