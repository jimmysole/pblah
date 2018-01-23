<?php

namespace Members\Model\Interfaces;

use Members\Model\Filters\CreateGroup;


interface GroupsInterface
{
    
    /**
     * Gets all the groups that the user is not a part of
     * 
     * @throws GroupsException
     * @return string[]
     */
    public function getAllGroups();
    
    
    /**
     * Gets the groups the user is a part of for displaying on group home page
     * 
     * @throws GroupsException
     * @return array
     */
    public function getGroupsIndex();
    
    
    /**
     * Shows all of the groups (if the user clicks on view more)
     * 
     * @throws GroupsException
     * @return array
     */
    public function getMoreGroups();
    
    
    /**
     * Gets the list of the groups that a user is a part of to display on member home page
     * 
     * @throws GroupsException
     * @return array
     */
    public function getGroups();
    
    
    /**
     * Get the group(s) id
     * 
     * @throws GroupsException
     * @return array
     */
    public function getGroupIds();
    
    
    /**
     * Lets the user leave a group(s)
     * 
     * @param int $group_id
     * @throws GroupsException
     * @return boolean
     */
    public function leaveGroup($group_id);
    
    
    /**
     * Lets a user send a request to join a group
     * 
     * @param int $group_id
     * @param array $data
     */
    public function joinGroup($group_id, array $data);
    
    
    /**
     * Allows the user to create a group
     * 
     * @param CreateGroup $group
     * @throws GroupsException
     * @return boolean
     */
    public function createGroup(CreateGroup $group);
    
    
    /**
     * Inserts the user into the group members online table for the newly create group
     * 
     * @param int $id
     * @throws GroupsException
     * @return boolean
     */
    public function insertIntoGroupMembersOnline($id);
}