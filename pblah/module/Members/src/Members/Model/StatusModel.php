<?php

namespace Members\Model;

use Members\Model\Classes\Members;
use Zend\Db\TableGateway\TableGateway;


class StatusModel extends Members
{
    /**
     * @var TableGateway
     */
    public $gateway;
    
    
    /**
     * Constructor method for StatusModel class
     * @param TableGateway $gateway
     * @param string $user
     */
    public function __construct(TableGateway $gateway, $user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        parent::getTableGateway($this->gateway);
        parent::getSQLClass();
        parent::setUser($user);
    }
    
    
    public function postCurrentStatus(array $data)
    {
       return parent::postStatus($data['status']);
    }
    
    
    public function getCurrentStatus($user)
    {
        return parent::getStatus($user);
    }
}