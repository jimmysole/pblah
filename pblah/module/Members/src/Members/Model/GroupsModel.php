<?php
namespace Members\Model;


use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Update;
use Zend\Db\Adapter\Adapter;

use Members\Model\Filters\CreateGroup;
use Members\Model\Filters\JoinGroup;


use Members\Model\Interfaces\GroupsInterface;
use Members\Model\Interfaces\GroupMembersOnlineInterface;
use Members\Model\Exceptions\GroupsException;


class GroupsModel implements GroupsInterface, GroupMembersOnlineInterface
{

     /**
     * @var TableGateway
     */
    public $gateway;

    /**
     * @var string
     */
    public $user;
    
    /**
     * @var Select
     */
    public $select;
    
    /**
     * @var Insert
     */
    public $insert;
    
    /**
     * @var Delete
     */
    public $delete;
    
    /**
     * @var Update
     */
    public $update;
    
    /**
     * @var Sql
     */
    public $sql;
    
    /**
     * @var ConnectionInterface
     */
    public $connection;

    
    /**
     * Constructor method for GroupsModel class
     *
     * @param TableGateway $gateway            
     * @param string $group_user            
     */
    public function __construct(TableGateway $gateway, $group_user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        $this->select = new Select();
        
        $this->insert = new Insert();
        
        $this->delete = new Delete();
        
        $this->update = new Update();
        
        $this->sql = new Sql($this->gateway->getAdapter());
        
        $this->user =  $group_user;
        
        $this->connection = $this->sql->getAdapter()->getDriver()->getConnection();
    }
    
    /**
     * 
     * Gets user id
     * 
     * @return ResultSet|boolean
     */
    public function getUserId()
    {
        $this->select->columns(array('*'))
        ->from('members')
        ->where(array('username' => $this->user));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
            );
        
        if ($query->count() > 0) {
            foreach ($query as $result) {
                $row = $result;
            }
            
            return $row;
        }
        
