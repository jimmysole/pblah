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
     * @var string
     */
    protected $user;
    
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
     * Constructor
     */
    public function __construct($request_id, $friend_id)
    {
        
        $this->friend_id    = (!empty($friend_id))    ? $this->friend_id    = $friend_id    : null;
        $this->request_id   = (!empty($request_id))   ? $this->request_id   = $request_id   : null;
        
        $this->user = parent::getUser();
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
            $select = new Select('profiles');
            
            // determine what critera was passed
            if ($criteria == 'age') {
                $select->columns(array('profle_id', 'display_name', 'age', 'location', 'bio'))
                ->where(array('age' => intval($criteria_params['age']), 'friend_id' => $criteria_params['friend_id']));
                
                $query = parent::getSQLClass()->getAdapter()->query(
                    parent::getSQLClass()->buildSqlString($select),
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
                $select->columns(array('profile_id', 'display_name', 'age', 'location', 'bio'))
                ->where(array('display_name' => $criteria_params['display_name'], 'friend_id' => $criteria_params['friend_id']));
                
                $query = parent::getSQLClass()->getAdapter()->query(
                    parent::getSQLClass()->buildSqlString($select),
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
                $select->columns(array('profile_id', 'display_name', 'age', 'location', 'bio'))
                ->where(array('display_name' => $criteria_params['location'], 'friend_id' => $criteria_params['friend_id']));
                
                $query = parent::getSQLClass()->getAdapter()->query(
                    parent::getSQLClass()->buildSqlString($select),
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
            $select = new Select('profiles');
            
            $select->columns(array('profile_id', 'display_name', 'age', 'location', 'bio'))
            ->where(array('friend_id' => $criteria_params['friend_id']));
            
            $query = parent::getSQLClass()->getAdapter()->query(
                parent::getSQLClass()->buildSqlString($select),
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
        $select = new Select('friend_requests');
        
        $select->columns(array('request_id', 'friend_id'))
        ->where(array('request_id' => $this->request_id, 'friend_id' => $this->friend_id));
        
        $query = parent::getSQLClass()->getAdapter()->query(
            parent::getSQLClass()->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            throw new FriendsException("A friend request is already pending.");
        } else {
            // go ahead and insert into the friend_request table
            // request_id is the id of the current user logged in
            // and friend_id is the id of the user who the current user
            // logged in wants to be a friend with
            $insert = new Insert('friend_requests');
            
            $insert->columns(array('request_id', 'friend_id'))
            ->values(array('request_id' => $this->request_id, 'friend_id' => $this->friend_id));
            
            $query = parent::getSQLClass()->getAdapter()->query(
                parent::getSQLClass()->buildSqlString($insert),
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
        // since no duplicate friend ids are allowed (unique for each friend/person)
        // we can do a simple delete
        $delete = new Delete('friend_requests');
        
        $delete->where(array('request_id' => $this->request_id, 'friend_id' => $this->friend_id));
        
        $query = parent::getSQLClass()->getAdapter()->query(
            parent::getSQLClass()->buildSqlString($delete),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            return true;
        } else {
            throw new FriendsException("Error cancelling your friend request, please try again.");
        }
    }
    
    
    public function blockFriendRequest($who, array $params = array())
    {
        
    }
    
    
    public function approveFriendRequest()
    {
        
    }
    
    
    public function denyFriendRequest()
    {
        
    }
}