<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\FriendsException;

use Zend\Db\Adapter\Adapter;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Delete;


class Friends extends Members
{
    /**
     * @var array
     */
    protected $browse_results = array();
    
    /**
     * @var integer
     */
    protected $friend_id;
    
    /**
     * @var integer
     */
    protected $request_id;
    
    
    /**
     * @var Select
     */
    private $select;
    
    /**
     * 
     * @var Insert
     */
    private $insert;
    
    /**
     * @var Delete
     */
    private $delete;
    
    /**
     * Constructor
     */
    public function __construct($request_id, $friend_id)
    {
        
        $this->friend_id    = (!empty($friend_id))    ? $this->friend_id    = $friend_id    : null;
        
        $this->request_id   = (!empty($request_id))   ? $this->request_id   = $request_id   : null;
        
        $this->select = new Select();
        
        $this->insert = new Insert();
        
        $this->delete = new Delete();
    }
    
    
    /**
     * Browses through the user's friend list with optional criteria
     * @param null|string $criteria
     * @param array $criteria_params
     * @throws FriendsException
     * @return array
     */
    public function browseFriends($criteria = null, array $criteria_params = array())
    {
        if (null !== $criteria) {
            // determine what critera was passed
            if ($criteria == 'age') {
                $this->select->columns(array('profle_id', 'display_name', 'age', 'location', 'bio'))
                ->from('profiles')
                ->where(array('age' => intval($criteria_params['age']), 'friend_id' => $criteria_params['friend_id']));
                
                $query = parent::getSQLClass()->getAdapter()->query(
                    parent::getSQLClass()->buildSqlString($this->select),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if (count($query) > 0) {
                    foreach ($query as $key => $value) {
                        $this->browse_results[$key] = $value;
                    }
                    
                    return $this->browse_results;
                } else {
                    throw new FriendsException("No friends were found with " . $criteria_params['age'] . " as the critera.");
                }
            } else if ($criteria == 'display_name') {
                $this->select->columns(array('profile_id', 'display_name', 'age', 'location', 'bio'))
                ->from('profiles')
                ->where(array('display_name' => $criteria_params['display_name'], 'friend_id' => $criteria_params['friend_id']));
                
                $query = parent::getSQLClass()->getAdapter()->query(
                    parent::getSQLClass()->buildSqlString($this->select),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if (count($query) > 0) {
                    foreach ($query as $key => $value) {
                        $this->browse_results[$key] = $value;
                    }
                    
                    return $this->browse_results;
                } else {
                    throw new FriendsException("No friends were found with " . $criteria_params['display_name'] . " as the critera.");
                }
            } else if ($criteria == 'location') {
                $this->select->columns(array('profile_id', 'display_name', 'age', 'location', 'bio'))
                ->from('profiles')
                ->where(array('display_name' => $criteria_params['location'], 'friend_id' => $criteria_params['friend_id']));
                
                $query = parent::getSQLClass()->getAdapter()->query(
                    parent::getSQLClass()->buildSqlString($this->select),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if (count($query) > 0) {
                    foreach ($query as $key => $value) {
                        $this->browse_results[$key] = $value;
                    }
                    
                    return $this->browse_results;
                } else {
                    throw new FriendsException("No friends were found with " . $criteria_params['location'] . " as the critera.");
                }
            } else {
                throw new FriendsException("Invalid search critera passed.");
            }
        } else {
            // display all friends based on friend id
            $this->select->columns(array('profile_id', 'display_name', 'age', 'location', 'bio'))
            ->from('profiles')
            ->where(array('friend_id' => $criteria_params['friend_id']));
            
            $query = parent::getSQLClass()->getAdapter()->query(
                parent::getSQLClass()->buildSqlString($this->select),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if (count($query) > 0) {
                foreach ($query as $key => $value) {
                    $this->browse_results[$key] = $value;
                }
                
                return $this->browse_results;
            } else {
                throw new FriendsException("No friends were found.");
            }
        }
    }
    
    
    /**
     * Sends a friend add request
     * @throws FriendsException
     * @return boolean
     */
    public function sendAddRequest()
    {
        // see if a request is already pending first
        $this->select->columns(array('request_id', 'friend_id'))
        ->from('friend_requests')
        ->where(array('request_id' => $this->request_id, 'friend_id' => $this->friend_id));
        
        $query = parent::getSQLClass()->getAdapter()->query(
            parent::getSQLClass()->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            throw new FriendsException("A friend request is already pending.");
        } else {
            // go ahead and insert into the friend_request table
            // request_id is the id of the current user logged in
            // and friend_id is the id of the user who the current user
            // logged in wants to be a friend with
            $this->insert->into('friend_requests')
            ->columns(array('request_id', 'friend_id'))
            ->values(array('request_id' => $this->request_id, 'friend_id' => $this->friend_id));
            
            $query = parent::getSQLClass()->getAdapter()->query(
                parent::getSQLClass()->buildSqlString($this->insert),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if (count($query) > 0) {
                return true;
            } else {
                throw new FriendsException("Error sending your friend request, please try again.");
            }
        }
    }
    
    
    /**
     * Cancels a pending friend request
     * @throws FriendsException
     * @return boolean
     */
    public function cancelFriendRequest()
    {
        // remove both of the columns by matching up the request id and the friend id
        // since no duplicate requests ids are allowed (unique for each friend/person)
        // we can do a simple delete
        $this->select->columns(array('id'))
        ->from('friend_requests')
        ->where(array('request_id' => $this->request_id, 'friend_id' => $this->friend_id));
        
        $query = parent::getSQLClass()->getAdapter()->query(
            parent::getSQLClass()->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            $row_id = array();
            
            foreach ($query as $val) {
                $row_id[] = $val;    
            }
            
            $this->delete->from('friend_requests')
            ->where(array('id' => $row_id[0]));
            
            $query = parent::getSQLClass()->getAdapter()->query(
                parent::getSQLClass()->buildSqlString($this->delete),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if (count($query) > 0) {
                return true;
            } else {
                throw new FriendsException("Error cancelling your friend request, please try again.");
            }
        } else {
            throw new FriendsException("Could not locate the supplied friend request.");
        }
    }
    
    
    /**
     * Approves a pending friend request
     * @throws FriendsException
     * @return boolean
     */
    public function approveFriendRequest()
    {
        // if approved, add request id to the friends table
        // and then delete the friend request
        $this->select->columns(array('id', 'request_id', 'friend_id'))
        ->from('friend_requests')
        ->where(array('request_id' => $this->request_id, 'friend_id' => $this->friend_id));
        
        $query = parent::getSQLClass()->getAdapter()->query(
            parent::getSQLClass()->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            $request_id = array();
            
            foreach ($query as $value) {
                $request_id[] = $value['id'];
            }
            
            // insert now into friends table
            $this->insert->into('friends')
            ->columns(array('friend_id', 'user_id'))
            ->values(array('friend_id' => $this->friend_id, 'user_id' => parent::getUserId()['id']));
            
            $query = parent::getSQLClass()->getAdapter()->query(
                parent::getSQLClass()->buildSqlString($this->insert),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if (count($query) > 0) {
                // delete from friend_requests now
                $this->delete->from('friend_requests')
                ->where(array('id' => $request_id[0]));
                
                $query = parent::getSQLClass()->getAdapter()->query(
                    parent::getSQLClass()->buildSqlString($this->delete),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if (count($query) > 0) {
                    return true;
                } else {
                    throw new FriendsException("Error removing friend request, please try again.");
                }
            } else {
                throw new FriendsException("Error finding user in friends table, aborting.");
            }
        } else {
            throw new FriendsException("Request id not found.");
        }
    }
    
    
    public function blockFriendRequest($who, array $params = array())
    {
        // really necessary?
    }
    
    
    /**
     * Denies a pending friend request
     * @throws FriendsException
     * @return boolean
     */
    public function denyFriendRequest()
    {
        // delete the request from the friend_requests table
        $this->delete->from('friend_requests')
        ->where(array('request_id' => $this->request_id, 'friend_id' => $this->friend_id));
        
        $query = parent::getSQLClass()->getAdapter()->query(
            parent::getSQLClass()->buildSqlString($this->delete),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            return true;
        } else {
            throw new FriendsException("Error removing denied friend request, please try again.");
        }
    }
}