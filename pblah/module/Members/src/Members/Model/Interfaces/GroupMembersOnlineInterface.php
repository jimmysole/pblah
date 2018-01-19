<?php

namespace Members\Model\Interfaces;


interface GroupMembersOnlineInterface
{
    
    /**
     * Gets the group members that are online
     * 
     * @param int $group_id
     * @throws GroupMembersOnlineException
     * @return array[][]
     */
    public function getGroupMembersOnline($group_id = null);
}