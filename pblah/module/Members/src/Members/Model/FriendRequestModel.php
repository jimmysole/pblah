<?php

namespace Members\Model;

use Members\Model\Classes\Friends;
use Zend\Db\TableGateway\TableGateway;


class FriendRequestModel extends Friends
{
    public $gateway;
    
    
    /**
     * Constructor
     * @param TableGateway $gateway
     * @param string $user
     */
    public function __construct(TableGateway $gateway, $user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        parent::getTableGateway($this->gateway);
        parent::setUser($user);
    }
    
    
    /**
     * Sends a friend request
     * @param integer $request_id
     * @param integer $friend_id
     * @return boolean
     */
    public function sendFriendRequest($request_id, $friend_id)
    {
        return parent::setIds($request_id, $friend_id)->sendAddRequest();
    }
}