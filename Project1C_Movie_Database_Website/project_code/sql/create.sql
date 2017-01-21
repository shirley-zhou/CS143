CREATE TABLE Movie (
	id INT NOT NULL,
	title VARCHAR(100) NOT NULL,
	year INT,
	rating VARCHAR(10),
	company VARCHAR(50),
	PRIMARY KEY(id), -- id must be unique, nonnull
	CHECK(year >= 1800 AND rating >= 0) -- year must be after 1800 bcs no movies before, rating must be nonnegative
) ENGINE = INNODB;

CREATE TABLE Actor (
	id INT NOT NULL,
	last VARCHAR(20) NOT NULL,
	first VARCHAR(20) NOT NULL,
	sex VARCHAR(6),
	dob DATE NOT NULL,
	dod DATE,
	PRIMARY KEY(id) -- id must be unique nonnull
) ENGINE = INNODB;

CREATE TABLE Director (
	id INT NOT NULL,
	last VARCHAR(20) NOT NULL,
	first VARCHAR(20) NOT NULL,
	dob DATE NOT NULL,
	dod DATE,
	PRIMARY KEY(id) -- id must be unique nonnull
) ENGINE = INNODB;

CREATE TABLE MovieGenre (
	mid INT NOT NULL,
	genre VARCHAR(20),
	FOREIGN KEY (mid) references Movie(id) -- movie id here must reference some actual movie in Movie table
) ENGINE = INNODB;

CREATE TABLE MovieDirector (
	mid INT NOT NULL,
	did INT NOT NULL,
	FOREIGN KEY (mid) references Movie(id), -- movie id here must reference some actual movie in Movie table
	FOREIGN KEY (did) references Director(id) -- director id here must reference some actual director in Director table
) ENGINE = INNODB;

CREATE TABLE MovieActor (
	mid INT NOT NULL,
	aid INT NOT NULL,
	role VARCHAR(50),
	FOREIGN KEY (mid) references Movie(id), -- movie id here must reference some actual movie in Movie table
	FOREIGN KEY (aid) references Actor(id) -- actor id here must reference some actual actor in Actor table
) ENGINE = INNODB;

CREATE TABLE Review (
	name VARCHAR(20),
	time TIMESTAMP,
	mid INT NOT NULL,
	rating INT,
	comment VARCHAR(500),
	FOREIGN KEY (mid) references Movie(id), -- movie id here must reference some actual movie in Movie table
	CHECK(rating >= 0) -- rating must be nonnegative
) ENGINE = INNODB;

CREATE TABLE MaxPersonID (
	id INT NOT NULL
) ENGINE = INNODB;

CREATE TABLE MaxMovieID (
	id INT NOT NULL
) ENGINE = INNODB;
