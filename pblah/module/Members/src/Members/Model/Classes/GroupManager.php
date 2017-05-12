<?php
namespace Members\Model\Classes;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Update;
use Zend\Db\Adapter\Adapter;
use Members\Model\Classes\Interfaces\GroupAdmin;
use Members\Model\Classes\Exceptions\GroupsException;

class GroupManager extends Groups implements GroupAdmin
{

    /**
     *
     * @var array
     */
    public $member_ids = array();

    /**
     *
     * @var array
     */
    public $choice = array();

    /**
     *
     * Manages group users
     * 
     * @param int $group_id            
     * @param array $member_ids            
     * @param array $choices            
     * @param array $ranks            
     * @throws GroupsException
     * @return self
     */
    public function manageGroupUsers($group_id, array $member_ids, array $choices, array $ranks = array())
    {
        if (empty($group_id)) {
            throw new GroupsException("Invalid group id passed, please check it and try again.");
        }
        
        // check to see if the length of the member_id and choices arrays
        // are greater than zero
        // if so, pass to $this->member_ids and $this->choices
        // if not, throw GroupsException
        if (count($member_ids) > 0 && count($choices) > 0) {
            foreach ($member_ids as $key => $value) {
                $this->member_ids[$key] = $value;
            }
            
            foreach ($choices as $key => $value) {
                $this->choice[$key] = $value;
            }
            
            $connection = parent::$sql->getAdapter()
                ->getDriver()
                ->getConnection();
            
            // determine which choices were passed
            if ($this->choice['choice'] == 'remove_members') {
                // remove member from group
                $select = new Select('group_members');
                
                $select->columns(array(
                    '*'
                ))->where("group_id = " . intval($group_id) . " AND member_id IN (" . implode(",", $this->member_ids['member_id']) . ")");
                
                $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                
                if ($query->count() > 0) {
                    $member_ids_holder = array();
                    
                    foreach ($query as $value) {
                        $member_ids_holder = array(
                            'groups' => $value['group_id'],
                            'members' => $value['member_id']
                        );
                    }
                    
                    $exec = $connection->execute("DELETE FROM group_members WHERE group_id IN (SELECT id from groups
                         WHERE id IN (" . implode(", ", $member_ids_holder['groups']) . ")
                         AND member_id IN (" . implode(", ", $member_ids_holder['members']) . ")");
                    
                    if (count($exec) > 0) {
                        // delete from group_members_online table
                        $delete = $connection->execute("DELETE FROM group_members_online WHERE member_id IN (" . implode(", ", $member_ids_holder['members']) . ")
                             AND group_id IN (" . implode(", ", $member_ids_holder['groups']) . ")");
                        
                        if (count($delete) > 0) {
                            return $this;
                        } else {
                            throw new GroupsException("Database error occurred, please try again.");
                        }
                    } else {
                        throw new GroupsException("Error removing user(s) from the specified groups, please try again.");
                    }
                }
            } else if ($this->choice['choice'] == 'remove_member') {
                $exec = $connection->execute("DELETE FROM group_members WHERE group_id = " . intval($group_id) . "
                   AND member_id = " . array_values($member_ids['member_id'])[0] . " LIMIT 1");
                
                if (count($exec) > 0) {
                    // delete from group_members_table
                    $exec = $connection->execute("DELETE FROM group_members_online WHERE member_id = " . array_values($member_ids['member_id'][0]) . " AND group_id = " . intval($group_id));
                    
                    if ($exec > 0) {
                        return $this;
                    } else {
                        throw new GroupsException("Database error occurred, please try again.");
                    }
                } else {
                    throw new GroupsException("Error removing member from the group, please try again.");
                }
            } else if ($this->choice['choice'] == 'set_rank') {
                if (count($ranks) > 0) {
                    // get the rank passed
                    if ($ranks['rank_passed'] == 1) {
                        // group leader rank passed
                        // set the user to group leader
                        $select = new Select('group_ranks');
                        
                        $select->columns(array(
                            '*'
                        ))->where('group_id = ' . $group_id . ' AND user_id IN (' . implode(", ", $member_ids['member_id']) . ')');
                        
                        $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                        
                        if (count($query) > 0) {
                            $rank_holder = array();
                            
                            foreach ($query as $value) {
                                $rank_holder[] = array(
                                    'group_id' => $value['group_id'],
                                    'user_id' => $value['user_id'],
                                    'user_rank' => $value['rank']
                                );
                            }
                            
                            // update the user rank now
                            $update = new Update('group_ranks');
                            
                            $update->set(array(
                                'user_rank' => GroupRank::$ranks['group_leader']
                            ))->where(array(
                                'group_id' => $rank_holder['group_id'],
                                'user_id' => $rank_holder['user_id']
                            ));
                            
                            $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($update), Adapter::QUERY_MODE_EXECUTE);
                            
                            if (count($query) > 0) {
                                return $this;
                            } else {
                                throw new GroupsException("Error updating the rank for the user, please try again.");
                            }
                        } else {
                            throw new GroupsException("User was not found in the group ranks table.");
                        }
                    } else if ($ranks['rank_passed'] == 2) {
                        // group asst leader rank passed
                        // set the user to asst leader
                        $select = new Select('group_ranks');
                        
                        $select->columns(array(
                            '*'
                        ))->where('group_id = ' . $group_id . ' AND user_id IN (' . implode(", ", $member_ids['member_id']) . ')');
                        
                        $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                        
                        if (count($query) > 0) {
                            $rank_holder = array();
                            
                            foreach ($query as $value) {
                                $rank_holder[] = array(
                                    'group_id' => $value['group_id'],
                                    'user_id' => $value['user_id'],
                                    'user_rank' => $value['rank']
                                );
                            }
                            
                            // update the user rank now
                            $update = new Update('group_ranks');
                            
                            $update->set(array(
                                'user_rank' => GroupRank::$ranks['group_asst']
                            ))->where(array(
                                'group_id' => $rank_holder['group_id'],
                                'user_id' => $rank_holder['user_id']
                            ));
                            
                            $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($update), Adapter::QUERY_MODE_EXECUTE);
                            
                            if (count($query) > 0) {
                                return $this;
                            } else {
                                throw new GroupsException("Error updating the rank for the user, please try again.");
                            }
                        } else {
                            throw new GroupsException("User was not found in the group ranks table.");
                        }
                    } else if ($ranks['rank_passed'] == 3) {
                        // group member rank passed
                        // set the user to group member
                        $select = new Select('group_ranks');
                        
                        $select->columns(array(
                            '*'
                        ))->where('group_id = ' . $group_id . ' AND user_id IN (' . implode(", ", $member_ids['member_id']) . ')');
                        
                        $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                        
                        if (count($query) > 0) {
                            $rank_holder = array();
                            
                            foreach ($query as $value) {
                                $rank_holder[] = array(
                                    'group_id' => $value['group_id'],
                                    'user_id' => $value['user_id'],
                                    'user_rank' => $value['rank']
                                );
                            }
                            
                            // update the user rank now
                            $update = new Update('group_ranks');
                            
                            $update->set(array(
                                'user_rank' => GroupRank::$ranks['group_member']
                            ))->where(array(
                                'group_id' => $rank_holder['group_id'],
                                'user_id' => $rank_holder['user_id']
                            ));
                            
                            $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($update), Adapter::QUERY_MODE_EXECUTE);
                            
                            if (count($query) > 0) {
                                return $this;
                            } else {
                                throw new GroupsException("Error updating the rank for the user, please try again.");
                            }
                        } else {
                            throw new GroupsException("User was not found in the group ranks table.");
                        }
                    } else if ($ranks['rank_passed'] == 4) {
                        // group invitee rank passed
                        // set the user to group invitee
                        $select = new Select('group_ranks');
                        
                        $select->columns(array(
                            '*'
                        ))->where('group_id = ' . $group_id . ' AND user_id IN (' . implode(", ", $member_ids['member_id']) . ')');
                        
                        $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
                        
                        if (count($query) > 0) {
                            $rank_holder = array();
                            
                            foreach ($query as $value) {
                                $rank_holder[] = array(
                                    'group_id' => $value['group_id'],
                                    'user_id' => $value['user_id'],
                                    'user_rank' => $value['rank']
                                );
                            }
                            
                            // update the user rank now
                            $update = new Update('group_ranks');
                            
                            $update->set(array(
                                'user_rank' => GroupRank::$ranks['group_invitees']
                            ))->where(array(
                                'group_id' => $rank_holder['group_id'],
                                'user_id' => $rank_holder['user_id']
                            ));
                            
                            $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($update), Adapter::QUERY_MODE_EXECUTE);
                            
                            if (count($query) > 0) {
                                return $this;
                            } else {
                                throw new GroupsException("Error updating the rank for the user, please try again.");
                            }
                        } else {
                            throw new GroupsException("User was not found in the group ranks table.");
                        }
                    } else {
                        throw new GroupsException("Invalid rank passed.");
                    }
                } else {
                    throw new GroupsException("No rank was supplied, please fix this and try again.");
                }
            } else if ($this->choice['choice'] == 'delete_rank') {
                
            } else if ($this->choice['choice'] == 'ban_user') {
                
            } else if ($this->choice['choice'] == 'suspend_user') {
                
            }
        } else {
            throw new GroupsException("No values were found for member ids and/or choices, please fix this and try again.");
        }
    }

    /**
     * Manage a single group
     * 
     * @param int $group_id            
     * @throws GroupsException
     * @return self
     */
    public function manageGroup($group_id)
    {}

    /**
     * Manages multiple groups
     * 
     * @param array $group_id            
     * @throws GroupsException
     * @return self
     */
    public function manageGroups(array $group_id)
    {}

    /**
     * Manages a groups events
     * 
     * @param int $group_id            
     * @param Events $event_id            
     * @throws GroupsException
     * @return self
     */
    public function manageGroupEvents($group_id, Events $event_id)
    {}
}