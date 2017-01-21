Answers to the referential integrity section:

Every movie has a unique ID: Primary Key(id)
Movie year must be a reasonable number, ie post 1800s: CHECK(Year >= 1800 AND rating >= 0)
Rating must be a positive number: see above
Every movie must have a title: title VARCHAR(100) NOT NULL
Every actor must have a date of birth: dob DATE NOT NULL
Every actor must have a first and last name: NOT NULL
Every person must have a unique ID: PRIMARY KEY(id)
Every director must have a first and last name: NOT NULL
Every director must have a date of birth: NOT NULL
Every mid in the MovieGenre table must correspond to a movie id in the Movie table: FOREIGN KEY (mid) references Movie(id) ENGINE=INNODB
Every movie must have a director: did INT NOT NULL
Every movie id must exist in the Movie table, and every director id must exist in the Director table: FOREIGN KEY (mid) references Movie(id) ENGINE=INNODB, FOREIGN KEY (did) references Director(id) ENGINE=INNODB
Every movie id must exist in the Movie table, and every actor id must exist in the Actor table: FOREIGN KEY (mid) references Movie(id) ENGINE=INNODB, FOREIGN KEY (aid) references Actor(id) ENGINE=INNODB
Every movie must have an actor: aid INT NOT NULL
Every review must have a movie ID from the Movie table: FOREIGN KEY (mid) references Movie(id) ENGINE=INNODB
Every rating must be nonnegative: CHECK(rating >= 0)

IN SUMMARY:

Primary Key Constraints:
1. In the movie table, PRIMARY KEY(id)
2. In the Actor table, PRIMARY KEY(id)
3. In the Director table, PRIMARY KEY(id)

Referential Integrity Constraints:
1. In the MovieGenre table, FOREIGN KEY (mid) references Movie(id) ENGINE=INNODB
2. In the MovieDirector table, FOREIGN KEY (mid) references Movie(id) ENGINE=INNODB
3. In the MovieDirector table, FOREIGN KEY (did) references Director(id) ENGINE=INNODB
4. In the MovieActor table, FOREIGN KEY (mid) references Movie(id) ENGINE=INNODB,
5. In the MovieActor table, FOREIGN KEY (aid) references Actor(id) ENGINE=INNODB
6. In the Review table, FOREIGN KEY (mid) references Movie(id) ENGINE=INNODB

Check Constraints:
1. In the Movie table, CHECK(Year >= 1800)
2. In the Movie table, CHECK(rating >= 0)
3. In the Review table, CHECK(rating >= 0)