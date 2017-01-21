/*
 * Copyright (C) 2008 by The Regents of the University of California
 * Redistribution of this file is permitted under the terms of the GNU
 * Public License (GPL).
 *
 * @author Junghoo "John" Cho <cho AT cs.ucla.edu>
 * @date 3/24/2008
 */
 
#include "BTreeIndex.h"
#include "BTreeNode.h"

using namespace std;

/*
 * BTreeIndex constructor
 */
BTreeIndex::BTreeIndex()
{
    rootPid = -1;
	treeHeight = 0;
}

/*
 * Open the index file in read or write mode.
 * Under 'w' mode, the index file should be created if it does not exist.
 * @param indexname[IN] the name of the index file
 * @param mode[IN] 'r' for read, 'w' for write
 * @return error code. 0 if no error
 */
RC BTreeIndex::open(const string& indexname, char mode)
{
	RC rc;

	if ((rc = pf.open(indexname, mode)) != 0) //automatically creates file if not exist
		return rc;

	if (pf.endPid() == 0)
		return 0; //new file, no need to read anything

	//BTreeIndex class is destructed each time, 
	//reload rootPid and treeHeight values from disk
	char buffer[PageFile::PAGE_SIZE]; //similar to reading in node, see BTreeNode
	if ((rc = pf.read(0, buffer)) != 0) //read 0th page, metainfo
		return rc;
	treeHeight = *((int*)buffer);
	rootPid = *((PageId*)(buffer + sizeof(int)));
	//should i just include treeHeight in root? or does that mess with branching factor...
	
    return 0;
}

/*
 * Close the index file.
 * @return error code. 0 if no error
 */
RC BTreeIndex::close()
{
	RC rc;
	//BTreeIndex class is destructed each time, 
	//save rootPid and treeHeight values to disk
	
	//write rootPid and treeHeight back to disk
	char buffer[PageFile::PAGE_SIZE];
	int* height = (int*)buffer;
	*height = treeHeight;
	PageId* pid = (PageId*)(buffer + sizeof(int));
	*pid = rootPid;

	if ((rc = pf.write(0, buffer)) != 0)
		return rc;
	
	return pf.close();
}

/*
 * Insert (key, RecordId) pair to the index.
 * @param key[IN] the key for the value inserted into the index
 * @param rid[IN] the RecordId for the record being inserted into the index
 * @return error code. 0 if no error
 */
RC BTreeIndex::insert(int key, const RecordId& rid)
{
	RC rc;
	
	if (treeHeight == 0)
	{
		//at this point, pf should be empty, reserve pg0 for metainfo
		rootPid = 1;
		
		BTLeafNode newleaf;
		newleaf.insert(key, rid);
		newleaf.write(rootPid, pf);
		
		treeHeight++;
		return 0;
	}

	IndexCursor c;
	c.pid = rootPid;
	c.eid = -1;
	int splitKey = -1;
	PageId splitPid = -1;

	if (insert(key, rid, c, 1, splitKey, splitPid) == RC_NODE_FULL)
	{
		BTNonLeafNode newroot;
		newroot.initializeRoot(rootPid, splitKey, splitPid);
		rootPid = pf.endPid();
		newroot.write(rootPid, pf);
		treeHeight++;
	}

	return 0;
}

