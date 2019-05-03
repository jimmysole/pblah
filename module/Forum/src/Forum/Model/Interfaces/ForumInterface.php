<?php

namespace Forum\Model\Interfaces;



interface ForumInterface
{
    
    /**
     * Gets all the messages for the forum
     * @return array
     * @throws ForumException
     */
    public function getMessages();
    
    
    
}