<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\FriendsException;

use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;


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
    
    
    public function __construct()
    {
        $this->user = parent::getUser();
    }
    
    
    public function browseFriends($critera = null, array $critera_params = array())
    {
        if (null !== $critera) {
            $select = new Select('profiles');
            
            // determine what critera was passed
            if ($critera == 'age') {
                $select->columns(array('profle_id', 'display_name', 'age', 'location', 'bio'))
                ->where(array('age' => intval($critera_params['age'])));
                
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
                    throw new FriendsException("No friends were found with " . $critera_params['age'] . " as the critera.");
                }
                
            } else if ($critera == 'display_name') {
                
            } else if ($critera == 'location') {
                
            } else {
                throw new FriendsException("Invalid search critera passed.");
            }
        } else {
            
        }
    }
    
    
    public function sendAddRequest($friend_name, array $params = array())
    {
        
    }
    
    
    public function cancelAddRequest()
    {
        
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