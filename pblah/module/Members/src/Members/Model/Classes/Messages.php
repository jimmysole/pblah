<?php

namespace Members\Model\Classes;

use Zend\Db\Sql\Insert;
use Zend\Db\Adapter\Adapter;


use Members\Model\Classes\Exceptions\MessagesException;




class Messages extends Friends
{
    
    public $to;
    
    public $message = array();
    
    private $insert;
    
    
    public function __construct()
    {
        $this->insert = new Insert();
    }
    
    
    /**
     * Sends a private message to a user from a user
     * @param string $to
     * @param array $message
     * @throws MessagesException
     * @return boolean
     */
    public function sendMessage($to, array $message)
    {
        $this->to = (!empty($to)) ? $this->to = $to : null;
        
        if (count($message) > 0) {
            foreach ($message as $key => $value) {
                $this->message[$key] = $value;
            }
        } else {
            throw new MessagesException("Cannot send a empty message.");
        }
        
        // send the message to the user from the sender
        $this->insert->into('private_messages')
        ->columns(array('to', 'from', 'message', 'date_received', 'active'))
        ->values(array('to' => $to, 'from' => $this->getFrom(), 'message' => $this->message['message'],
            'date_received' => $this->message['date_received'], 'active' => 1));
        
        $query = parent::getSQLClass()->getAdapter()->query(
            parent::getSQLClass()->buildSqlString($this->insert),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            return true;
        }
        
        return false;
    }
    
    
    
    
    
    public function setFrom($from)
    {
        $this->from = $from;
        
        return $this;
    }
    
    
    public function getFrom()
    {
        return $this->from;
    }
}
