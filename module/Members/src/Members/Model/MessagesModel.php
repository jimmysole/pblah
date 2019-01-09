<?php

namespace Members\Model;

use Members\Model\Interfaces\MessagesInterface;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;


class MessagesModel implements MessagesInterface
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
     * @var Sql
     */
    public $sql;
    
    /**
     * @var Select
     */
    public $select;
      
    
    /**
     * Constructor method for MessagesModel class
     * @param TableGateway $gateway
     * @param string $user
     */
     public function __construct(TableGateway $gateway, $user)
     {
         $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
            
         $this->select = new Select();
            
         $this->sql = new Sql($this->gateway->getAdapter());
            
         $this->user = $user;
     }
    
    

    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\MessagesInterface::sendMessage()
     */
    public function sendMessage($to, array $message)
    {
        
    }
}