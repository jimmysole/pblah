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
     * @var string
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
     * @var array
     */
    public $chat_logger = [];
    
    
    /**
     * @var string
     */
    private $state;
    
    
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
        if (null !== $who) {
            $this->who = $who;
            
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
                            $get_id = $chat_id['id'];
                        }
                        
                        // update the date only
                        $update_data = array(
                            'chat_date' => date('Y-m-d H:i:s'),
                            'active' => 1,
                        );
                        
                        $update = $this->gateway->update($update_data, array('id' => $get_id));
                        
                        if ($update > 0) {
                            $create_chat_file = @fopen("./data/chat/" . $this->who . '-' . $this->getUserInfo()['username'] . '.txt', 'a');
                            
                            return $this;
                        } else {
                            throw new ChatException("Error starting the chat session, please try again.");
                        }
                    } else {
                        // no records found
                        // start a new chat session
                        // create the file
                        $insert_data = array(
                            'who'           => $this->who,
                            'from'          => $this->getUserInfo()['username'],
                            'chat_date'     => date('Y-m-d H:i:s'),
                            'active'        => 1,
                        );
                        
                        $create_chat_file = @fopen("./data/chat/" . $this->who . '-' . $this->getUserInfo()['username'] . '.txt', 'a');
                        
                        if (is_resource($create_chat_file)) {
                            // chat file created
                            // move on to inserting
                            $insert = $this->gateway->insert($insert_data);
                            
                            if ($insert > 0) {
                                return $this;
                            } else {
                                throw new ChatException("Error starting the chat session, please try again.");
                            }
                        } else {
                            throw new ChatException("Error creating the chat file, please try again.");
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
    
    
    public function getState($file)
    {
        if (file_exists($file)) {
            $flines = file($file);
        }
        
        return count($flines);
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ChatInterface::processChat()
     */
    public function processChat($function, $state, $file, array $details = array())
    {
        if ($function == 'getState') {
            $this->chat_logger['state'] = $this->getState("./data/chat/" . $file);
        } else if ($function == 'update') {
            if (file_exists("./data/chat/" . $file)) {
                $flines = file("./data/chat/" . $file);
                
                if ($state == $this->getState("./data/chat/" . $file)) {
                    $this->chat_logger['state'] = $state;
                    $this->chat_logger['text']  = false;
                } else {
                    $text = [];
                        
                    $this->chat_logger['state'] = $state + count($flines) - $state;
                        
                    foreach ($flines as $num => $line) {
                        if ($num >= $state) {
                            $text[] = $line = str_replace("\n", "", $line);
                        }
                    }
                        
                    $this->chat_logger['text'] = $text;
                }
            } else {
                throw new ChatException("Error retrieving the updated status of the chat session.");
            }
        } else if ($function == 'send') {
            if (count($details) > 0) {
                if ($details['message'] != "\n") {
                    fwrite(fopen("./data/chat/" . $file, 'a'),
                        "<span class=\"w3-tag w3-padding w3-round-medium w3-theme-d2\">" . $this->getUserInfo()['username'] . "</span>" 
                        . " " . $details['message'] = str_replace("\n", " ", $details['message']) . "\n");
                }
            } else {
                throw new ChatException("Cannot send an empty message.");
            }
        } else {
            throw new ChatException("Invalid function given.");
        }
        
        echo json_encode($this->chat_logger);
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ChatInterface::endChat()
     */
    public function endChat($who)
    {
        $update_data = array(
            'chat_end_date' => date('Y-m-d H:i:s'),
            'active' => 0,
        );
        
        $update = $this->gateway->update($update_data, array('who' => $who, 'from' => $this->getUserInfo()['username']));
        
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
    
    
    /**
     * Clear up objects 
     */
    public function __destruct()
    {
        $this->who = null;
        $this->message = null;
    }
}