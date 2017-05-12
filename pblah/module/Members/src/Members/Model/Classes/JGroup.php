<?php

namespace Members\Model\Classes;

use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;

use Zend\Db\Adapter\Adapter;


use Members\Model\Classes\Exceptions\GroupsException;
use Members\Model\Filters\JoinGroup;



class JGroup extends Groups
{
    
    /**
     * Sends a request to join a specific group
     * @param int $group_id
     * @param JGroup $data
     * @throws GroupsException
     * @return boolean
     */
    public static function sendRequestToJoin($group_id, JoinGroup $data)
    {
        if (empty($group_id)) {
            throw new GroupsException("Could not locate the group specified.");
        }
        
        
        // assign data to array
        // for insertion
        $holder = array(
            'group_id'  => $group_id,
            'member_id' => parent::getUserId()['id'],
            'user_data' => array($data->first_name, $data->last_name, $data->age, $data->message),
        );
        
        $get_member = new Select('members');
        
        $get_member->columns(array('username'))
        ->where(array('id' => $holder['member_id']));
        
        $query = parent::$sql->getAdapter()->query(
            parent::$sql->buildSqlString($get_member),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            $members = array();
            
            foreach ($query as $value) {
                $members[] = $value['username'];
            }
        } 
        
        // get the user ids of the group admins
        $select_admins = new Select('group_admins');
        
        $select_admins->columns(array('user_id'))
        ->where(array('group_id' => $group_id));
        
        $select_admins_query = parent::$sql->getAdapter()->query(
            parent::$sql->buildSqlString($select_admins),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($select_admins_query->count() > 0) {
            $group_admins = array();
            
            foreach ($select_admins_query as $admins) {
                $group_admins[] = $admins['user_id'];
            }
            
            // locate the admin(s) now based on the user ids just fetched
            $select_admins_from_members = new Select('members');
            
            $select_admins_from_members->columns(array('username'))
            ->where(array('id' => array_values($group_admins)));
            
            $select_admins_from_members_query = parent::$sql->getAdapter()->query(
                parent::$sql->buildSqlString($select_admins_from_members),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if ($select_admins_from_members_query->count() > 0) {
                $admin_users = array();
                
                foreach ($select_admins_from_members_query as $val) {
                    $admin_users[] = $val['username'];
                }
                
                $insert = new Insert('private_messages');
                
                // go ahead and insert into the private messages table
                $insert->columns(array('to', 'from', 'message', 'date_received', 'active'))
                ->values(array('to' => implode(", ", array_values($admin_users)), 'from' => $members[0],
                    'message' => 'A user has requested to join your group',
                    'date_received' => date('Y-m-d H:i:s'), 'active' => 1
                ));
                
                $exec = parent::$sql->getAdapter()->query(
                    parent::$sql->buildSqlString($insert),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if ($exec->count() > 0) {
                    // insert the join data into the group_join_requests table
                    $insert = new Insert('group_join_requests');
                    
                    $insert->columns(array('group_id', 'member_id', 'data'))
                    ->values(array('group_id' => $holder['group_id'], 'member_id' => $holder['member_id'], 
                        'user_data' => implode(", ", array_values($holder['user_data']))));
                    
                    $query = parent::$sql->getAdapter()->query(
                        parent::$sql->buildSqlString($insert),
                        Adapter::QUERY_MODE_EXECUTE
                    );
                    
                    if ($query->count() > 0) {
                        return true;
                    } else {
                        throw new GroupsException("An error has occurred while processing the request to join, please try again.");
                    }
                } else {
                    throw new GroupsException("Error sending your request, please try again.");
                }
            } else {
                throw new GroupsException("Error locating admin, perhaps they are no longer members.");
            }
        } else {
            throw new GroupsException("The group has no administrators.");
        }
       
    }
}