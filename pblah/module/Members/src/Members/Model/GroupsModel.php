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
use Members\Model\Exceptions\GroupMembersOnlineException;


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
     * @var array
     */
    private $group_settings = array();
    
    /**
     * @var array
     */
    private $allowed_group_settings = array(
        'join_authorization', 'closed_to_public'
    );
    
    /**
     * @var array
     */
    private $group_members_id = array();
    
    /**
     * @var array
     */
    private $group_names = array();

    
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
        $query = $this->connection->execute("SELECT id, group_name, group_creator, group_created_date FROM groups
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
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\GroupsInterface::createGroup()
     */
    public function createGroup(CreateGroup $group)
    {
        if (empty($group->group_name)) {
            throw new GroupsException("You cannot leave your group name empty.");
        }
        
        // get the member username
        $this->select->columns(array('username'))
        ->from('members')
        ->where(array('id' => $this->getUserId()['id']));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            // username found
            $group_creator = array();
            
            foreach ($query as $value) {
                $group_creator[] = $value['username'];
            }
            
            // get the settings passed
            if ($group->group_settings == 1 || $group->group_settings2 == 1) {
                $group_settings = array($group->group_settings, $group->group_settings2);
                
                // combine $this->allowed_group_settings array and $group_settings as array
                // using $this->allowed_group_settings as the array keys and $group_settings as the value
                $set_group_settings = array_combine($this->allowed_group_settings, $group_settings);
                
                
                // first, create the group
                // then insert the settings by grapping the group id just created
                $this->insert->into('groups')
                ->columns(array('group_name', 'group_creator', 'group_created_data'))
                ->values(array('group_name' => $group->group_name, 'group_creator' => $group_creator[0],
                    'group_created_date' => date('Y-m-d H:i:s'), 'group_description' => $group->group_description));
                
                $query = $this->sql->getAdapter()->query(
                    $this->sql->buildSqlString($this->insert),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if ($query->count() > 0) {
                    // get the last id passed
                    $id = $this->sql->getAdapter()->getDriver()->getLastGeneratedValue();
                    
                    if ($set_group_settings[$this->allowed_group_settings[0]] == 1 && $set_group_settings[$this->allowed_group_settings[1]] == 1) {
                        // both settings were passed
                        // insert into group_settings table
                        $this->insert->into('group_settings')
                        ->columns(array('group_id', 'setting'))
                        ->values(array('group_id' => $id, 'setting' => 'join_authorization, closed_to_public'));
                        
                        $query = $this->sql->getAdapter()->query(
                            $this->sql->buildSqlString($this->insert),
                            Adapter::QUERY_MODE_EXECUTE
                        );
                        
                        if ($query->count() > 0) {
                            // insert into group members table and group admins table now
                            $this->insert->into('group_admins')
                            ->columns(array('group_id', 'user_id'))
                            ->values(array('group_id' => $id, 'user_id' => $this->getUserId()['id']));
                            
                            $query_admin = $this->sql->getAdapter()->query(
                                $this->sql->buildSqlString($this->insert),
                                Adapter::QUERY_MODE_EXECUTE
                            );
                            
                            $this->insert->into('group_members')
                            ->columns(array('group_id', 'member_id'))
                            ->values(array('group_id' => $id, 'member_id' => $this->getUserId()['id']));
                            
                            $query_member = $this->sql->getAdapter()->query(
                                $this->sql->buildSqlString($this->insert),
                                Adapter::QUERY_MODE_EXECUTE
                            );
                            
                            
                            if ($query_admin->count() > 0 && $query_member->count() > 0) {
                                // insert user into group members online table
                                $this->insertIntoGroupMembersOnline($id);
                                
                                return true;
                            } else {
                                throw new GroupsException("Error inserting you into the group members and/or group admins table, please try again.");
                            }
                        } else {
                            throw new GroupsException("Error inserting the group settings, please try again.");
                        }
                    } else if ($set_group_settings[$this->allowed_group_settings[0]] == 1) {
                        // only the first setting was passed (join_authorization)
                        // insert this setting
                        $this->insert->into('group_settings')
                        ->columns(array('group_id', 'setting'))
                        ->values(array('group_id' => $id, 'setting' => $this->allowed_group_settings[0]));
                        
                        $query = $this->sql->getAdapter()->query(
                            $this->sql->buildSqlString($this->insert),
                            Adapter::QUERY_MODE_EXECUTE
                        );
                        
                        if ($query->count() > 0) {
                            // insert into group member table and group admins table now
                            $this->insert->into('group_admins')
                            ->columns(array('group_id', 'user_id'))
                            ->values(array('group_id' => $id, 'user_id' => $this->getUserId()['id']));
                            
                            $query_admin = $this->sql->getAdapter()->query(
                                $this->sql->buildSqlString($this->insert),
                                Adapter::QUERY_MODE_EXECUTE
                            );
                            
                            
                            $this->insert->into('group_members')
                            ->columns(array('group_id', 'member_id'))
                            ->values(array('group_id' => $id, 'member_id' => $this->getUserId()['id']));
                            
                            $query_member = $this->sql->getAdapter()->query(
                                $this->sql->buildSqlString($this->insert),
                                Adapter::QUERY_MODE_EXECUTE
                            );
                            
                            if ($query_admin->count() > 0 && $query_member->count() > 0) {
                                // insert user into group members online table
                                $this->insertIntoGroupMembersOnlineFromCreateGroupId($id);
                                
                                return true;
                            } else {
                                throw new GroupsException("Error inserting you into the group members and/or group admins table, please try again.");
                            }
                        } else {
                            throw new GroupsException("Error inserting the group settings, please try again.");
                        }
                    } else if ($set_group_settings[$this->allowed_group_settings[1]] == 1) {
                        // only the second setting was passed (closed_to_public)
                        // insert this setting
                        $this->insert->into('group_settings')
                        ->columns(array('group_id', 'setting'))
                        ->values(array('group_id' => $id, 'setting' => $this->allowed_group_settings[1]));
                        
                        $query = $this->sql->getAdapter()->query(
                            $this->sql->buildSqlString($this->insert),
                            Adapter::QUERY_MODE_EXECUTE
                        );
                        
                        if ($query->count() > 0) {
                            // insert into group member table and group admins table now
                            $this->insert->into('group_admins')
                            ->columns(array('group_id', 'user_id'))
                            ->values(array('group_id' => $id, 'user_id' => $this->getUserId()['id']));
                            
                            $query_admin = $this->sql->getAdapter()->query(
                                $this->sql->buildSqlString($this->insert),
                                Adapter::QUERY_MODE_EXECUTE
                            );
                            
                            
                            $this->insert->into('group_members')
                            ->columns(array('group_id', 'member_id'))
                            ->values(array('group_id' => $id, 'member_id' => $this->getUserId()['id']));
                            
                            $query_member = $this->sql->getAdapter()->query(
                                $this->sql->buildSqlString($this->insert),
                                Adapter::QUERY_MODE_EXECUTE
                                );
                            
                            if ($query_admin->count() > 0 && $query_member->count() > 0) {
                                // insert user into group members online table
                                $this->insertIntoGroupMembersOnlineFromCreateGroupId($id);
                                
                                return true;
                            } else {
                                throw new GroupsException("Error inserting you into the group members and/or group admins table, please try again.");
                            }
                        } else {
                            throw new GroupsException("Error inserting the group settings, please try again.");
                        }
                    } else {
                        throw new GroupsException("Invalid group setting passed, please try again.");
                    }
                } 
            } else {
                // no group settings passed
                // just create the group without any settings in place
                $this->insert->into('groups')
                ->columns(array('group_name', 'group_creator', 'group_created_date'))
                ->values(array('group_name' => $group->group_name, 'group_creator' => $group_creator[0], 'group_created_date' => date('Y-m-d H:i:s')));
                
                $query = $this->sql->getAdapter()->query(
                    $this->sql->buildSqlString($this->insert),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if ($query->count() > 0) {
                    // get the last id passed
                    $id = $this->sql->getAdapter()->getDriver()->getLastGeneratedValue();
                    
                    // insert into group members table and group admin tables now
                    $this->insert->into('group_admins')
                    ->columns(array('group_id', 'user_id'))
                    ->values(array('group_id' => $id, 'user_id' => $this->getUserId()['id']));
                    
                    $query_admin = $this->sql->getAdapter()->query(
                        $this->sql->buildSqlString($this->insert),
                        Adapter::QUERY_MODE_EXECUTE
                    );
                    
                    
                    $this->insert->into('group_members')
                    ->columns(array('group_id', 'member_id'))
                    ->values(array('group_id' => $id, 'member_id' => $this->getUserId()['id']));
                    
                    $query_member = $this->sql->getAdapter()->query(
                        $this->sql->buildSqlString($this->insert),
                        Adapter::QUERY_MODE_EXECUTE
                    );
                    
                    if ($query_admin->count() > 0 && $query_member->count() > 0) {
                        // insert user into group members online table
                        $this->insertIntoGroupMembersOnlineFromCreateGroupId($id);
                        
                        return true;
                    } else {
                        throw new GroupsException("Error inserting you into the group members and/or group admins table, please try again.");
                    }
                } else {
                    throw new GroupsException("Error creating the group, please try again.");
                }
            }
        } else {
            // should never reach this point..
            throw new GroupsException("Username not found, aborting.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\GroupsInterface::insertIntoGroupMembersOnline()
     */
    public function insertIntoGroupMembersOnline($id)
    {
        // insert the member into the group members online table 
        $this->insert->into('group_members_online')
        ->columns(array('member_id', 'group_id', 'status'))
        ->values(array('member_id' => $this->getUserId()['id'], 'group_id' => $id, 'status' => 1));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->insert),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            return true;
        }
        
        return false;
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\GroupMembersOnlineInterface::getGroupMembersOnline()
     */
    public function getGroupMembersOnline($group_id = null)
    {
        // check to see which group members are online
        // by checking the group_members_online table
        // and fetching the member_id and status
        if ($group_id !== null) {
            $query = $this->connection->execute("SELECT DISTINCT groups.id, groups.group_name AS grp_name, gmo.member_id AS gm_mid, 
                gmo.status AS gm_status
                FROM group_members_online as gmo
                INNER JOIN group_members ON group_members.member_id = gmo.member_id
                INNER JOIN groups ON groups.id = gmo.group_id
                WHERE gmo.status = 1 AND groups.id = $group_id");
            
            if ($query->count() > 0) {
                // get the users on
                // fetch the display name based on the member_id
                // from the profiles table
                foreach ($query as $value) {
                    $this->group_members_id[] = $value['gm_mid'];
                    $this->group_names[] = $value['grp_name'];
                }
                
                $this->select->columns(array('display_name'))
                ->from('profiles')
                ->where(array('profile_id' => array_values($this->group_members_id)));
                
                $query = $this->sql->getAdapter()->query(
                    $this->sql->buildSqlString($this->select),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if ($query->count() > 0) {
                    $display_name = array();
                    
                    foreach ($query as $val) {
                        $display_name[] = $val['display_name'];
                    }
                    
                    return array('display_name' => $display_name);
                } else {
                    throw new GroupMembersOnlineException("User was not found.");
                }
            } else {
                throw new GroupMembersOnlineException("No users are currently on.");
            }
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\GroupsInterface::getGroupInformation()
     */
    public function getGroupInformation($group_id)
    {
        // get the group admins
        $query = $this->connection->execute("SELECT user_id, members.username FROM group_admins
            INNER JOIN members ON members.id = user_id
            INNER JOIN groups g ON group_id = g.id
            WHERE g.id = " . $group_id);
        
        $group_admins = array();
        
        if ($query->count() > 0) {
            foreach ($query as $group_admin) {
                $group_admins[] = $group_admin['username'];
            }
        } else {
            // no admins
            $group_admins[0] = "No admins exist for this group.";
        }
        
        // get the group members
        $query = $this->connection->execute("SELECT member_id, group_id, members.username FROM group_members
            INNER JOIN members ON members.id = member_id
            INNER JOIN groups g ON group_id = g.id
            WHERE g.id = " . $group_id);
        
        $group_members = array();
        
        if ($query->count() > 0) {
            
            foreach ($query as $group_member) {
                $group_members[] = $group_member['username'];
            }
        } else {
            // no members
            $group_members[0] = "No members exist in this group.";
        }
        
        // get the rest of the group info
        $fetch = $this->gateway->select(array('id' => $group_id));
        
        $row = $fetch->current();
        
        if (!$row) {
            throw new GroupsException("Error retrieving the group's information.");
        }
        
        return array(
            'admins'  => implode(", ", $group_admins),
            'members' => implode(", ", $group_members),
            'info' => $row,
        );
    }
}