<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\MessagesException;


class Messages extends Friends
{
    public $to;
    
    public $message = array();
    
    
    public function __construct()
    {
       
    }
    
    
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
        
    }
}
