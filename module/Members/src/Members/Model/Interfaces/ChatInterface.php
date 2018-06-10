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
     * @param string $who
     * @param string $message
     * @throws ChatException
     * @return ChatInterface
     */
    public function sendMessage($who, $message);
    
    
    /**
     * Ends a chat session
     * @param string $who
     * @throws ChatException
     * @return bool
     */
    public function endChat($who);
    
    
    /**
     * Sends a reply to the user
     * @param string $who
     * @param string $message
     * @throws ChatException
     * @return ChatInterface
     */
    public function respondTo($who, $message);
}