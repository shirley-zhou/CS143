-- names of all the actors in the movie 'Die Another Day'. Return <firstname> <lastname> separated by single space
SELECT CONCAT_WS(' ',Actor.first,Actor.last)
FROM MovieActor, Movie, Actor
WHERE Movie.title='Die Another Day' AND MovieActor.mid=Movie.id AND MovieActor.aid=Actor.id;

-- count all actor who acted in multiple movies
SELECT COUNT(*)
FROM (
SELECT aid
FROM MovieActor
GROUP BY aid
HAVING COUNT(DISTINCT mid) > 1) A;

-- get all directors who are also actors
SELECT CONCAT_WS(' ', Actor.first, Actor.last)
FROM Actor, Director
WHERE Actor.id=Director.id;