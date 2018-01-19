<?php

namespace Members\Model\Interfaces;

use Members\Model\Classes\Events;


interface GroupAdminInterface
{
    /**
     * @param int $group_id
     * @param array $choices
     * @throws GroupsException
     * @return self
     */
    public function manageGroupUsers($group_id, array $member_ids, array $choices);
    
    
    /**
     * @param int $group_id
     * @throws GroupsException
     * @return self
     */
    public function manageGroup($group_id);
    
    
    /**
     * @param array $group_id
     * @throws GroupsException
     * @return self
     */
    public function manageGroups(array $group_id);
    
    
    /**
     * @param int $group_id
     * @param Events $event_id
     * @throws GroupsException
     * @return self
     */
    public function manageGroupEvents($group_id, Events $event_id);
}