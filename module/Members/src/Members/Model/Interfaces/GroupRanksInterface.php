<?php

namespace Members\Model\Interfaces;


interface GroupRanksInterface
{
    
    /**
     * Adds a rank for a user to the group
     * 
     * @param int $rank
     * @param int $user_id
     * @param int $group_id
     * @throws GroupRankException::
     * @return boolean
     */
    public function addRank($rank, $user_id, $group_id);
    
    
    /**
     * Sets a rank for a user
     * 
     * @param int $rank
     * @param int $user_id
     * @param int $group_id
     * @throws GroupRankException
     * @return boolean
     */
    public function setRank($rank, $user_id, $group_id);
    
    
    /**
     * Removes a rank for a user
     * 
     * @param int $user_id
     * @param int $group_id
     * @throws GroupRankException
     * @return boolean
     */
    public function deleteRank($user_id, $group_id);
    
    
    /**
     * Gets the group's ranks
     * 
     * @param int $group_id
     * @throws GroupRankException
     * @return array
     */
    public function getRanks($group_id);
}