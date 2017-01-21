I implemented every required feature of the project.

Input pages:
	I have a general Add Content page in the search bar, which contains forms and links to add people (actor or director), movies, and relations. A link to add an actor/movie relation also appears on each info page for specific actors and movies. When you follow that link on the info page for a movie for example, you'll notice the form input for movie will already be filled out with the movie that you just came from. This makes it more convenient for users who want to add info to a movie page that they were just on.

	Displaying and adding comments is also on individual movie info pages. You can add a comment at the bottom of the page, refresh the page, and your comment will then show up at the top, since comments are displayed with newest first.

Browsing pages:
	Actor and movie information can be accessed via their links in the search results. This will take you to a page with detailed info, and also an additional (extra) search bar at the top in case you want to search again.

Search page:
	There is a header included in every page, with a general search bar which will search BOTH actors and movies. For a more specific search, you can also search from the browsing pages for Actors and Movies, which will only do a search on the category you are browsing.
	For multi word searches, the search feature correctly implements the "AND" relation, where it will look at either titles for movies or concatenated first and last name for actors, returning results which contain all words you searched. This is implemented via LIKE '%$w0%', appending AND $attr LIKE '%$w1%' for each word.
	All results are returned in ascending (alphabetical) order by either movie title or actor name concatenated.