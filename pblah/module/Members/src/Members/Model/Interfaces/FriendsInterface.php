<?php

namespace Members\Model\Interfaces;


use Members\Model\Exceptions\FriendsException;

interface FriendsInterface
{
    /**
     * Browses through the user's frined list with optional criteria
     * 
     * @param null|string $criteria
     * @param array $criteria_params
     * @throws FriendsException
     * @return array
     */
    public function browseFriends($criteria = null, array $criteria_params = array());
    
    
    /**
     * Sends a friend add request
     * 
     * @param int $request_id
     * @param int $friend_id
     * @throws FriendsException
     * @return boolean
     */
    public function sendAddRequest($friend_id);
    
    
    /**
     * Cancels a pending friend request
     * 
     * @param int $friend_id
     * @throws FriendsException
     * @return boolean
     */
    public function cancelFriendRequest($friend_id);
    
    
    /**
     * Approves a pending friend request
     * 
     * @param int $friend_id
     * @throws FriendsException
     * @return boolean
     */
    public function approveFriendRequest($friend_id);
    
    
    /**
     * Denies a pending friend request
     * 
     * @param int $friend_id
     * @throws FriendsException
     * @return boolean
     */
    public function denyFriendRequest($friend_id);
    
    
    /**
     * Start following a friend
     * 
     * @throws FriendsException
     * @return boolean
     */
    public function followFriend();
    
    
    /**
     * Unfollow a friend
     * 
     * @throws FriendsException
     * @return boolean
     */
    public function unfollowFriend();
    
    
    /**
     * Sends a message to a friend
     * 
     * @param MessagesInterface $messages
     * @param string $to
     * @param array $message
     * @throws FriendsException
     * @return void
     */
    public function messageFriend(MessagesInterface $messages, $to, array $message);
    
    
    /**
     * Gets the friends online for the user
     * 
     * @throws FriendsException
     * @return array
     */
    public function getFriendsOnline();
}