<!DOCTYPE html>
<html lang="en">
	<head>
	    <meta charset="utf-8">
	    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	    <meta name="viewport" content="width=device-width, initial-scale=1">
	    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

		<title>CS143 Movie Database</title>
		<link rel="stylesheet" href="css/styles.css">
		<link rel="stylesheet" href="css/bootstrap.min.css">
	
		<div class="page-header">
	  		<h1 class="text-center">IMDB LITE</h1>
		</div>
	
	<nav class="navbar navbar-default" role="navigation">
		<div class="container-fluid">
			<div class="navbar-header">
				<a class="navbar-brand" href="index.php">IMDB Lite</a>
			</div>
		    <ul class="nav navbar-nav">
				<li ><a href="index.php">Home</a></li>
				<li><a href="movieinfo.php">Movies</a></li>
				<li><a href="actorinfo.php">Actors</a></li>
				<li><a href="addcontent.php">Add Content</a></li>
		    </ul>
		    <div class="col-lg-3 col-sm-3 col-md-3 pull-right">
        		<form class="navbar-form" method="GET" role="search" action="search.php">
        			<input type="hidden" name="searchtype" value="both">
        			<div class="input-group">
            			<input type="text" name="searchterm" class="form-control" placeholder="Search" id="srch-term">
            			<div class="input-group-btn">
                			<button class="btn btn-default" type="submit"><i class="glyphicon glyphicon-search"></i></button>
            			</div>
        			</div>
        			<!--
        			<label class="radio-inline">
      					<input type="radio" name="optradio" checked>Movie
    				</label>
    				<label class="radio-inline">
      					<input type="radio" name="optradio">Actor
    				</label>
    				-->
        		</form>
        	</div>
		</div>
	</nav>

	</head>

	<body>

	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>

	</body>
</html>