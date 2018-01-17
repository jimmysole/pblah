<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\FriendsException;

use Zend\Db\Adapter\Adapter;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Delete;

use Zend\Db\TableGateway\TableGateway;


class Friends
{
    /**
     * @var string
     */
    public static $user;
    
    /**
     * @var TableGateway
     */
    public static $table_gateway;
    
    /**
     * @var Sql
     */
    public static $sql;
    
    
    /**
     * @var array
     */
    protected static $browse_results = array();
    
    /**
     * @var integer
     */
    protected static $friend_id;
    
    /**
     * @var integer
     */
    protected static $request_id;
    
    
    /**
     * @var Select
     */
    private static $select;
    
    /**
     * 
     * @var Insert
     */
    private static $insert;
    
    /**
     * @var Delete
     */
    private static $delete;
    
    
    public static function setIds($request_id, $friend_id)
    {
        
        self::$friend_id    = (!empty($friend_id))    ? self::$friend_id    = $friend_id    : null;
        
        self::$request_id   = (!empty($request_id))   ? self::$request_id   = $request_id   : null;
        
        self::$select = new Select();
        
        self::$insert = new Insert();
        
        self::$delete = new Delete();
        
        return new self();
    }
    
    
    /**
     * Browses through the user's friend list with optional criteria
     * @param null|string $criteria
     * @param array $criteria_params
     * @throws FriendsException
     * @return array
     */
    public static function browseFriends($criteria = null, array $criteria_params = array())
    {
        if (null !== $criteria) {
            // determine what critera was passed
            if ($criteria == 'age') {
                self::$select->columns(array('profle_id', 'display_name', 'age', 'location', 'bio'))
                ->from('profiles')
                ->where(array('age' => intval($criteria_params['age']), 'friend_id' => $criteria_params['friend_id']));
                
                $query = self::getSQLClass()->getAdapter()->query(
                    self::getSQLClass()->buildSqlString(self::$select),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if (count($query) > 0) {
                    foreach ($query as $key => $value) {
                        self::$browse_results[$key] = $value;
                    }
                    
                    return self::$browse_results;
                } else {
                    throw new FriendsException("No friends were found with " . $criteria_params['age'] . " as the critera.");
                }
            } else if ($criteria == 'display_name') {
                self::$select->columns(array('profile_id', 'display_name', 'age', 'location', 'bio'))
                ->from('profiles')
                ->where(array('display_name' => $criteria_params['display_name'], 'friend_id' => $criteria_params['friend_id']));
                
                $query = self::getSQLClass()->getAdapter()->query(
                    self::getSQLClass()->buildSqlString(self::$select),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if (count($query) > 0) {
                    foreach ($query as $key => $value) {
                        self::$browse_results[$key] = $value;
                    }
                    
                    return self::$browse_results;
                } else {
                    throw new FriendsException("No friends were found with " . $criteria_params['display_name'] . " as the critera.");
                }
            } else if ($criteria == 'location') {
                self::$select->columns(array('profile_id', 'display_name', 'age', 'location', 'bio'))
                ->from('profiles')
                ->where(array('display_name' => $criteria_params['location'], 'friend_id' => $criteria_params['friend_id']));
                
                $query = self::getSQLClass()->getAdapter()->query(
                    self::getSQLClass()->buildSqlString(self::$select),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if (count($query) > 0) {
                    foreach ($query as $key => $value) {
                        self::$browse_results[$key] = $value;
                    }
                    
                    return self::$browse_results;
                } else {
                    throw new FriendsException("No friends were found with " . $criteria_params['location'] . " as the critera.");
                }
            } else {
                throw new FriendsException("Invalid search critera passed.");
            }
        } else {
            // display all friends based on friend id
            self::$select->columns(array('profile_id', 'display_name', 'age', 'location', 'bio'))
            ->from('profiles')
            ->where(array('friend_id' => $criteria_params['friend_id']));
            
            $query = self::getSQLClass()->getAdapter()->query(
                self::getSQLClass()->buildSqlString(self::$select),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if (count($query) > 0) {
                foreach ($query as $key => $value) {
                    self::$browse_results[$key] = $value;
                }
                
                return self::$browse_results;
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
    public static function sendAddRequest()
    {
        // see if a request is already pending first
        self::$select->columns(array('request_id', 'friend_id'))
        ->from('friend_requests')
        ->where(array('request_id' => self::$request_id, 'friend_id' => self::$friend_id));
        
        $query = self::getSQLClass()->getAdapter()->query(
            self::getSQLClass()->buildSqlString(self::$select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            throw new FriendsException("A friend request is already pending.");
        } else {
            // go ahead and insert into the friend_request table
            // request_id is the id of the current user logged in
            // and friend_id is the id of the user who the current user
            // logged in wants to be a friend with
            self::$insert->into('friend_requests')
            ->columns(array('request_id', 'friend_id'))
            ->values(array('request_id' => self::$request_id, 'friend_id' => self::$friend_id));
            
            $query = self::getSQLClass()->getAdapter()->query(
                self::getSQLClass()->buildSqlString(self::$insert),
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
    public static function cancelFriendRequest()
    {
        // remove both of the columns by matching up the request id and the friend id
        // since no duplicate requests ids are allowed (unique for each friend/person)
        // we can do a simple delete
        self::$select->columns(array('id'))
        ->from('friend_requests')
        ->where(array('request_id' => self::$request_id, 'friend_id' => self::$friend_id));
        
        $query = self::getSQLClass()->getAdapter()->query(
            self::getSQLClass()->buildSqlString(self::$select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            $row_id = array();
            
            foreach ($query as $val) {
                $row_id[] = $val;    
            }
            
            self::$delete->from('friend_requests')
            ->where(array('id' => $row_id[0]));
            
            $query = self::getSQLClass()->getAdapter()->query(
                self::getSQLClass()->buildSqlString(self::$delete),
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
    public static function approveFriendRequest()
    {
        // if approved, add request id to the friends table
        // and then delete the friend request
        self::$select->columns(array('id', 'request_id', 'friend_id'))
        ->from('friend_requests')
        ->where(array('request_id' => self::$request_id, 'friend_id' => self::$friend_id));
        
        $query = self::getSQLClass()->getAdapter()->query(
            self::getSQLClass()->buildSqlString(self::select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            $request_id = array();
            
            foreach ($query as $value) {
                $request_id[] = $value['id'];
            }
            
            // insert now into friends table
            self::$insert->into('friends')
            ->columns(array('friend_id', 'user_id'))
            ->values(array('friend_id' => self::$friend_id, 'user_id' => self::getUserId()['id']));
            
            $query = self::getSQLClass()->getAdapter()->query(
                self::getSQLClass()->buildSqlString(self::insert),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if (count($query) > 0) {
                // delete from friend_requests now
                self::$delete->from('friend_requests')
                ->where(array('id' => $request_id[0]));
                
                $query = self::getSQLClass()->getAdapter()->query(
                    self::getSQLClass()->buildSqlString(self::$delete),
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
    
    
    public static function blockFriendRequest($who, array $params = array())
    {
        // really necessary?
    }
    
    
    /**
     * Denies a pending friend request
     * @throws FriendsException
     * @return boolean
     */
    public static function denyFriendRequest()
    {
        // delete the request from the friend_requests table
        self::$delete->from('friend_requests')
        ->where(array('request_id' => self::$request_id, 'friend_id' => self::$friend_id));
        
        $query = self::getSQLClass()->getAdapter()->query(
            self::getSQLClass()->buildSqlString(self::$delete),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            return true;
        } else {
            throw new FriendsException("Error removing denied friend request, please try again.");
        }
    }
    
    
    
    
 
    
    
    /**
     * Setter method
     * @param string $user
     * @return \Members\Profile
     */
    public static function setUser($user)
    {
        self::$user = $user;
        
        return new self();
    }
    
    
    /**
     * Getter method
     * @return string
     */
    public static function getUser()
    {
        return self::$user;
    }
    
    
    /**
     * Gets the user id
     * @return ResultSet|boolean
     */
    public static function getUserId()
    {
        $select = new Select('members');
        
        $select->columns(array('*'))
        ->where(array('username' => self::getUser()));
        
        
        $query = self::getSQLClass()->getAdapter()->query(
            self::$sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            foreach ($query as $result) {
                $row = $result;
            }
            
            return $row;
        }
        
        return false;
    }
    
    
    /**
     * Gets table gateway instance
     * @param TableGateway $gateway
     * @return NULL|\Zend\Db\TableGateway\TableGateway
     */
    public static function getTableGateway(TableGateway $gateway)
    {
        self::$table_gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        return self::$table_gateway;
    }
    
    
    /**
     * gets sql instance
     * @return \Zend\Db\Sql\Sql
     */
    public static function getSQLClass()
    {
        self::$sql = new Sql(self::$table_gateway->getAdapter());
        
        return self::$sql;
    }
}