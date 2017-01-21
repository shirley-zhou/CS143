-- violate primary key constraint in Movie table
-- ID 272 is already taken
INSERT INTO Movie VALUES (272, "blah", 2012, 5, "sldjf");
-- Error: Duplicate entry '272' for key 'PRIMARY'

-- violates primary key constraint in Actor table
-- ID 1 is already taken
INSERT INTO Actor VALUES (1, "blah", "blah", "female", 2001-01-01, 2001-01-01);
-- Error: Duplicate entry '1' for key 'PRIMARY'

--violates primary key constraint in Director table
-- ID 37146 is already taken
INSERT INTO Director VALUES (37146, "blah", "blah", 2001-01-01, 2001-01-01);
-- Error: Duplicate entry '37146' for key 'PRIMARY'

-- violate referential integrity in MovieGenre table
-- mid -1 is not an actual id in Movie table
INSERT INTO MovieGenre VALUES (-1, "comedy");
-- Error: Cannot add or update a child row: a foreign key constraint fails (`CS143`.`MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))

-- violate referential integrity in MovieDirector table
-- mid -1 is not an actual id in Movie table, but 37146 is an actual director
INSERT INTO MovieDirector VALUES (-1, 37146);
-- Error: Cannot add or update a child row: a foreign key constraint fails (`CS143`.`MovieDirector`, CONSTRAINT `MovieDirector_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))

-- violate referential integrity in MovieDirector table
-- did -1 is not an actual director in Director table, but 272 is an actual movie
INSERT INTO MovieDirector VALUES (272, -1);
-- Error: Cannot add or update a child row: a foreign key constraint fails (`CS143`.`MovieDirector`, CONSTRAINT `MovieDirector_ibfk_2` FOREIGN KEY (`did`) REFERENCES `Director` (`id`))

-- violate referential integrity in MovieActor table
-- aid -1 is not an actual actor in Actor table, but 272 is an actual movie
INSERT INTO MovieActor VALUES (272, -1, "main character");
-- Error: Cannot add or update a child row: a foreign key constraint fails (`CS143`.`MovieActor`, CONSTRAINT `MovieActor_ibfk_2` FOREIGN KEY (`aid`) REFERENCES `Actor` (`id`))

-- violate referential integrity in MovieActor table
-- mid -1 is not an actual movie in Movie table, but 1 is an actual actor
INSERT INTO MovieActor VALUES (-1, 1, "main character");
-- Error: Cannot add or update a child row: a foreign key constraint fails (`CS143`.`MovieActor`, CONSTRAINT `MovieActor_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))

-- violate referential integrity in Review table
-- mid -1 is not an actual movie in Movie table
INSERT INTO Review VALUES ("name", '01-01-2001 00:00:00', -1, 4, "good movie");
-- Error: Cannot add or update a child row: a foreign key constraint fails (`CS143`.`Review`, CONSTRAINT `Review_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))

-- violate check constraint in Movie table
-- invalid year, before 1800
INSERT INTO Movie VALUES (100000, "blah", 1750, 5, "sldjf");
-- MySQL doesn't actually do this check

-- violate check constraint in Movie table
-- negative rating
INSERT INTO Movie VALUES (100001, "blah", 2012, -3, "sldjf");
-- MySQL doesn't actually do this check

-- violate check constraint in Review table
-- negative rating
INSERT INTO Review VALUES ("name", '01-01-2001 00:00:00', 272, -3, "bad movie");
-- MySQL doesn't actually do this check
