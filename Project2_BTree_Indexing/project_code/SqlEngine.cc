/**
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */

#include <cstdio>
#include <cstring>
#include <cstdlib>
#include <iostream>
#include <fstream>
#include <climits>
#include "Bruinbase.h"
#include "SqlEngine.h"
#include "BTreeIndex.h"


using namespace std;

// external functions and variables for load file and sql command parsing 
extern FILE* sqlin;
int sqlparse(void);


RC SqlEngine::run(FILE* commandline)
{
  fprintf(stdout, "Bruinbase> ");

  // set the command line input and start parsing user input
  sqlin = commandline;
  sqlparse();  // sqlparse() is defined in SqlParser.tab.c generated from
               // SqlParser.y by bison (bison is GNU equivalent of yacc)

  return 0;
}

RC SqlEngine::select(int attr, const string& table, const vector<SelCond>& cond)
{
  RecordFile rf;   // RecordFile containing the table
  RecordId   rid;  // record cursor for table scanning

  RC     rc;
  int    key;     
  string value;
  int    count;
  int    diff;

  // open the table file
  if ((rc = rf.open(table + ".tbl", 'r')) < 0) {
    fprintf(stderr, "Error: table %s does not exist\n", table.c_str());
    return rc;
  }
  
  BTreeIndex bti;

  //if index exists, then try to use it
  if (bti.open(table + ".idx", 'r') == 0)
  {
	  //const SelCond* bestCond = NULL; //which condition would be best for a B+ Tree query
	  int lowLim = 0; //lower lim, update by cond
	  int upperLim = INT_MAX; //upper lim, update by cond
	  //int eq = -1; //in case of strict equality
	  //int ne = -1; //inequality, just for checking conflicts
	  bool loadVals = false; //check whether any conditions look at value, if not, no need to load
	  if (attr == 2 || attr == 3) //query value or *, must load tuple
		  loadVals = true;
	  for (int i = 0; i < cond.size(); i++)
	  {
		  if (cond[i].attr ==2)
		  {
			  loadVals = true; //need to actually load tuples to get values
			  continue; //index search only possible on key
		  }

		  int val = atoi(cond[i].value);
		  if (cond[i].comp == SelCond::EQ)
		  {
			  if (val > upperLim || val < lowLim) //check for conflict
			  {
				  bti.close();
				  goto exit_select;
			  }
			  //bestCond = &cond[i];
			  upperLim = val;
			  lowLim = val;
		  }
		  
		  switch (cond[i].comp)
		  {
			  case SelCond::LT:
				  if (val - 1 < upperLim)
				  {
					  upperLim = val - 1;
				  }
				  break;
			  case SelCond::GT:
				  if (val + 1> lowLim)
				  {
					  lowLim = val + 1;
				  }
				  break;
			  case SelCond::LE:
				  if (val < upperLim)
				  {
					  upperLim = val;
				  }
				  break;
			  case SelCond::GE:
				  if (val > lowLim)
				  {
					  lowLim = val;
				  }
				  break;
			  /*case SelCond::NE:
				  if (val > upperLim && val <) //conflict
				  {
					  bti.close();
					  goto exit_select;
				  }
				  break;*/
			}
						
			if (upperLim < lowLim) //after processing condition, check for conflicts
			{
				bti.close();
				goto exit_select;
			}
			/*if (eq != -1 && (eq > upperLim || eq < lowLim))
			{
				bti.close();
				goto exit_select;
			}*/
	  }
	  
	  //equality takes priority
	  /*if (eq != -1)
	  {
		  lowLim = eq;
		  upperLim = eq;
	  }*/
	  
	  //index helps if conds involve sorting OR query attr is count(*), else just skip this and scan table below
	  if (lowLim > 0 || upperLim < INT_MAX || attr == 4)
	  {
		  //search the index
		  IndexCursor c;
		  bti.locate(lowLim, c);

		  //if ((rc = bti.locate(lowLim, c)) == 0) //found leaf node where val MAY exist
		  //{
			  count = 0;
			  while ((bti.readForward(c, key, rid) == 0) && key <= upperLim) //return key, rid pair from leaf node
			  {
				  //only load tuple if SOME condition requires looking at value, else leaf nodes already know key
				  if (loadVals)
				  {
					  if ((rc = rf.read(rid, key, value)) < 0) {
						  fprintf(stderr, "Error: while reading a tuple from table %s\n", table.c_str());
						  bti.close();
						  goto exit_select;
					  }
				  }
					  
				  //this section of code follows the original non-index example below

				  //check rest of conditions
				  for (int i = 0; i < cond.size(); i++)
				  {
					  // compute the difference between the tuple value and the condition value
					  switch (cond[i].attr)
					  {
						case 1:
							diff = key - atoi(cond[i].value);
							break;
						case 2:
							diff = strcmp(value.c_str(), cond[i].value);
							break;
					  }

					  // skip the tuple if any condition is not met
					  switch (cond[i].comp)
					  {
						case SelCond::EQ:
							if (diff != 0) goto next_entry;
							break;
						case SelCond::NE:
							if (diff == 0) goto next_entry;
							break;
						case SelCond::GT:
							if (diff <= 0) goto next_entry;
							break;
						case SelCond::LT:
							if (diff >= 0) goto next_entry;
							break;
						case SelCond::GE:
							if (diff < 0) goto next_entry;
							break;
						case SelCond::LE:
							if (diff > 0) goto next_entry;
							break;
					  }
				  }
				  // the condition is met for the tuple.
				  // increase matching tuple counter
				  count++;
				  // print the tuple 
				  switch (attr)
				  {
					case 1:  // SELECT key
						fprintf(stdout, "%d\n", key);
						break;
					case 2:  // SELECT value
						fprintf(stdout, "%s\n", value.c_str());
						break;
					case 3:  // SELECT *
						fprintf(stdout, "%d '%s'\n", key, value.c_str());
						break;
				  }
				  
				  next_entry:
					continue; //skip tuple, go to next
			  }
			  // print matching tuple count if "select count(*)"
			  if (attr == 4)
				  fprintf(stdout, "%d\n", count);
			  
			  rc = 0;
			  // close the table file and return
			  bti.close();
			  goto exit_select;
		  //}
		  //else //key not found, don't print anything
			//  goto exit_select;
	  } //else no condition found for which index would be useful, proceed with traditional table scan
  }

  // scan the table file from the beginning
  rid.pid = rid.sid = 0;
  count = 0;
  while (rid < rf.endRid()) {
    // read the tuple
    if ((rc = rf.read(rid, key, value)) < 0) {
      fprintf(stderr, "Error: while reading a tuple from table %s\n", table.c_str());
      goto exit_select;
    }

    // check the conditions on the tuple
    for (unsigned i = 0; i < cond.size(); i++) {
      // compute the difference between the tuple value and the condition value
      switch (cond[i].attr) {
      case 1:
	diff = key - atoi(cond[i].value);
	break;
      case 2:
	diff = strcmp(value.c_str(), cond[i].value);
	break;
      }

      // skip the tuple if any condition is not met
      switch (cond[i].comp) {
      case SelCond::EQ:
	if (diff != 0) goto next_tuple;
	break;
      case SelCond::NE:
	if (diff == 0) goto next_tuple;
	break;
      case SelCond::GT:
	if (diff <= 0) goto next_tuple;
	break;
      case SelCond::LT:
	if (diff >= 0) goto next_tuple;
	break;
      case SelCond::GE:
	if (diff < 0) goto next_tuple;
	break;
      case SelCond::LE:
	if (diff > 0) goto next_tuple;
	break;
      }
    }

    // the condition is met for the tuple. 
    // increase matching tuple counter
    count++;

    // print the tuple 
    switch (attr) {
    case 1:  // SELECT key
      fprintf(stdout, "%d\n", key);
      break;
    case 2:  // SELECT value
      fprintf(stdout, "%s\n", value.c_str());
      break;
    case 3:  // SELECT *
      fprintf(stdout, "%d '%s'\n", key, value.c_str());
      break;
    }

    // move to the next tuple
    next_tuple:
    ++rid;
  }

  // print matching tuple count if "select count(*)"
  if (attr == 4) {
    fprintf(stdout, "%d\n", count);
  }
  rc = 0;

  // close the table file and return
  exit_select:
  rf.close();
  return rc;
}

