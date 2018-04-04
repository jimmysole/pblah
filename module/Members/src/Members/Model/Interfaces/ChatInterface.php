<?php

namespace Members\Model\Interfaces;


interface ChatInterface
{
    /**
     * Starts a chat with a user
     * @param string $who
     * @throws ChatException
     * @return ChatInterface
     */
    public function startChat($who);
    
    
    /**
     * Sends a message to the user
     * @param array $message
     * @throws ChatException
     * @return ChatInterface
     */
    public function sendMessage(array $message);
    
    
    /**
     * Ends a chat session
     * @throws ChatException
     * @return bool
     */
    public function endChat();
}