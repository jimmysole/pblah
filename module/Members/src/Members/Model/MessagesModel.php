<?php

namespace Members\Model;



use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;


use Members\Model\Exceptions\MessagesException;
use Members\Model\Interfaces\MessagesInterface;


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
        
        $connection = $this->sql->getAdapter()->getDriver()->getConnection();
        
        $query = $connection->execute("SELECT private_messages.to AS pmsg_to, private_messages.from AS pmsg_from,
            private_messages.subject AS pmsg_subject, private_messages.message AS pmsg_message, private_messages.date_received AS pmsg_drec
            FROM private_messages
            INNER JOIN members ON private_messages.user_id = members.id
            WHERE members.id = " . $this->getUserId()['id']);
        
       
        
        if ($query->count() > 0) {
            $messages_holder = [];
            
            foreach ($query as $key => $messages) {
                $messages_holder = array_merge_recursive($messages_holder, array($key => $messages));
            }
            
            return $messages_holder;
        } else {
            throw new MessagesException("No messages currently are in your inbox.");
        }
    }
    
    
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