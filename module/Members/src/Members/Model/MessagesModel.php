<?php

namespace Members\Model;



use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;


use Members\Model\Exceptions\MessagesException;
use Members\Model\Interfaces\MessagesInterface;
use Zend\Db\ResultSet\ResultSet;
use Members\Model\Filters\Messages;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Insert;


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
     * @var string
     */
    public $to;
    
    
    /**
     * @var string
     */
    public $subject;
    
    
    /**
     * @var string
     */
    public $message;
      
    
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
    public function sendMessage($to, $subject, $message)
    {
        $this->to      = (!empty($to))      ? $to      : null;
        $this->subject = (!empty($subject)) ? $subject : "No Subject";
        $this->message = (!empty($message)) ? $message : null;
        
        // check the members table to see if user exists
        $select = new Select();
        
        $select->from('members')
        ->columns(array('id', 'username'))
        ->where(array('username' => $this->to));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            // user was found
            // insert into private messages table now
            foreach ($query as $result) {
                $row = $result;
            }
            
            $insert = new Insert();
            
            $insert->into('private_messages')
            ->columns(array('user_id', 'to', 'from', 'subject', 'message', 'date_received', 'active'))
            ->values(array('user_id' => $row['id'], 'to' => $row['username'],
                'from' => $this->user, 'subject' => $this->subject, 'message' => $this->message, 
                'date_received' => date('Y-m-d H:i:s'), 'active' => 1));
            
            $query = $this->sql->getAdapter()->query(
                $this->sql->buildSqlString($insert),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if ($query->count() > 0) {
                return true;
            } else {
                throw new MessagesException("Error sending your message");
            }
        } else {
            throw new MessagesException("User does not exist.");
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
    
    
    public function getMembers()
    {
        $select = new Select();
        
        $select->from('members')->columns(array('username'))->where('id != ' . $this->getUserId()['id']);
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            $rows = array();
            
            foreach ($query as $result) {
                $rows[] = $result;
            }
            
            return $rows;
        } else {
            return false;
        }
    }
    
    
    public function getAllMessages() 
    {
        $select = new Select();
        
        $select->from('private_messages')->where(array('user_id' => $this->getUserId()['id']));
        
        $result_set_prototype = new ResultSet();
        
        $result_set_prototype->setArrayObjectPrototype(new Messages());
        
        $paginator_adapter = new DbSelect($select, $this->gateway->getAdapter(), $result_set_prototype);
        
        $paginator = new Paginator($paginator_adapter);
        
        return $paginator;
    }
}