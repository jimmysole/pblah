<?php

namespace Members\Model\Classes;

use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;

use Members\Model\Classes\Exceptions\GroupMembersOnlineException;



class GroupMembersOnline extends Groups
{
    /**
     * @var array
     */
    protected static $member_id = array();

    /**
     * @var array
     */
    protected static $group_name = array();



    /**
     * Gets the group members that are online
     * @param int $group_id
     * @throws GroupMembersOnlineException
     * @return array[][]
     */
    public static function getGroupMembersOnline($group_id = null)
    {
        // check to see which group members are online
        // by checking the group_members_online table
        // and fetching the member_id and status
        $connection = parent::$sql->getAdapter()->getDriver()->getConnection();

        if ($group_id !== null) {
            $query = $connection->execute(
                "SELECT DISTINCT groups.id, groups.group_name AS grp_name, gmo.member_id AS gm_mid, gmo.status AS gm_status
                FROM group_members_online as gmo
                INNER JOIN group_members ON group_members.member_id = gmo.member_id
                INNER JOIN groups ON groups.id = gmo.group_id
                WHERE gmo.status = 1 AND groups.id = $group_id"
            );

            if (count($query) > 0) {
                // get the users on
                $select = new Select();

                // fetch the display name based on the member_id
                // from the profiles table
                foreach ($query as $value) {
                    self::$member_id[] = $value['gm_mid'];
                    self::$group_name[] = $value['grp_name'];
                }

                $select->columns(array('display_name'))
                ->from('profiles')
                ->where(array('profile_id' => array_values(self::$member_id)));


                $execute_query = parent::$sql->getAdapter()->query(
                    parent::$sql->buildSqlString($select),
                    Adapter::QUERY_MODE_EXECUTE
                );

                if (count($execute_query) > 0) {
                    $display_name = array();

                    foreach ($execute_query as $val) {
                        $display_name[] = $val['display_name'];
                    }

                    return array('display_name' => $display_name);
                } else {
                    throw new GroupMembersOnlineException('User was not found.');
                }
            } else {
                throw new GroupMembersOnlineException('No users are currently on.');
            }
        } else {
            $query = $connection->execute(
                "SELECT DISTINCT groups.id, groups.group_name AS grp_name, gmo.member_id AS gm_mid, gmo.status AS gm_status
                     FROM group_members_online as gmo
                     INNER JOIN group_members ON group_members.member_id = gmo.member_id
                     INNER JOIN groups ON groups.id = gmo.group_id
                     WHERE gmo.status = 1"
                );

            if (count($query) > 0) {
                // list the users on
                $select = new Select();

                foreach ($query as $value) {
                    // fetch the display name based on the member_id
                    // from the profiles table
                    self::$member_id[] = $value['gm_mid'];
                    self::$group_name[] = $value['grp_name'];
                }

                $select->columns(array('display_name'))
                ->from('profiles')
                ->where(array('profile_id' => array_values(self::$member_id)));


                $execute_query = parent::$sql->getAdapter()->query(
                    parent::$sql->buildSqlString($select),
                    Adapter::QUERY_MODE_EXECUTE
                );


                if (count($execute_query) > 0) {
                    $display_name = array();

                    foreach ($execute_query as $val) {
                        $display_name[] = $val['display_name'];
                    }

                    return array('display_name' => $display_name);
                } else {
                    throw new GroupMembersOnlineException('User was not found.');
                }
            } else {
                throw new GroupMembersOnlineException('No users are currently on.');
            }
        }
    }
}