        return false;
    }

    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\GroupsInterface::getAllGroups()
     */
    public function getAllGroups()
    {
        $query = $this->connection->execute("SELECT id, group name, group_creator, group_created_date FROM groups
            WHERE id NOT IN (SELECT group_id FROM group_members WHERE member_id = " . $this->getUserId()['id'] . ")");
        
        if ($query->count() > 0) {
            $all_group_holders = array();
       
        
            foreach ($query as $key => $groups) {
                $all_group_holders[$key] = $groups;
            }
        
            return $all_group_holders;
        } else {
            throw new GroupsException("No groups were found.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\GroupsInterface::getGroupsIndex()
     */
    public function getGroupsIndex()
    {
        $query = $this->connection->execute("SELECT groups.id AS group_id, groups.group_name AS g_name FROM group_members
            INNER JOIN members ON group_members.member_id = members.id
            INNER JOIN groups ON group_members.group_id = groups.id
            WHERE members.id = " . $this->getUserId()['id'] . " ORDER BY groups.id LIMIT 5");
        
        if ($query->count() > 0) {
            $group_holder = array();
            
            foreach ($query as $key => $value) {
                $group_holder[$key] = $value;
            }
            
            return $group_holder;
        } else {
            throw new GroupsException("You aren't a part of any groups.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\GroupsInterface::getMoreGroups()
     */
    public function getMoreGroups()
    {
        $query = $this->connection->execute("SELECT groups.id AS group_id, groups.group_name AS g_name FROM members
            INNER JOIN members ON group_members.member_id = members.id
            INNER JOIN groups ON group_members.group_id = groups.id
            WHERE members.id = " . $this->getUserId['id'] . " ORDER BY groups.id");
        
        if ($query->count() > 0) {
            $group_holder = array();
            
            foreach ($query as $key => $value) {
                $group_holder[$key] = $value;
            }
            
            return $group_holder;
        } else {
            throw new GroupsException("No more groups were found.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\GroupsInterface::getGroups()
     */
    public function getGroups()
    {
        $query = $this->connection->execute("SELECT groups.id AS group_id, groups.group_name AS g_name FROM group_members
                                       INNER JOIN members ON group_members.member_id = members.id
                                       INNER JOIN groups ON group_members.group_id = groups.id
                                       WHERE members.id = " . $this->getUserId()['id'] . " ORDER BY groups.id");
        
        if (count($query) > 0) {
            $group_name = array();
            $group_id   = array();
            
            foreach ($query as $value) {
                // list the group names and ids
                $group_name[] = $value['g_name'];
                $group_id[]   = $value['group_id'];
            }
            
            return array('group_name' => $group_name, 'group_id' => $group_id);
        } else {
            throw new GroupsException("You aren't a part of any groups!");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\GroupsInterface::getGroupIds()
     */
    public function getGroupIds()
    {
        $this->select->columns(array('id'))
        ->from('groups')
        ->where("id IS NOT NULL OR id != ''");
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            $groups = array();
            
            foreach ($query as $group_ids) {
                $groups[] = $group_ids['id'];
            }
            
            return $groups;
        } else {
            throw new GroupsException("Could not find any groups.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\GroupsInterface::leaveGroup()
     */
    public function leaveGroup($group_id)
    {
        if (empty($group_id)) {
            throw new GroupsException("No group was selected to leave.");
        }
        
        // get the group based on $group_id
        $query = $this->connection->execute("SELECT groups.group_name, groups.id AS group_id FROM group_members
            INNER JOIN members on group_members.member_id = member.id
            INNER JOIN groups ON group_members.group_id = groups.id
            WHERE members.id = " . $this->getUserId()['id'] . " AND groups.id = " . $group_id);
        
        if ($query->count() > 0) {
            // go ahead and delete the user from the group
            $this->delete->from('group_members')
            ->where(array('member_id' => $this->getUserId()['id'], 'group_id' => $group_id));
            
            $query = $this->sql->getAdapter()->query(
                $this->sql->buildSqlString($this->delete),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if ($query->count() > 0) {
                // remove from group members online table
                $this->delete->from('group_members_online')
                ->where(array('member_id' => $this->getUserId()['id'], 'group_id' => $group_id));
                
                $query = $this->sql->getAdapter()->query(
                    $this->sql->buildSqlString($this->delete),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                // delete from group admins table as well (if found)
                $this->delete->from('group_admins')
                ->where(array('user_id' => $this->getUserId()['id'], 'group_id' => $group_id));
                
                $exec = $this->sql->getAdapter()->query(
                    $this->sql->buildSqlString($this->delete),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if ($exec->count() < 0) {
                    // not an admin
                    return true;
                } else if ($exec->count() > 0 && $query->count() > 0) {
                    return true; 
                } else {
                    throw new GroupsException("An error occurred while attempting to process your request to leave the group specified, please try again.");
                }
            } else {
                throw new GroupsException("An error occurred while attempting to process your request to leave the group specified, please try again.");
            }
        } else {
            throw new GroupsException("You don't belong to the specified group.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\GroupsInterface::joinGroup()
     */
    public function joinGroup($group_id, JoinGroup $data)
    {
        if (empty($group_id)) {
            throw new GroupsException("Could not locate the group specified.");
        }
        
        
        // assign data to array
        // for insert
        $data_holder = array(
            'group_id'  => $group_id,
            'member_id' => $this->getUserId()['id'],
            'user_data' => array($data->first_name, $data->last_name, $data->age, $data->message),
        );
        
        $this->select->columns(array('username'))
        ->from('members')
        ->where(array('id' => $data_holder['member_id']));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            $members = array();
            
            foreach ($query as $value) {
                $members[] = $value['username'];
            }
        }
        
        
        // get the user ids of the group admins
        $this->select->columns(array('user_id'))
        ->from('group_admins')
        ->where(array('group_id' => $group_id));
        
        $admins_query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($admins_query->count() > 0) {
            // admins found
            $group_admins = array();
            
            foreach ($admins_query as $admins) {
                $group_admins[] = $admins['user_id'];
            }
            
            // locate the admin(s) now based on the ids just fetched
            $this->select->columns(array('username'))
            ->from('members')
            ->where(array('id' => array_values($group_admins)));
            
            $admins_from_members_query = $this->sql->getAdapter()->query(
                $this->sql->buildSqlString($this->select),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if ($admins_from_members_query->count() > 0) {
                $admin_users = array();
                
                foreach ($admins_from_members_query as $values) {
                    $admin_users[] = $values['username'];
                }
                
                $this->insert->into('private_messages')
                ->columns(array('to', 'from', 'message', 'date_received', 'active'))
                ->values(array('to' => implode(", ", array_values($admin_users)), 'from' => $members[0],
                    'message' => 'A user has requested to join your group', 'date_received' => date('Y-m-d H:i:s'), 'active' => 1
                ));
                
                $exec = $this->sql->getAdapter()->query(
                    $this->sql->buildSqlString($this->insert),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if ($exec->count() > 0) {
                    // insert the join data into the group_join_requests table
                    $this->insert->into('group_join_requests')
                    ->columns(array('group_id', 'member_id', 'data'))
                    ->values(array('group_id' => $data_holder['group_id'], 'member_id' => $data_holder['member_id'],
                        'user_data' => implode(", ", array_values($data_holder['user_data']))
                    ));
                    
                    $query = $this->sql->getAdapter()->query(
                        $this->sql->buildSqlString($this->insert),
                        Adapter::QUERY_MODE_EXECUTE
                    );
                    
                    if ($query->count() > 0) {
                        return true;
                    } else {
                        throw new GroupsException("An error has occurred while processing the request to join the group, please try again.");
                    }
                } else {
                    throw new GroupsException("Error sending your request to join, please try again.");
                }
            } else {
                throw new GroupsException("Error locationg admin(s), perhaps they are no longer members of the group.");
            }
        } else {
            // no admins
            throw new GroupsException("The group has no administrators, request to join cancelled.");
        }
    }
    
    
    public function createGroup(CreateGroup $group)
    {
        
    }
    
    
    public function insertIntoGroupMembersOnlineFromCreateGroupId($id)
    {
        
    }
    
    
    public function getGroupMembersOnline($group_id = null)
    {
        
    }
}