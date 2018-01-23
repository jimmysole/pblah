<?php

namespace Members\Model;

use Zend\Db\TableGateway\TableGateway;

use Members\Model\Interfaces\StatusInterface;


class StatusModel implements StatusInterface
{
    /**
     * @var TableGateway
     */
    public $gateway;
    
    /**
     * @var string
     */
    public $user;
    
    
    /**
     * Constructor method for StatusModel class
     * @param TableGateway $gateway
     * @param string $user
     */
    public function __construct(TableGateway $gateway, $user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        $this->user = $user;
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\StatusInterface::postStatus()
     */
    public function postStatus($status)
    {
        
    }
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\StatusInterface::getStatus()
     */
    public function getStatus()
    {
        
    }
}