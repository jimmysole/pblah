<?php

namespace Members\Model;

use Members\Model\Classes\Friends;
use Zend\Db\TableGateway\TableGateway;


class FriendsModel extends Friends
{
    public $gateway;
    
    
    public function __construct(TableGateway $gateway, $user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        parent::getTableGateway($this->gateway);
        parent::setUser($user);
    }
}