#include <string.h> //to support memmove
#include "BTreeNode.h"

using namespace std;

/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::read(PageId pid, const PageFile& pf)
{
	return pf.read(pid, buffer); //each node is stored on a page, read page into buffer
}
    
/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::write(PageId pid, PageFile& pf)
{ 
	return pf.write(pid, buffer); //each node is stored on a page, write buffer to page
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTLeafNode::getKeyCount()
{
	int* count = (int*)buffer;
	return *count; //store at head of node
}

void BTLeafNode::incrKeyCount(int amt)
{
	int* count = (int*) buffer;
	*count += amt;
}

/*
 * Insert a (key, rid) pair to the node.
 * @param key[IN] the key to insert
 * @param rid[IN] the RecordId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTLeafNode::insert(int key, const RecordId& rid)
{
	RC rc;

	struct pair{int key; RecordId rid;};
	pair* p;

	int maxkeys = (PageFile::PAGE_SIZE - sizeof(int) - sizeof(PageId))/sizeof(pair); //-pageid for sibling ptr, -int for keycount
	//check if node full
	if (getKeyCount() >= maxkeys)
		return RC_NODE_FULL;

	int eid;
	if ((rc = locate(key, eid)) == 0)
		return rc;
	
	p = (pair*)(buffer + sizeof(int) + eid*sizeof(pair));

	if (eid == maxkeys)
		return RC_NODE_FULL;

	memmove(p+1, p, (getKeyCount()-eid)*sizeof(pair)); //FIX, shift everything else over, NOTE: can't use memcpy bcs src dest overlap

	incrKeyCount();

	p->key = key;
	p->rid = rid;

	return 0;
}

/*
 * Insert the (key, rid) pair to the node
 * and split the node half and half with sibling.
 * The first key of the sibling node is returned in siblingKey.
 * @param key[IN] the key to insert.
 * @param rid[IN] the RecordId to insert.
 * @param sibling[IN] the sibling node to split with. This node MUST be EMPTY when this function is called.
 * @param siblingKey[OUT] the first key in the sibling node after split.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::insertAndSplit(int key, const RecordId& rid, 
                              BTLeafNode& sibling, int& siblingKey)
{
	RC rc;

	struct pair{int key; RecordId rid;};
	pair* p;
	pair* startbuffer = (pair*)(buffer + sizeof(int));

	int half = (getKeyCount() + 1)/2; //then split
	pair* cutoff = startbuffer + half;
	siblingKey = cutoff->key;
	
	int origKeyCount = getKeyCount();

	//is there a better way, to batch insert?
	for (int eid = half; eid < origKeyCount; eid++)
	{
		//int i = eid*sizeof(pair);
		p = &startbuffer[eid];
		sibling.insert(p->key, p->rid);
		incrKeyCount(-1);
	}
	
	if (key < siblingKey)
	{
		if ((rc = insert(key, rid)) != 0)
			return rc; //insert
	}
	else
	{
		if ((rc = sibling.insert(key, rid)) != 0)
			return rc; //insert
	}

	if ((rc = sibling.setNextNodePtr(getNextNodePtr())) != 0)
		return rc;

	return 0;
}

/**
 * If searchKey exists in the node, set eid to the index entry
 * with searchKey and return 0. If not, set eid to the index entry
 * immediately after the largest index key that is smaller than searchKey,
 * and return the error code RC_NO_SUCH_RECORD.
 * Remember that keys inside a B+tree node are always kept sorted.
 * @param searchKey[IN] the key to search for.
 * @param eid[OUT] the index entry number with searchKey or immediately
                   behind the largest key smaller than searchKey.
 * @return 0 if searchKey is found. Otherwise return an error code.
 */
RC BTLeafNode::locate(int searchKey, int& eid)
{
	struct pair{int key; RecordId rid;};
	pair* p;
	pair* startbuffer = (pair*)(buffer + sizeof(int));

	for (eid = 0; eid < getKeyCount(); eid++)
	{
		//int i = eid*sizeof(pair);
		p = &startbuffer[eid];
		if (p->key == searchKey) //equal or immediately larger than searchKey
			return 0;
		if (p->key > searchKey)
			return RC_NO_SUCH_RECORD;
	}
	
	return RC_NO_SUCH_RECORD;
}

/*
 * Read the (key, rid) pair from the eid entry.
 * @param eid[IN] the entry number to read the (key, rid) pair from
 * @param key[OUT] the key from the entry
 * @param rid[OUT] the RecordId from the entry
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::readEntry(int eid, int& key, RecordId& rid)
{
	if (eid >= getKeyCount())
		return RC_INVALID_CURSOR;

	struct pair{int key; RecordId rid;};
	pair* p;
	int i = eid*sizeof(pair);
	p = (pair*)(buffer+i + sizeof(int));
	key = p->key;
	rid = p->rid;

	return 0;
}

/*
 * Return the pid of the next slibling node.
 * @return the PageId of the next sibling node 
 */
PageId BTLeafNode::getNextNodePtr()
{
	//assume sibling ptr is at the very end of buffer
	PageId* p = (PageId*)(buffer + PageFile::PAGE_SIZE - sizeof(PageId));
	return *p;
}

/*
 * Set the pid of the next slibling node.
 * @param pid[IN] the PageId of the next sibling node 
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTLeafNode::setNextNodePtr(PageId pid)
{
	PageId* p = (PageId*)(buffer + PageFile::PAGE_SIZE - sizeof(PageId));
	*p = pid;

	return 0;
}

/*
 * Read the content of the node from the page pid in the PageFile pf.
 * @param pid[IN] the PageId to read
 * @param pf[IN] PageFile to read from
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::read(PageId pid, const PageFile& pf)
{
	return pf.read(pid, buffer); //each node is stored on a page, read page into buffer
}
    
/*
 * Write the content of the node to the page pid in the PageFile pf.
 * @param pid[IN] the PageId to write to
 * @param pf[IN] PageFile to write to
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::write(PageId pid, PageFile& pf)
{ 
	return pf.write(pid, buffer); //each node is stored on a page, write buffer to page
}

/*
 * Return the number of keys stored in the node.
 * @return the number of keys in the node
 */
int BTNonLeafNode::getKeyCount()
{
	int* count = (int*)buffer;
	return *count; //store at head of node
}

void BTNonLeafNode::incrKeyCount(int amt)
{
	int* count = (int*)buffer;
	*count += amt;
}

/*
 * Insert a (key, pid) pair to the node.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @return 0 if successful. Return an error code if the node is full.
 */
RC BTNonLeafNode::insert(int key, PageId pid)
{
	struct pair{int key; PageId pid;};
	pair* p;
	pair* startbuffer = (pair*)(buffer + sizeof(int) + sizeof(PageId)); //extra keyCount and PageId at start

	int maxkeys = (PageFile::PAGE_SIZE - sizeof(int) - sizeof(PageId))/sizeof(pair); //initial PageId at start
	//check if node full
	if (getKeyCount() >= maxkeys)
		return RC_NODE_FULL;

	//NonLeafNode has no locate function
	int eid;
	for (eid = 0; eid < getKeyCount(); eid++)
	{
		//int i = eid*sizeof(pair);
		p = &startbuffer[eid];
		if (p->key > key) //equal or immediately larger than key
			break;
	}
	p = &startbuffer[eid];
	
	if (eid == maxkeys)
		return RC_NODE_FULL;

	memmove(p+1, p, (getKeyCount()-eid)*sizeof(pair)); //NOTE: can't use memcpy bcs src dest overlap
	
	incrKeyCount();

	p->key = key;
	p->pid = pid;

	return 0;
}

/*
 * Insert the (key, pid) pair to the node
 * and split the node half and half with sibling.
 * The middle key after the split is returned in midKey.
 * @param key[IN] the key to insert
 * @param pid[IN] the PageId to insert
 * @param sibling[IN] the sibling node to split with. This node MUST be empty when this function is called.
 * @param midKey[OUT] the key in the middle after the split. This key should be inserted to the parent node.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::insertAndSplit(int key, PageId pid, BTNonLeafNode& sibling, int& midKey)
{
	RC rc;

	struct pair{int key; PageId pid;};
	pair* p;
	pair* startbuffer = (pair*)(buffer + sizeof(int) + sizeof(PageId));
	
	int half = (getKeyCount() + 1)/2; //then split
	pair* cutoff = (startbuffer + half);
	midKey = cutoff->key;
	int origKeyCount = getKeyCount();

	//is there a better way, to batch insert?
	for (int eid = half; eid < origKeyCount; eid++)
	{
		//int i = eid*sizeof(pair);
		p = &startbuffer[eid];
		if ((rc = sibling.insert(p->key, p->pid)) != 0)
			return rc;
		incrKeyCount(-1);
	}
	
	if (key < midKey)
		if ((rc = insert(key, pid)) != 0)
			return rc; //insert
	else
		if ((rc = sibling.insert(key, pid)) != 0)
			return rc; //insert

	return 0;
}

/*
 * Given the searchKey, find the child-node pointer to follow and
 * output it in pid.
 * @param searchKey[IN] the searchKey that is being looked up.
 * @param pid[OUT] the pointer to the child node to follow.
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::locateChildPtr(int searchKey, PageId& pid)
{
	struct pair{int key; PageId pid;};
	pair* startbuffer = (pair*)(buffer + sizeof(int) + sizeof(PageId));
	pair* p = startbuffer;

	//prob: buffer actually starts with a rid...check it first
	/*
	p = (pair*)(startbuffer);
	if (searchKey < p->key)
	{
		pid = (PageId*)(buffer+sizeof(int));
		return 0;
	}*/
	int eid;
	for (eid = 0; eid < getKeyCount(); eid++)
	{
		//int i = eid*sizeof(pair);
		p = &startbuffer[eid];
		if (searchKey < p->key)
		{
			pid = *((PageId*)p - 1); //return prev!!
			return 0;
		}
		//if (p->key > searchKey)
		//	return RC_NO_SUCH_RECORD;
	}

	p = &startbuffer[eid];
	pid = *((PageId*)p - 1);
	return 0;
}

