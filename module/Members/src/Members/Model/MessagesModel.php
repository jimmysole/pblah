<?php

namespace Members\Model;

use Members\Model\Interfaces\MessagesInterface;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Members\Model\Exceptions\MessagesException;


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
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\MessagesInterface::getMessages()
     */
    public function getMessages()
    {
        $select = $this->gateway->select(array('from' => $this->user));
        
        if ($select->count() > 0) {
            $messages_holder = [];
            
            foreach ($select as $messages) {
                $messages_holder[] = $messages;
            }
            
            return $messages_holder;
        } else {
            throw new MessagesException("No messages currently are in your inbox.");
        }
    }
}