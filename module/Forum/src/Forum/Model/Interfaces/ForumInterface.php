<?php

namespace Forum\Model\Interfaces;



interface ForumInterface
{
    
    /**
     * Gets all the messages for the forum
     * @return array
     * @throws ForumException
     */
    public function getMessages($board_id);
    
    
    /**
     * Gets all the topics for the forum
     * @return array
     * @throws ForumException
     */
    public function getTopics();
    
    
    /**
     * Gets the number of replies for a topic/message
     * @return int
     * @throws ForumException
     */
    public function getNumOfReplies();
    
    
    /**
     * Gets the text of the replies for a topic/message
     * @return array
     * @throws ForumException
     */
    public function getRepliesText();
}