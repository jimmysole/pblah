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
                
                $online->columns(array('user_id'))
                ->where(array('user_id' => $id));
                
                $query = $this->sql->getAdapter()->query(
                    $this->sql->buildSqlString($online),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                
                if ($query->count() > 0) {
                    // friend is online 
                    // start the chat session
                    // by inserting who, from, and the chat_date in the database table chat
                    // messages will be empty until messages are sent
                    // unless a chat session already exists
                    // in the case, update the date only
                    $select_from_chat = new Select('chat');
                    
                    $select_from_chat->columns(array('id'))
                    ->where(array('who' => $this->who, 'from' => $this->getUserInfo()['username']))
                    ->limit(1);
                    
                    $query = $this->sql->getAdapter()->query(
                        $this->sql->buildSqlString($select_from_chat),
                        Adapter::QUERY_MODE_EXECUTE
                    );
                    
                    if ($query->count() > 0) {
                        foreach ($query as $chat_id) {
                            $get_id = $chat_id;
                        }
                        
                        // update the date only
                        $update_data = array(
                            'chat_date' => date('Y-m-d H:i:s')                          
                        );
                        
                        $update = $this->gateway->update($update_data, array('id' => $get_id));
                        
                        if ($update > 0) {
                            return $this;
                        } else {
                            throw new ChatException("Error starting the chat session, please try again.");
                        }
                    } else {
                        // no records found
                        // start a new chat session
                        $insert_data = array(
                            'who'           => $this->who,
                            'from'          => $this->getUserInfo()['username'],
                            'from_message'  => '',
                            'chat_date'     => date('Y-m-d H:i:s'),
                        );
                        
                        $insert = $this->gateway->insert($insert_data);
                        
                        if ($insert > 0) {
                            return $this;
                        } else {
                            throw new ChatException("Error starting the chat session, please try again.");
                        }
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
    public function sendMessage($who, $message)
    {
        $to            = !empty($who)     ? $who     : null;
        $this->message = !empty($message) ? $message : null;
        
        // update the message field in the chat field
        $update_data = array(
            'from_message' => $this->message,
        );
        
        $update = $this->gateway->update($update_data, array('who' => $to, 'from' => $this->getUserInfo()['username']));
        
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
    public function endChat($who)
    {
        $update_data = array(
            'chat_end_date' => date('Y-m-d H:i:s')
        );
        
        $update = $this->gateway->update($update_data, array('who' => $who, 'from' => $this->getUserInfo()['username']));
        
        if ($update > 0) {
            return true;
        } else {
            throw new ChatException("Error ending your chat session.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ChatInterface::respondTo()
     */
    public function respondTo($to, $message)
    {
        $send_to = !empty($to) ? $to : null;
        
        $send_message = !empty($message) ? $message : null;
        
        $update_data = array(
            'who_message' => $send_message,
        );
        
        $update = $this->gateway->update($update_data, array('from' => $send_to));
        
        if ($update > 0) {
            return true;
        } else {
            throw new ChatException("Error sending your reply.");
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