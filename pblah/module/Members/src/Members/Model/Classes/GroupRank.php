<?php
namespace Members\Model\Classes;

use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Insert;

use Members\Model\Classes\Exceptions\GroupsException;
use Members\Model\Classes\Exceptions\GroupRankException;


class GroupRank extends Groups
{

    /**
     * Holds the rank ids
     *
     * @var array
     */
    public static $ranks = array(
        'group_leader'   => 1,
        'group_asst'     => 2,
        'group_member'   => 3,
        'group_invitees' => 4
    );

    /**
     * Adds a rank for a user to the group
     *
     * @param int $rank
     * @param int $user_id
     * @param int $group_id
     * @throws GroupsException
     * @return boolean
     */
    public static function addRank($rank, $user_id, $group_id)
    {
        // make sure the user is an admin
        // (only admins should have the right to add a rank)
        $select = new Select();

        $select->columns(array(
            'rank',
            'group_id'
        ))->from('group_ranks')
          ->join('group_admins', 'group_ranks.group_id = group_admins.group_id')
          ->where('group_admins.user_id = ' . parent::getUserId()['id']);

        $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);

        if (count($query) > 0) {
            // insert the rank for the user
            // first get the group id for which the rank corresponds to
            $group_select = new Select();

            $group_select
            ->from('groups')
            ->columns(array(
                'id'
            ))->where('id = ' . $group_id)
            ->limit(1);

            $group_id_query = parent::$sql->getAdapter()->query(
                parent::$sql->buildSqlString($group_select), 
                Adapter::QUERY_MODE_EXECUTE
            );

            if (count($group_id_query) > 0) {
                $group_id_holder = array();

                foreach ($group_id_query as $values) {
                    $group_id_holder[] = array(
                        'group_id' => $values['id']
                    );
                }

                // insert the rank now
                $insert = new Insert();

                $insert->into('group_ranks')
                    ->columns(array(
                    'group_id',
                    'user_id',
                    'rank'
                ))
                ->values(array(
                    'group_id' => $group_id_holder['group_id'],
                    'user_id' => $user_id,
                    'rank' => $rank
                ));

                $insert_query = parent::$sql->getAdapter()->query(
                    parent::$sql->buildSqlString($insert),
                    Adapter::QUERY_MODE_EXECUTE
                );

                if ($insert_query > 0) {
                    return true;
                } else {
                    throw new GroupsException("Error adding the rank, please try again.");
                }
            } else {
                throw new GroupsException("Invalid group id.");
            }
        } else {
            throw new GroupsException("Only admins can add ranks.");
        }
    }

    /**
     * Sets a rank for a user
     *
     * @param int $rank
     * @param int $user_id
     * @param int $group_id
     * @throws GroupsExceptions
     * @return boolean
     */
    public static function setRank($rank, $user_id, $group_id)
    {
        // make sure the user is an admin
        // (only admins should have the right to set rank)
        $select = new Select();

        $select->columns(array(
            'rank',
            'group_id'
        ))
        ->from('group_ranks')
        ->join('group_admins', 'group_ranks.group_id = group_admins.group_id')
        ->where('group_admins.user_id = ' . parent::getUserId()['id']);

        $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);

        if (count($query) > 0) {
            // admin found
            // assign the rank given
            if ($rank == 1) {
                $rank = self::$ranks['group_leader'];
            } else
                if ($rank == 2) {
                    $rank = self::$ranks['group_asst'];
                } else
                    if ($rank == 3) {
                        $rank = self::$ranks['group_member'];
                    } else
                        if ($rank == 4) {
                            $rank = self::$ranks['group_invitees'];
                        } else {
                            throw new GroupsException("Invalid rank given, only ranks 1-4 are permitted.");
                        }

            // get the group_id
            $group_id = array();

            foreach ($query as $row) {
                $group_id[] = array(
                    'id' => $row['group_id'],
                    'rank' => $row['rank']
                ); // group id & rank
            }

            // check if the specified user exists in the group
            $get_user_from_group = new Select();

            $get_user_from_group->columns(array(
                'uid' => 'member_id'
            ))->from(array(
                'gm' => 'group_members'
            ))->join('groups', 'gm.group_id = groups.id')
              ->where(array(
                'groups.id' => $group_id,
                'group_members.member_id' => $user_id
            ));

            $get_user_from_group_query = parent::$sql->getAdapter()->query(
                parent::$sql->buildSqlString($get_user_from_group), 
                Adapter::QUERY_MODE_EXECUTE
            );

            if (count($get_user_from_group_query) > 0) {
                // user was found in the groups
                // set the rank specified
                $connection = parent::$sql->getAdapter()
                    ->getDriver()
                    ->getConnection();

                $query = $connection->execute("UPDATE group_ranks
                    INNER JOIN group_members ON group_ranks.group_id = group_members.group_id
                    INNER JOIN members ON group_members.member_id = members.id
                    SET group_ranks.rank = $rank
                    WHERE group_members.member_id = $user_id AND group_ranks.group_id = $group_id");

                if (count($query) > 0) {
                    return true;
                } else {
                    throw new GroupsException("Error updating rank for user specified, please try again.");
                }
            } else {
                // user not found in specified group
                throw new GroupsException("User not found in the specified group.");
            }
        } else {
            // admin not found
            throw new GroupsException("Admin record not found, only admins can set ranks.");
        }
    }

    /**
     * Removes a rank for a user
     *
     * @param int $user_id
     * @param int $group_id
     * @throws GroupRankException
     * @return boolean
     */
    public static function deleteRank($user_id, $group_id)
    {
        // @todo scrap this entire method and rewrite it
        if (empty($user_id) || empty($group_id)) {
            throw new GroupsException("User id and/or group id cannot be left empty in order to delete a rank.");
        }

        // make sure the user is an admin
        // (only admins should have the right to add a rank)
        $select = new Select();

        $select->columns(array(
            'rank',
            'group_id'
        ))
            ->from('group_ranks')
            ->join('group_admins', 'group_ranks.group_id = group_admins.group_id')
            ->where('group_admins.user_id = ' . parent::getUserId()['id']);

        $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);

        if (count($query) > 0) {
            // get the rank of the user
            // from the group_ranks table
            $select = new Select();

            $select->from('group_ranks')
                ->columns(array(
                '*'
            ))
                ->where(array(
                'user_id' => $user_id,
                'group_id' => $group_id
            ))
                ->limit(1);
        } else {
            // admin not found
            throw new GroupRankException("Admin record not found, only admins can remove ranks.");
        }

        /*
         * $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
         *
         * if (count($query) > 0) {
         * // result was found
         * // retrieve it and then delete the rank for the user
         * $rank_info = array();
         *
         * foreach ($query as $values) {
         * $rank_info[] = array(
         * 'group_id' => $values['group_id'],
         * 'user_id' => $values['user_id']
         * );
         * }
         *
         * $delete = new Delete();
         *
         * $delete->from('group_ranks')->where(array(
         * 'group_id' => $rank_info['group_id'],
         * 'user_id' => $rank_info['user_id']
         * ));
         *
         * $query_delete = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($delete), Adapter::QUERY_MODE_EXECUTE);
         *
         * if ($query_delete > 0) {
         * return true;
         * } else {
         * throw new GroupsException("Error removing rank, please try again.");
         * }
         * } else {
         * throw new GroupsException("No rank was found for the user specified.");
         * }
         */
    }

    /**
     * Gets the group's ranks
     *
     * @param int $group_id
     * @throws GroupsException
     * @return array
     */
    public static function getRanks($group_id)
    {
        if (empty($group_id)) {
            throw new GroupsException("Group does not exist or is invalid.");
        }

        // get the ranks based on $groups
        // which gets the groups the user is a part of
        $connection = parent::$sql->getAdapter()
            ->getDriver()
            ->getConnection();

        $query = $connection->execute("SELECT DISTINCT group_ranks.rank AS gr_rank, group_ranks.group_id AS gr_id,
            members.username AS gr_user FROM group_ranks
            INNER JOIN groups ON group_ranks.group_id = " . $group_id . "
            INNER JOIN group_admins ON group_ranks.group_id = group_admins.group_id
            INNER JOIN group_members ON group_ranks.group_id = group_members.group_id
            INNER JOIN members ON members.id = group_ranks.user_id
            WHERE group_ranks.group_id = $group_id");

        if (count($query) > 0) {
            $group_ranks = array();
            $group_ids = array();
            $group_users = array();

            foreach ($query as $value) {
                $group_ranks[] = $value['gr_rank'];
                $group_ids[] = $value['gr_id'];
                $group_users[] = $value['gr_user'];
            }

            return array(
                'group_ranks' => $group_ranks,
                'group_ids' => $group_ids,
                'group_users' => $group_users
            );
        } else {
            throw new GroupsException("No ranks were found for the specified group.");
        }
    }
}