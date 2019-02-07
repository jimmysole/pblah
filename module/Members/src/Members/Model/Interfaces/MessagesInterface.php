<?php

namespace Members\Model\Interfaces;


interface MessagesInterface 
{
    /**
     * Sends a private message to a user from a user
     * 
     * @param string $to
     * @param string $subject
     * @param string $message
     * @throws MessagesException
     * @return boolean
     */
    public function sendMessage($to, $subject, $message);
    
}