<?php

namespace Members\Model\Classes;



use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;


use Members\Model\Classes\Exceptions\GroupsException;
use Members\Model\Filters\CreateGroup;
use Members\Model\Filters\JoinGroup;




class Groups extends Profile
{

    /**
     * @var array
     */
    private static $group_settings = array();


    /**
     * @var array
     */
    private static $allowed_group_settings = array(
        'join_authorization',
        'closed_to_public',
    );
    
    
    /**
     * Gets the current user id
     * @return int
     */
    public static function getCurrentUserId()
    {
        return parent::getUserId()['id'];
    }


    /**
     * Gets all the groups that the user is not a part of
     * @throws GroupsException
     * @return string[]
     */
    public static function getAllGroups()
    {
        $connection = parent::$sql->getAdapter()->getDriver()->getConnection();
        
        $query = $connection->execute("SELECT id, group_name, group_creator, group_created_date FROM groups
               WHERE id not in (SELECT group_id FROM group_members WHERE member_id = " . parent::getUserId()['id'] . ")");
     
        if (count($query) > 0) {
            $all_groups_holder = array();

            foreach ($query as $key => $groups) {
                $all_groups_holder[$key] = $groups;
            }
            
            return $all_groups_holder;
        } else {
            throw new GroupsException("No groups were found.");
        }
    }
    
    
    /**
     * Gets the groups the user is apart of for displaying on group home page
     * @return array
     */
    public static function getGroupsIndex()
    {
        $connection = parent::$sql->getAdapter()->getDriver()->getConnection();

        $query = $connection->execute("SELECT groups.id AS group_id, groups.group_name AS g_name FROM group_members
                                       INNER JOIN members ON group_members.member_id = members.id
                                       INNER JOIN groups ON group_members.group_id = groups.id
                                       WHERE members.id = " . parent::getUserId()['id'] . " ORDER BY groups.id LIMIT 5");

        if (count($query) > 0) {
            $group_holder = array();

            foreach ($query as $key => $value) {
               $group_holder[$key] = $value;
            }

            return $group_holder;
        }
    }
    
    
    /**
     * Shows all of the groups (if user clicked on view-more)
     * @return array
     */
    public static function getMoreGroups()
    {
        $connection = parent::$sql->getAdapter()->getDriver()->getConnection();
        
        $query = $connection->execute("SELECT groups.id AS group_id, groups.group_name AS g_name FROM group_members
                                       INNER JOIN members ON group_members.member_id = members.id
                                       INNER JOIN groups ON group_members.group_id = groups.id
                                       WHERE members.id = " . parent::getUserId()['id'] . " ORDER BY groups.id");
        
        if (count($query) > 0) {
            $group_holder = array();
            
            foreach ($query as $key => $value) {
                $group_holder[$key] = $value;
            }
            
            return $group_holder;
        }
    }


    /**
     * Gets the list of groups that a user is apart of to display on member home page
     * @return array
     */
    public static function getGroups()
    {

        $connection = parent::$sql->getAdapter()->getDriver()->getConnection();

        $query = $connection->execute("SELECT groups.id AS group_id, groups.group_name AS g_name FROM group_members
                                       INNER JOIN members ON group_members.member_id = members.id
                                       INNER JOIN groups ON group_members.group_id = groups.id
                                       WHERE members.id = " . parent::getUserId()['id'] . " ORDER BY groups.id");

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
            return array('no_groups' => array("You aren't a part of any groups!"));
        }
    }


    /**
     * Gets the group(s) id
     * @throws GroupsExceptions
     * @return array
     */
    public static function getGroupIds()
    {
        $select = new Select();

        $select->columns(array('id'))
        ->from('groups')
        ->where("id IS NOT NULL OR id != ''");

        $query = parent::$sql->getAdapter()->query(
            parent::$sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );

        if (count($query) > 0) {
            $groups = array();

            foreach ($query as $group_ids) {
                $groups[] = $group_ids['id'];
            }
        } else {
            throw new GroupsException("Could not find any groups.");
        }

        return $groups;
    }



    /**
     * Handles the leaving of group(s) by a user
     * @param int $group_id
     * @throws GroupsExceptions
     * @return boolean|string[][]
     */
    public static function leaveGroup($group_id)
    {
        if (empty($group_id)) {
            throw new GroupsException("No group was selected to leave.");
        }

        // get the group based on $group_id
        $connection = parent::$sql->getAdapter()->getDriver()->getConnection();

        $query = $connection->execute("SELECT groups.group_name, groups.id AS group_id FROM group_members
                                       INNER JOIN members ON group_members.member_id = members.id
                                       INNER JOIN groups ON group_members.group_id = groups.id
                                       WHERE members.id = " . parent::getUserId()['id'] . " AND groups.id = " . $group_id);

        if (count($query) > 0) {
            // go ahead and delete the user from the group
            $delete = new Delete();

            $delete->from('group_members')
            ->where(array('member_id' => parent::getUserId()['id'], 'group_id' => $group_id));

            $exec = parent::$sql->getAdapter()->query(
                parent::$sql->buildSqlString($delete),
                Adapter::QUERY_MODE_EXECUTE
            );

            if (count($exec) > 0) {
                // remove from group_members_online table
                $delete_from_group_members_online = new Delete('group_members_online');
                
                $delete_from_group_members_online->where(array('member_id' => parent::getUserId()['id'], 'group_id' => $group_id));
                
                $query = parent::$sql->getAdapter()->query(
                    parent::$sql->buildSqlString($delete_from_group_members_online),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                // remove from group admins table as well
                // (if found)
                $delete_from_group_admins = new Delete();

                $delete_from_group_admins->from('group_admins')
                ->where(array('user_id' => parent::getUserId()['id'], 'group_id' => $group_id));

                $execute = parent::$sql->getAdapter()->query(
                    parent::$sql->buildSqlString($delete_from_group_admins),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if (count($execute) < 0) {
                    // not an admin
                    return array('message' => 'You left the group successfully.');
                } else if (count($execute) > 0 && count($query) > 0) {
                    return array('message' => 'You left the group successfully.');
                } else {
                    throw new GroupsException("An error occurred while attempting to process your request to leave the group specified, please try again.");
                }
            } else {
                throw new GroupsException("An error occurred while attempting to process your request to leave the group specified, please try again.");
            }
        } else {
            throw new GroupsException("You don't belong to the specified group!");
        }
    }


    /**
     * @see JoinGroup
     * @param int $group_id
     * @param array $data
     */
    public static function joinGroup($group_id, JoinGroup $data)
    {
        return JGroup::sendRequestToJoin($group_id, $data);
    }


    /**
     * Allows the user to create a group
     * @param CreateGroup $group
     * @throws GroupsException
     * @return boolean
     */
    public static function createGroup(CreateGroup $group)
    {
        if (empty($group->group_name)) {
            throw new GroupsException("You cannot leave your group name empty.");
        }

        // get the member username
        $select = new Select();

        $select->columns(array('username'))
        ->from('members')
        ->where('id = ' . parent::getUserId()['id']);

        $query = parent::$sql->getAdapter()->query(
            parent::$sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );

        if (count($query) > 0) {
            // username found
            $group_creator = array();

            foreach ($query as $val) {
                $group_creator[] = $val['username'];
            }

            // get the settings passed
            if ($group->group_settings == 1 || $group->group_settings2 == 1) {
                $group_settings = array($group->group_settings, $group->group_settings2);
                
                // combine self::$allowed_group_settings array and $group_settings as array
                // using self::$allowed_group_settings as the array keys and $group_settings as the values
                $set_group_settings = array_combine(self::$allowed_group_settings, $group_settings);
              
                
                // first create the group
                // then insert the settings by grabbing the group id just created
                $insert = new Insert('groups');

                $insert->columns(array('group_name, group_creator', 'group_created_date'))
                ->values(array('group_name' => $group->group_name, 'group_creator' => $group_creator[0],
                    'group_created_date' => date('Y-m-d H:i:s')));

                $query = parent::$sql->getAdapter()->query(
                    parent::$sql->buildSqlString($insert),
                    Adapter::QUERY_MODE_EXECUTE
                );

                if (count($query) > 0) {
                    // get the last id passed
                    $id = parent::$sql->getAdapter()->getDriver()->getLastGeneratedValue();

                    if ($set_group_settings[self::$allowed_group_settings[0]] == 1 && 
                        $set_group_settings[self::$allowed_group_settings[1]] == 1) {
                        // both settings were passed
                        // insert into group_settings_table
                        $insert = new Insert('group_settings');

                        $insert->columns(array('group_id', 'setting'))
                        ->values(array('group_id' => $id, 'setting' => 'join_authorization, closed_to_public'));

                        $query = parent::$sql->getAdapter()->query(
                            parent::$sql->buildSqlString($insert),
                            Adapter::QUERY_MODE_EXECUTE
                        );

                        if (count($query) > 0) {
                            // insert into groups member table and group admins table now
                            $insert_admin = new Insert('group_admins');
                            
                            $insert_admin->columns(array('group_id', 'user_id'))
                            ->values(array('group_id' => $id, 'user_id' => parent::getUserId()['id']));
                            
                            $query_admin = parent::$sql->getAdapter()->query(
                                parent::$sql->buildSqlString($insert_admin),
                                Adapter::QUERY_MODE_EXECUTE
                            );
                            
                            
                           
                            $insert = new Insert('group_members');
                            
                            $insert->columns(array('group_id', 'member_id'))
                            ->values(array('group_id' => $id, 'member_id' => parent::getUserId()['id']));
                            
                            $query = parent::$sql->getAdapter()->query(
                                parent::$sql->buildSqlString($insert),
                                Adapter::QUERY_MODE_EXECUTE
                            );
                            
                            if (count($query) > 0 && count($query_admin) > 0) {
                                // insert user into group members online table
                                self::insertIntoGroupMembersOnlineFromCreatedGroup($id); 
                                
                                return true;
                            } else {
                                throw new GroupsException("Error inserting you into the group members and/or group admins table, please try again.");
                            }
                        } else {
                            throw new GroupsException("Error inserting the group settings, please try again.");
                        }
                    } else if ($set_group_settings[self::$allowed_group_settings[0]] == 1) {
                        // only the first setting was passed (join_authorization)
                        // insert this setting only
                        $insert = new Insert('group_settings');

                        $insert->columns(array('group_id', 'setting'))
                        ->values(array('group_id' => $id, 'setting' => self::$allowed_group_settings[0]));

                        $query = parent::$sql->getAdapter()->query(
                            parent::$sql->buildSqlString($insert),
                            Adapter::QUERY_MODE_EXECUTE
                        );

                        if (count($query) > 0) {
                            // insert into groups member table and group admins table now
                            $insert_admin = new Insert('group_admins');
                            
                            $insert_admin->columns(array('group_id', 'user_id'))
                            ->values(array('group_id' => $id, 'user_id' => parent::getUserId()['id']));
                            
                            $query_admin = parent::$sql->getAdapter()->query(
                                parent::$sql->buildSqlString($insert_admin),
                                Adapter::QUERY_MODE_EXECUTE
                            );
                            
                            
                            
                            $insert = new Insert('group_members');
                            
                            $insert->columns(array('group_id', 'member_id'))
                            ->values(array('group_id' => $id, 'member_id' => parent::getUserId()['id']));
                            
                            $query = parent::$sql->getAdapter()->query(
                                parent::$sql->buildSqlString($insert),
                                Adapter::QUERY_MODE_EXECUTE
                            );
                            
                            if (count($query) > 0 && count($query_admin) > 0) {
                                // insert user into group members online table
                                self::insertIntoGroupMembersOnlineFromCreatedGroup($id); 
                                
                                return true;
                            } else {
                                throw new GroupsException("Error inserting you into the group members and/or group admins table, please try again.");
                            }
                        } else {
                            throw new GroupsException("Error inserting the group settings, please try again.");
                        }
                    } else if ($set_group_settings[self::$allowed_group_settings[1]] == 1) {
                        // only the second setting was passed (closed_to_public)
                        // insert this setting only
                        $insert = new Insert('group_settings');

                        $insert->columns(array('group_id', 'setting'))
                        ->values(array('group_id' => $id, 'setting' => self::$allowed_group_settings[1]));

                        $query = parent::$sql->getAdapter()->query(
                            parent::$sql->buildSqlString($insert),
                            Adapter::QUERY_MODE_EXECUTE
                        );

                        if (count($query) > 0) {
                            // insert into groups member table and group admins table now
                            $insert_admin = new Insert('group_admins');
                            
                            $insert_admin->columns(array('group_id', 'user_id'))
                            ->values(array('group_id' => $id, 'user_id' => parent::getUserId()['id']));
                            
                            $query_admin = parent::$sql->getAdapter()->query(
                                parent::$sql->buildSqlString($insert_admin),
                                Adapter::QUERY_MODE_EXECUTE
                            );
                            
                            
                            $insert = new Insert('group_members');
                            
                            $insert->columns(array('group_id', 'member_id'))
                            ->values(array('group_id' => $id, 'member_id' => parent::getUserId()['id']));
                            
                            $query = parent::$sql->getAdapter()->query(
                                parent::$sql->buildSqlString($insert),
                                Adapter::QUERY_MODE_EXECUTE
                            );
                            
                            
                            if (count($query) > 0 && count($query_admin) > 0) {
                                // insert user into group members online table
                                self::insertIntoGroupMembersOnlineFromCreatedGroup($id); 
                                
                                return true;
                            } else {
                                throw new GroupsException("Error inserting you into the group members and/or group admins table, please try again.");
                            }
                        } else {
                            throw new GroupsException("Error inserting the group settings, please try again.");
                        }
                    } else {
                        throw new GroupsException("Invalid group setting passed, please correct this and try again.");
                    }
                }
            } else {
                // no group settings passed
                // just create the group without any settings in place
                $insert = new Insert('groups');

                $insert->columns(array('group_name', 'group_creator', 'group_created_date'))
                ->values(array('group_name' => $group->group_name, 'group_creator' => $group_creator[0],
                    'group_created_date' => date('Y-m-d H:i:s')));

                $query = parent::$sql->getAdapter()->query(
                    parent::$sql->buildSqlString($insert),
                    Adapter::QUERY_MODE_EXECUTE
                );

                if (count($query) > 0) {
                    // get the last id passed
                    $id = parent::$sql->getAdapter()->getDriver()->getLastGeneratedValue();
                    
                    // insert into groups member table and group admins table now
                    $insert_admin = new Insert('group_admins');
                    
                    $insert_admin->columns(array('group_id', 'user_id'))
                    ->values(array('group_id' => $id, 'user_id' => parent::getUserId()['id']));
                    
                    $query_admin = parent::$sql->getAdapter()->query(
                        parent::$sql->buildSqlString($insert_admin),
                        Adapter::QUERY_MODE_EXECUTE
                    );
                    
                    
                    $insert = new Insert('group_members');
                    
                    $insert->columns(array('group_id', 'member_id'))
                    ->values(array('group_id' => $id, 'member_id' => parent::getUserId()['id']));
                    
                    $query = parent::$sql->getAdapter()->query(
                        parent::$sql->buildSqlString($insert),
                        Adapter::QUERY_MODE_EXECUTE
                    );
                    
                    if (count($query) > 0 && count($query_admin) > 0) {
                        // insert user into group members online table
                        self::insertIntoGroupMembersOnlineFromCreatedGroup($id); 
                        
                        return true;
                    } else {
                        throw new GroupsException("Error inserting you into the group members table, please try again.");
                    }
                } else {
                    throw new GroupsException("Error creating the group, please try again.");
                }
            }
        } else {
            // should never reach this point
            // but just in case, throw a GroupsException
            throw new GroupsException("Username not found, aborting.");
        }
    }
    
    /**
     * Inserts the user into the group members online table for the newly created group
     * @param int $id
     * @return boolean
     */
    public static function insertIntoGroupMembersOnlineFromCreatedGroup($id)
    {
        // insert the member into the group members online table from the just created group
        $insert = new Insert('group_members_online');
        
        $insert->columns(array('member_id', 'group_id', 'status'))
        ->values(array('member_id' => parent::getUserId()['id'], 'group_id' => $id, 'status' => 1));
        
        $query = parent::$sql->getAdapter()->query(
            parent::$sql->buildSqlString($insert),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            return true;
        } else {
            return false;
        }
    }
}

