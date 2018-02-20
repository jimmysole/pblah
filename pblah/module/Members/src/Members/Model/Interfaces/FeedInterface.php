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
     * Hides a friends status from showing up 
     * 
     * 
     * @param int $friend_id
     * @throws FeedException
     * @return bool
     */
    public function hideFriendsStatus($friend_id);
}