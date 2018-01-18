<?php

namespace Members\Model;

use Zend\Db\TableGateway\TableGateway;

use Members\Model\Classes\Friends;
use Members\Model\Classes\Messages;


class FriendsModel extends Friends
{
    public $gateway;
    
    
    public function __construct(TableGateway $gateway, $user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        parent::getTableGateway($this->gateway);
        parent::setUser($user);
    }
    
    
    public function sendFriendMessage($to, array $message)
    {
        parent::messageFriend(new Messages(), $to, $message);
    }
}