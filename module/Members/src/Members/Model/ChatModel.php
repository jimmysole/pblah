<?php

namespace Members\Model;

use Zend\Db\TableGateway\TableGateway;

use Members\Model\Interfaces\ChatInterface;



class ChatModel implements ChatInterface
{
    public function __construct(TableGateway $gateway, $user)
    {
        
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ChatInterface::startChat()
     */
    public function startChat($who)
    {
        
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ChatInterface::sendMessage()
     */
    public function sendMessage($message, array $emojis = array())
    {
        
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\ChatInterface::endChat()
     */
    public function endChat()
    {
        
    }
}