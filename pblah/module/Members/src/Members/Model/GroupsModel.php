<?php
namespace Members\Model;

use Members\Model\Classes\Groups;
use Members\Model\Classes\GroupMembersOnline;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Members\Model\Filters\CreateGroup;
use Members\Model\Filters\JoinGroup;

class GroupsModel extends Groups
{

    /**
     *
     * @var TableGateway
     */
    public $gateway;

    /**
     * Constructor method for GroupsModel class
     *
     * @param TableGateway $gateway            
     * @param string $group_user            
     */
    public function __construct(TableGateway $gateway, $group_user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        parent::getTableGateway($this->gateway);
        parent::getSQLClass();
        parent::setUser($group_user);
    }

    /**
     * Grabs the current user's id
     * 
     * @return number
     */
    public function grabUserId()
    {
        return parent::getCurrentUserId();
    }

    /**
     * Gets the list of all the groups
     * 
     * @return string[]
     */
    public function listAllGroups()
    {
        return parent::getAllGroups();
    }

    /**
     * Gets the list of groups joined by the user for displaying on the group home page
     * 
     * @return array
     */
    public function listGroupsIndex()
    {
        return parent::getGroupsIndex();
    }

    /**
     * Gets the list of groups joined for the user
     *
     * @return mixed
     */
    public function listGroups()
    {
        return parent::getGroups();
    }

    /**
     * Lets the user leave a group
     * 
     * @param int $group_id            
     * @return boolean|array
     */
    public function leaveTheGroup($group_id)
    {
        return parent::leaveGroup($group_id);
    }

    /**
     * Lists all the groups for the user
     * 
     * @return array
     */
    public function getAllUserGroups()
    {
        return parent::getMoreGroups();
    }

    /**
     * Gets the member id for each group member
     * 
     * @return string[]
     */
    public function partGroup()
    {
        return parent::getMemberGroups();
    }

    /**
     * Gets all the group information
     * 
     * @param int $group_id            
     * @return boolean|string[]|ArrayObject[]|NULL[]
     */
    public function getGroupInformation($group_id)
    {
        
        // get the group admins
        $select_admins = new Select();
        
        $select_admins->from(array(
            'ga' => 'group_admins'
        ))->join(array(
            'm' => 'members'
        ), 'ga.user_id = m.id', array(
            'username'
        ))->join(array(
            'g' => 'groups'
        ), 'ga.group_id = g.id')
            ->where('g.id = ' . $group_id);
        
        $query_group_admin = parent::$sql->getAdapter()->query(
            parent::$sql->buildSqlString($select_admins), 
            Adapter::QUERY_MODE_EXECUTE);
        
        $group_admins = array();
        
        if (count($query_group_admin) == 0) {
            $group_admins[] = 'No admins found.';
        }
        
        foreach ($query_group_admin as $group_admin) {
            $group_admins[] = $group_admin['username'];
        }
        
        // get the group members
        $select = new Select();
        
        $select->from(array(
            'g' => 'group_members'
        ))->join(array(
            'm' => 'members'
        ), 'g.member_id = m.id', array(
            'username'
        ))->join(array(
            'grp' => 'groups'
        ), 'g.group_id = grp.id')
        ->where(array(
            'g.group_id' => $group_id
        ));
        
        $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
        
        $member_username = array();
        
        if (count($query) == 0) {
            $member_username[] = 'No members found.';
        }
        
        foreach ($query as $member) {
            $member_username[] = $member['username'];
        }
        
        // get the rest of the group info
        $fetch = $this->gateway->select(array(
            'id' => $group_id
        ));
        
        $row = $fetch->current();
        
        if (!$row) {
            return false;
        }
        
        return array(
            'admins' => implode(", ", $group_admins),
            'members' => implode(", ", $member_username),
            'info' => $row
        );
    }

    /**
     * Allows user to join group
     * 
     * @param int $group_id            
     * @param JoinGroup $data            
     * @return boolean
     */
    public function joinTheGroup($group_id, JoinGroup $data)
    {
        return parent::joinGroup($group_id, $data);
    }

    /**
     * Gets group members currently online
     * 
     * @return array[][]
     */
    public function getGroupMemsOnline($group_id = null)
    {
        return GroupMembersOnline::getGroupMembersOnline($group_id);
    }

    /**
     * Creates a new group
     * 
     * @param CreateGroup $group            
     * @return boolean
     */
    public function createNewGroup(CreateGroup $group)
    {
        return parent::createGroup($group);
    }
}