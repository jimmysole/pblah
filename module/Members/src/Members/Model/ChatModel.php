<?php

namespace Members\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;

use Members\Model\Interfaces\ChatInterface;
use Members\Model\Exceptions\ChatException;



class ChatModel implements ChatInterface
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
     * @var string
     */
    public $who;
    
    
    /**
     * @var array
     */
    public $message;
    
    
    /**
     * @var Sql
     */
    public $sql;
    
    
    /**
     * @var Select
     */
    public $select;
     
    
    
    /**
     * Constructor method for ChatModel class
     * @param TableGateway $gateway
     * @param unknown $user
     */
    public function __construct(TableGateway $gateway, $user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        $this->user = $user;
        
        $this->sql = new Sql($this->gateway->getAdapter());
        
        $this->select = new Select();
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ChatInterface::startChat()
     */
    public function startChat($who)
    {
        $this->who = !empty($who) ? $who : null;
        
        if (null !== $this->who) {
            // fetch the user id in the friends_online table
            $user = new Select('members');
            
            $select = $user->columns(array('id'))
            ->where(array('username' => $this->who));
            
            $query = $this->sql->getAdapter()->query(
                $this->sql->buildSqlString($select),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if ($query->count() > 0) {
                foreach ($query as $row) {
                    $id = $row['id'];
                }
                
                $online = new Select('friends_online');
                
                $select->columns(array('user_id'))
                ->where(array('user_id' => $id));
                
                $query = $this->sql->getAdapter()->query(
                    $this->sql->buildSqlString($select),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if ($query->count() > 0) {
                    // friend is online 
                    // start the chat session
                    // by inserting who, from, and the chat_date in the database table chat
                    // messages will be empty until messages are sent
                    $insert_data = array(
                        'who'       => $this->who,
                        'from'      => $this->getUserInfo()['username'],
                        'messages'  => '',
                        'chat_date' => time(),
                    );
                    
                    $insert = $this->gateway->insert($insert_data);
                    
                    if ($insert > 0) {
                        return $this;
                    } else {
                        throw new ChatException("Error starting the chat session, please try again.");
                    }
                } else {
                    throw new ChatException("Friend is not online.");
                }
            } else {
                throw new ChatException("Member was not found.");
            }
        } else {
            throw new ChatException("To send a message, the chat receipent must contain a valid user.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ChatInterface::sendMessage()
     */
    public function sendMessage(array $message)
    {
        $this->message = !count($message) > 0 ? function() use ($message) { return implode("\r\r", $message); } : null;
        
        // update the message field in the chat field
        $update_data = array(
            'messages' => $this->message,
        );
        
        $update = $this->gateway->update($update_data, array('who' => $this->who, 'from' => $this->getUserInfo()['username']));
        
        if ($update > 0) {
            return $this;
        } else {
            throw new ChatException("Error sending your message, please try again.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ChatInterface::endChat()
     */
    public function endChat()
    {
        $update_data = array(
            'chat_end_date' => time()
        );
        
        $update = $this->gateway->update($update_data, array('who' => $this->who, 'from' => $this->getUserInfo()['username']));
        
        if ($update > 0) {
            return true;
        } else {
            throw new ChatException("Error ending your chat session.");
        }
    }
    
    
    
    /**
     * Gets the logged in user info
     * 
     * @return array|boolean
     */
    public function getUserInfo()
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