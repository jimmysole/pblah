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
     * Ends a chat session
     * @param string $who
     * @throws ChatException
     * @return bool
     */
    public function endChat($who);
    
    /**
     * Handles the chat functionality
     * @param string $function
     * @param string $state
     * @param string $file
     * @param array $details
     * @throws ChatException
     * @return bool
     */
    public function processChat($function, $state, $file, array $details = array());
}