RC SqlEngine::load(const string& table, const string& loadfile, bool index)
{
  /* your code here */
  ifstream inputfile;
  inputfile.open(loadfile.c_str()); //open() takes char *
  if (!inputfile.is_open())
    return RC_FILE_OPEN_FAILED;

  string filename = table + ".tbl";
  RecordFile rf;
  if (rf.open(filename, 'w') != 0)
    return RC_FILE_OPEN_FAILED;

  RecordId rid = rf.endRid(); //passed by ref into append(), changes each iter
  BTreeIndex bti;
  if (index)
  {
    if (bti.open(table + ".idx", 'w') != 0)
	{
      return RC_FILE_OPEN_FAILED;
	}
  }

  string line;
  while (getline(inputfile, line))
  {
    int key;
    string value;
    if (parseLoadLine(line, key, value) != 0)
      return RC_FILE_READ_FAILED;

    if (rf.append(key, value, rid) != 0)
      return RC_FILE_WRITE_FAILED;

    if (index)
    {
      if (bti.insert(key, rid) != 0)
        return RC_FILE_WRITE_FAILED;
    }
  }

  inputfile.close();
  rf.close();
  if (index)
    bti.close();
  return 0;
}

RC SqlEngine::parseLoadLine(const string& line, int& key, string& value)
{
    const char *s;
    char        c;
    string::size_type loc;
    
    // ignore beginning white spaces
    c = *(s = line.c_str());
    while (c == ' ' || c == '\t') { c = *++s; }

    // get the integer key value
    key = atoi(s);

    // look for comma
    s = strchr(s, ',');
    if (s == NULL) { return RC_INVALID_FILE_FORMAT; }

    // ignore white spaces
    do { c = *++s; } while (c == ' ' || c == '\t');
    
    // if there is nothing left, set the value to empty string
    if (c == 0) { 
        value.erase();
        return 0;
    }

    // is the value field delimited by ' or "?
    if (c == '\'' || c == '"') {
        s++;
    } else {
        c = '\n';
    }

    // get the value string
    value.assign(s);
    loc = value.find(c, 0);
    if (loc != string::npos) { value.erase(loc); }

    return 0;
}
