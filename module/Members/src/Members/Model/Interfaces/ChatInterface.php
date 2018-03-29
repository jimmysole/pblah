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
     * @param string $message
     * @param array $emojis
     * @throws ChatException
     * @return $this
     */
    public function sendMessage($message, array $emojis = array());
    
    
    /**
     * Ends a chat session
     * @throws ChatException
     * @return bool
     */
    public function endChat();
}