/*
 * Initialize the root node with (pid1, key, pid2).
 * @param pid1[IN] the first PageId to insert
 * @param key[IN] the key that should be inserted between the two PageIds
 * @param pid2[IN] the PageId to insert behind the key
 * @return 0 if successful. Return an error code if there is an error.
 */
RC BTNonLeafNode::initializeRoot(PageId pid1, int key, PageId pid2)
{
	*((int*)buffer) = 1; //initialize keyCount = 1
	PageId* p = (PageId*)(buffer+sizeof(int));
	*p = pid1;
	int* k = (int*)(buffer+sizeof(PageId)+sizeof(int));
	*k = key;
	p = (PageId*)(buffer + sizeof(PageId) + sizeof(int) + sizeof(int));
	*p = pid2;
	
	//FIX all for loops

	return 0;
}

/*
//DEBUGGING
void BTLeafNode::printNode()
{
	cerr << "printing leaf\n";
	struct pair{int key; RecordId rid;};
	pair* p;
	cerr << "keyCount: " << *((int*)(buffer));
	pair* startbuffer = (pair*)(buffer + sizeof(int));
	
	for (int eid = 0; eid < getKeyCount(); eid++)
	{
		//int i = eid*sizeof(pair);
		p = &startbuffer[eid];
		cerr << "| key: " << p->key << ", rid.page: " << (p->rid).pid << ", rid.sid: " << (p->rid).sid;
	}
	cerr << endl;
}

//DEBUGGING
void BTNonLeafNode::printNode()
{
	cerr << "printing nonleaf\n";
	struct pair{int key; PageId pid;};
	pair* p;
	cerr << "keyCount: " << *((int*)(buffer));
	cerr << "| startpid: " << *((PageId*)(buffer+sizeof(int)));
	pair* startbuffer = (pair*)(buffer + sizeof(int) + sizeof(PageId));
	
	for (int eid = 0; eid < getKeyCount(); eid++)
	{
		//int i = eid*sizeof(pair);
		p = &startbuffer[eid];
		cerr << "| key: " << p->key << ", pid: " << p->pid;
	}
	cerr << endl;
}
*/