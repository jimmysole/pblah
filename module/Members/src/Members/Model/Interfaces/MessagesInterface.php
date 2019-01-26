<?php

namespace Members\Model\Interfaces;


interface MessagesInterface 
{
    /**
     * Sends a private message to a user from a user
     * 
     * @param string $to
     * @param array $message
     * @throws MessagesException
     * @return boolean
     */
    public function sendMessage($to, array $message);
    
    
    /**
     * Gets messages for the user
     * @throws MessagesException
     * @return array
     */
    public function getMessages();
}