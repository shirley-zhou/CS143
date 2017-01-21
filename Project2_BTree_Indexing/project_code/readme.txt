Optimizations:
1. I used upper and lower bounds to handle checking the conditions.
This optimizes performance so that if (upperLim < lowLim) ever happens even before attempting to access anything,
the program will realize there is a conflict and return no results. 
The bounds are only updated if something narrower is found in the conditions. This ensures that the equality condition still takes precedence, per the spec.
For ex: SELECT * FROM large WHERE key < 100 AND key = 100
=> returns as soon as the program realizes there is a conflict.
Also: SELECT * FROM large WHERE key > 100 AND key < 200
=> will use the B+ Tree index and start reading from the node corresponding to 100,
then rightward, but will stop as soon as it reads a value >= 200

email: sjzhou@ucla.edu, no partner
