<?php

namespace Members\Model\Interfaces;


use Members\Model\Exceptions\GroupsException;

interface GroupManagerInterface
{
    
    /**
     * Manages group users
     * 
     * @param int $group_id
     * @param array $member_ids
     * @param array $choices
     * @param array $ranks
     * @throws GroupsException
     * @return GroupManagerInterface
     */
    public function manageGroupUsers($group_id, array $member_ids, array $choices, array $ranks = array());

    
    /**
     * Manage a single group
     * 
     * @param int $group_id
     * @throws GroupsException
     * @return GroupManagerInterface
     */
    public function manageGroup($group_id);
    
    
    /**
     * Manages multiple groups
     * 
     * @param array $group_id
     * @throws GroupsException
     * @return GroupManagerInterface
     */
    public function manageGroups(array $group_id);
    
    
    /**
     * Manages a group events
     * 
     * @param int $group_id
     * @param int $event_id
     * @throws GroupsException
     * @return GroupManagerInterface
     */
    public function manageGroupEvents($group_id, $event_id);
}