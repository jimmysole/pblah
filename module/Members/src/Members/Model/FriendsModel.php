<?php

namespace Members\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Delete;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

use Members\Model\Interfaces\FriendsInterface;
use Members\Model\Interfaces\MessagesInterface;
use Members\Model\Exceptions\FriendsException;
use Zend\Db\Adapter\Driver\ConnectionInterface;




class FriendsModel implements FriendsInterface
{
    /**
     * @var TableGateway
     */
    public $gateway;
    
    /**
     * @var Select
     */
    public $select;
    
    /**
     * @var Insert
     */
    public $insert;
    
    /**
     * @var Delete
     */
    public $delete;
    
    /**
     * @var Sql
     */
    public $sql;
    
    /**
     * @var ConnectionInterface
     */
    public $connection;
    
    
    /**
     * @var array
     */
    public $browse_results = array();
    
    /**
     * @var string
     */
    public $user;
    
    
    
    
    
    /**
     * Constructor
     * @param TableGateway $gateway
     * @param string $user
     */
    public function __construct(TableGateway $gateway, $user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        $this->select = new Select();
        
        $this->insert = new Insert();
        
        $this->delete = new Delete();
        
        $this->sql = new Sql($this->gateway->getAdapter());
        
        $this->connection = $this->sql->getAdapter()->getDriver()->getConnection();
        
        $this->user = $user;
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Classes\Friends::browseFriends()
     */
    public function browseFriends($criteria = null, array $criteria_params = array())
    {
        if (null !== $criteria) {
            // determine which criteria was passed
            if ($criteria == 'age') {
                // search friends by age
                // see if criteria params was passed
                // if any criteria is passed, the params must be as well
                if (count($criteria_params) > 0) {
                    if (array_key_exists('age', $criteria_params)) {
                        $age = !empty($criteria_params['age']) ? intval($criteria_params['age']) : null;
                        
                        $this->select->columns(array('profile_id', 'display_name', 'age', 'location', 'bio'))
                        ->from('profiles')
                        ->where(array('age' => $age, 'friend_id' => $this->getUserId()['id']))
                        ->order(array('profile_id DESC'));
                        
                        $query = $this->sql->getAdapter()->query(
                            $this->sql->buildSqlString($this->select),
                            Adapter::QUERY_MODE_EXECUTE
                        );
                        
                        if ($query->count() > 0) {
                            foreach ($query as $key => $value) {
                                $this->browse_results[$key] = $value;
                            }
                            
                            return $this->browse_results;
                        } else {
                            throw new FriendsException(sprintf("No results were found for user(s) with age %d", $age));
                        }
                    } else {
                        throw new FriendsException("No age was passed for the criteria, please do so and try again.");
                    }
                } else {
                    throw new FriendsException("No search criteria was passed, please do so and try again.");
                }
            } else if ($criteria == 'display_name') {
                // search friends by display name
                // see again if criteria params were passed
                // if any criteria is passed, the params must be as well
                if (count($criteria_params) > 0) {
                    if (array_key_exists('display_name', $criteria_params)) {
                        $display_name = !empty($criteria_params['display_name']) ? $criteria_params['display_name'] : null;
                        
                        $this->select->columns(array('profile_id', 'display_name', 'age', 'location', 'bio'))
                        ->from('profiles')
                        ->where(array('display_name' => $display_name, 'friend_id' => $this->getUserId()['id']))
                        ->order(array('profile_id DESC'));
                        
                        $query = $this->sql->getAdapter()->query(
                            $this->sql->buildSqlString($this->select),
                            Adapter::QUERY_MODE_EXECUTE
                        );
                        
                        if ($query->count() > 0) {
                            foreach ($query as $key => $value) {
                                $this->browse_results[$key] = $value;
                            }
                            
                            return $this->browse_results;
                        } else {
                            throw new FriendsException(sprintf("No results were found for a user with %s", $display_name));
                        }
                    } else {
                        throw new FriendsException("No display name was passed for the criteria, please do so and try again.");
                    }
                } else {
                    throw new FriendsException("No search criteria was passed, please do so and try again.");
                }
            } else if ($criteria == 'location') {
                // search friends by location
                // see once more if criteria params were passed
                // if any criteria is passed, the params must be as well
                if (count($criteria_params) > 0) {
                    if (array_key_exists('location', $criteria_params)) {
                        $location = !empty($criteria_params['location']) ? $criteria_params['location'] : null;
                        
                        $this->select->columns(array('profile_id', 'display_name', 'age', 'location', 'bio'))
                        ->from('profiles')
                        ->where(array('location' => $location, 'friend_id' => $this->getUserId()['id']))
                        ->order(array('profile_id DESC'));
                        
                        $query = $this->sql->getAdapter()->query(
                            $this->sql->buildSqlString($this->select),
                            Adapter::QUERY_MODE_EXECUTE
                        );
                        
                        if ($query->count() > 0) {
                            foreach ($query as $key => $value) {
                                $this->browse_results[$key] = $value;
                            }
                            
                            return $this->browse_results;
                        } else {
                            throw new FriendsException(sprintf("No results were found for user(s) with %s", $location));
                        }
                    } else {
                        throw new FriendsException("No location was passed for the criteria, please do so and try again.");
                    }
                } else {
                    throw new FriendsException("No search criteria was passed, please do so and try again.");
                }
            } else {
                throw new FriendsException("Invalid search criteria passed.");
            }
        } else {
            // display all friends based on friend id
            $this->select->columns(array('profile_id', 'display_name', 'age', 'location', 'bio'))
            ->from('profiles')
            ->where(array('friend_id' => $this->getUserId()['id']))
            ->order(array('profile_id DESC'));
            
            $query = $this->sql->getAdapter()->query(
                $this->sql->buildSqlString($this->select),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if ($query->count() > 0) {
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
     * {@inheritDoc}
     * @see \Members\Model\Classes\Friends::sendAddRequest()
     */
    public function sendAddRequest($friend_id)
    {
        // see if a request is already pending first
        $this->select->columns(array('id'))
        ->from('friend_requests')
        ->where(array('request_id' => $this->getUserId()['id'], 'friend_id' => $friend_id));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            throw new FriendsException("A friend request is already pending.");
        } else {
            // go ahead and insert into the friend requests table
            // friend id is the id of the user that this user
            // wants to be friends with
            $this->insert->into('friend_requests')
            ->columns(array('request_id', 'friend_id'))
            ->values(array('request_id' => $this->getUserId()['id'], 'friend_id' => $friend_id));
            
            $query = $this->sql->getAdapter()->query(
                $this->sql->buildSqlString($this->insert),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if ($query->count() > 0) {
                return true;
            } else {
                throw new FriendsException("Error sending your friend request, please try again.");
            }
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Classes\Friends::cancelFriendRequest()
     */
    public function cancelFriendRequest($friend_id)
    {
        // remove both of the columns by matching up the request id and the friend id
        // since no duplicate requests ids are allowed (unique for each friend/person)
        // we can do a simple delete by using the id 
        $this->select->columns(array('id'))
        ->from('friend_requests')
        ->where(array('request_id' => $this->getUserId()['id'], 'friend_id' => $friend_id));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        
        if ($query->count() > 0) {
            // delete the request
            $row_id = array();
            
            foreach ($query as $value) {
                $row_id[] = $value;
            }
            
            $this->delete->from('friend_requests')
            ->where(array('id' => $row_id[0]));
            
            $query = $this->sql->getAdapter()->query(
                $this->sql->buildSqlString($this->delete),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if ($query->count() > 0) {
                return true;
            } else {
                throw new FriendsException("Error cancelling your friend request, please try again.");
            }
        } else {
            throw new FriendsException("No pending requests were found with the supplied info.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Classes\Friends::approveFriendRequest()
     */
    public function approveFriendRequest($friend_id)
    {
        // if approved, add request id to the friends table for friend id
        // and then delete the friend request
        $this->select->columns(array('id', 'request_id', 'friend_id'))
        ->from('friend_requests')
        ->where(array('request_id' => $this->getUserId()['id'], 'friend_id' => $friend_id));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            $id = array();
            
            foreach ($query as $value) {
                $id[] = $value['id'];
            }
            
            // insert now into the friends table
            $data = array(
                'friend_id' => $friend_id,
                'user_id'   => $this->getUserId()['id']
            );
            
            if ($this->gateway->insert($data) > 0) {
                // delete from friend requests now
                $this->delete->from('friend_requests')
                ->where(array('id' => $id[0]));
                
                $query = $this->sql->getAdapter()->query(
                    $this->sql->buildSqlString($this->delete),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if ($query->count() > 0) {
                    return true;
                } else {
                    throw new FriendsException("Error removing friend request (but you are still friends with the user). ");
                }
            } else {
                throw new FriendsException("Error finding user in friends table, aborting.");
            }
        } else {
            throw new FriendsException("No friend requests were found with the supplied info.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Classes\Friends::denyFriendRequest()
     */
    public function denyFriendRequest($friend_id)
    {
        // delete the request from the friend requests table
        $this->delete->from('friend_requests')
        ->where(array('request_id' => $this->getUserId()['id'], 'friend_id' => $friend_id));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->delete),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            return true;
        } else {
            throw new FriendsException("Error removing friend request, please try again.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Classes\Friends::followFriend()
     */
    public function followFriend()
    {
        
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Classes\Friends::unfollowFriend()
     */
    public function unfollowFriend()
    {
        
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Classes\Friends::messageFriend()
     */
    public function messageFriend(MessagesInterface $messages, $to, array $message)
    {
        
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\FriendsInterface::getFriendsOnline()
     */
    public function getFriendsOnline()
    {
        $query = $this->connection->execute("SELECT members.username AS friend_username, friends_online.user_id FROM friends_online, friends, members
            WHERE friends_online.user_id = friends.friend_id AND members.id = friends.friend_id AND friends.user_id = " . $this->getUserId()['id']);
        
        if ($query->count() > 0) {
            // get the username of the friend(s) online
            $friends_online = array();
            
            foreach ($query as $values) {
                $friends_online[] = $values['friend_username'];
            }
            
            return $friends_online;
        } else {
            throw new FriendsException("No friends are online.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\FriendsInterface::populateChatList()
     */
    public function populateChatList()
    {
        $query = $this->connection->execute("SELECT members.username AS chat_username, friends_online.user_id AS chat_user_id FROM friends_online, friends, members
            WHERE friends_online.user_id = friends.friend_id AND members.id = friends.friend_id AND friends.user_id = " . $this->getUserId()['id']);
        
        if ($query->count() > 0) {
            // get the usernames of the friends
            $chat_id = array();
            $chat_friends = array();
            
            foreach ($query as $rows) {
                $chat_id[] = $rows['chat_user_id'];
                $chat_friends[] = $rows['chat_username'];
            }
            
            return array_combine($chat_id, $chat_friends);
        } else {
            throw new FriendsException("Could not build chat list, probably because no one friends are on.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\FriendsInterface::friendList()
     */
    public function friendList()
    {
        $query = $this->connection->execute("SELECT members.username AS friend_username FROM friends AS f
            INNER JOIN members ON members.id = f.friend_id WHERE f.user_id = " . $this->getUserId()['id']);
        
        if ($query->count() > 0) {
            // grab the usernames of the friends for the user
            $friends = array();
            
            foreach ($query as $values) {
                $friends[] = $values['friend_username'];
            }
            
            return $friends;
        } else {
            throw new FriendsException("Looks like you don't have any friends, go make some!");
        }
    }
    
    
    /**
     * Gets the user id
     * 
     * @return ResultSet|boolean
     */
    public function getUserId()
    {
        $this->select->columns(array('*'))
        ->from('members')
        ->where(array('username' => $this->user));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            foreach ($query as $result) {
                $row = $result;
            }
            
            return $row;
        }
        
        return false;
    }
}