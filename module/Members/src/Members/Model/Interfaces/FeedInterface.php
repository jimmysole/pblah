<?php

namespace Members\Model\Interfaces;


interface FeedInterface
{
    /**
     * Gets the user's friend(s) statuses
     * 
     * @throws FeedException
     * @return array|string
     */
    public function listFriendsStatus();
    
  
    /**
     * Gets the current user's status
     * 
     * @throws FeedException
     * @return array|string
     */
    public function listIndividualStatus();
}