//recursive version of insert
RC BTreeIndex::insert(int key, const RecordId& rid, IndexCursor& cursor, int height, int& splitKey, PageId& splitPid)
{
	RC rc;

	//first locate correct leaf node
	if (height != treeHeight) //keep searching
	{
		BTNonLeafNode nonleaf;
		if ((rc = nonleaf.read(cursor.pid, pf)) != 0)
			return rc; //read contents of current node from pf of the tree
		
		IndexCursor nextc;
		if ((rc = nonleaf.locateChildPtr(key, nextc.pid)) != 0)
			return rc; //find the correct child ptr
		
		if ((rc = insert(key, rid, nextc, height+1, splitKey, splitPid)) == RC_NODE_FULL)
		{
			if (nonleaf.insert(splitKey, splitPid) == RC_NODE_FULL)
			{
				BTNonLeafNode sibling;
				int siblingKey;

				nonleaf.insertAndSplit(splitKey, splitPid, sibling, siblingKey);
				splitPid = pf.endPid();
				splitKey = siblingKey;
				sibling.write(splitPid, pf);
				nonleaf.write(cursor.pid, pf);
				return RC_NODE_FULL;
			}
			nonleaf.write(cursor.pid, pf);
			return 0;
		}
		return 0;
	}

	//else if reached treeHeight
	BTLeafNode leaf;
	leaf.read(cursor.pid, pf);
	if (leaf.insert(key, rid) == RC_NODE_FULL) //node full, need to split
	{
		BTLeafNode sibling;
		int siblingKey;

		leaf.insertAndSplit(key, rid, sibling, siblingKey); //updates splitKey automatically, ERROR?
		splitPid = pf.endPid(); //insert new sibling at end of pagefile
		splitKey = siblingKey;
		leaf.setNextNodePtr(splitPid);
		sibling.write(splitPid, pf);
		leaf.write(cursor.pid, pf);
		return RC_NODE_FULL;
	}
	leaf.write(cursor.pid, pf);
	return 0;
}

/**
 * Run the standard B+Tree key search algorithm and identify the
 * leaf node where searchKey may exist. If an index entry with
 * searchKey exists in the leaf node, set IndexCursor to its location
 * (i.e., IndexCursor.pid = PageId of the leaf node, and
 * IndexCursor.eid = the searchKey index entry number.) and return 0.
 * If not, set IndexCursor.pid = PageId of the leaf node and
 * IndexCursor.eid = the index entry immediately after the largest
 * index key that is smaller than searchKey, and return the error
 * code RC_NO_SUCH_RECORD.
 * Using the returned "IndexCursor", you will have to call readForward()
 * to retrieve the actual (key, rid) pair from the index.
 * @param key[IN] the key to find
 * @param cursor[OUT] the cursor pointing to the index entry with
 *                    searchKey or immediately behind the largest key
 *                    smaller than searchKey.
 * @return 0 if searchKey is found. Othewise an error code
 */
RC BTreeIndex::locate(int searchKey, IndexCursor& cursor)
{
	RC rc;
	PageId p = rootPid;
	BTNonLeafNode nonleaf;

	if (treeHeight < 1)
		return RC_NO_SUCH_RECORD;

	for(int h = 1; h < treeHeight; h++) //nonleaf until leaf level
	{
		if ((rc = nonleaf.read(p, pf)) != 0)
			return rc; //read contents of current node from pf of the tree
		if ((rc = nonleaf.locateChildPtr(searchKey, p)) != 0)
			return rc; //find the correct child and update p
	}

	BTLeafNode leaf;

	leaf.read(p, pf);

	if ((rc = leaf.locate(searchKey, cursor.eid)) != 0)
	{
		cursor.pid = p;
		return rc; //updates eid if found
	}

	cursor.pid = p;

    return 0;
}

/*
 * Read the (key, rid) pair at the location specified by the index cursor,
 * and move foward the cursor to the next entry.
 * @param cursor[IN/OUT] the cursor pointing to an leaf-node index entry in the b+tree
 * @param key[OUT] the key stored at the index cursor location.
 * @param rid[OUT] the RecordId stored at the index cursor location.
 * @return error code. 0 if no error
 */
RC BTreeIndex::readForward(IndexCursor& cursor, int& key, RecordId& rid)
{
	RC rc;
	//reading key, rid pair so must be leaf
	BTLeafNode leaf;
	//PageId p = cursor.pid;
	//int eid = cursor.eid;
	
	if (cursor.pid <= 0 || cursor.pid >= pf.endPid())
		return RC_INVALID_CURSOR;
	
	if ((rc = leaf.read(cursor.pid, pf)) != 0)
		return rc;

	if ((rc = leaf.readEntry(cursor.eid, key, rid)) != 0)
		return rc;
	
	//update cursor
	cursor.eid++; //entry id move up

	if (cursor.eid >= leaf.getKeyCount())
	{
		cursor.pid = leaf.getNextNodePtr();
		cursor.eid = 0;
	}

    return 0;
}