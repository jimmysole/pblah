<?php
namespace Application\Model;


use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Insert;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Select;
use Application\Model\Filters\Login;


class LoginModel
{
    /**
     * @var TableGateway
     */
    protected $table_gateway;

    /**
     * @var Sql
     */
    protected $sql;

    /**
     * @var AdapterInterface
     */
    protected $adapter;


    /**
     * Constructor method for LoginModel class
     * 
     * @param TableGateway $gateway
     */
    public function __construct(TableGateway $gateway)
    {
        // check if $gateway was passed an instance of TableGateway
        // if so, assign $this->table_gateway the value of $gateway
        // if not, make it null
        $gateway instanceof TableGateway ? $this->table_gateway = $gateway : $this->table_gateway = null;

        $this->sql = new Sql($this->table_gateway->getAdapter());

        $this->adapter = $this->table_gateway->getAdapter();
    }


    /**
     * Verifies a password
     * 
     * @param Login $login
     * @return array|boolean
     */
    public function verifyPassword(Login $login)
    {
        $adapter = $this->sql->getAdapter()->getDriver()->getConnection();

        $query = $adapter->execute("SELECT password FROM admins WHERE username = '" . $login->username . "'");

        if ($query->count() > 0) {
            foreach ($query as $row) {
                $admin_pass = $row['password'];
            }
            
            if (password_verify($login->password, $admin_pass)) {
                // return admin pass
                return array('admin' => true, 'pass' => $admin_pass);
            }
        } else {
            // try the members table if no admin was found
            $query = $adapter->execute("SELECT password FROM members WHERE username = '" . $login->username . "'");

            foreach ($query as $row) {
                $member_pass = $row['password'];
            }

            if (password_verify($login->password, $member_pass)) {
                // return member pass
                return array('member' => true, 'pass' => $member_pass);
            } else {
                // return false if no matching password was found in admin and members table
                return false;
            }
        }
    }


    /**
     * Checks if a session is already active
     * 
     * @param mixed $username
     * @return boolean
     */
    public function checkSession($username)
    {
        $adapter = $this->sql->getAdapter()->getDriver()->getConnection();

        $query = $adapter->execute("SELECT active AS active_user FROM sessions WHERE username = '$username'");

        foreach ($query as $row) {
            if ($row['active_user'] == 1) {
                return false;
            }
        }

        return true;
    }


    /**
     * Inserts session info into sessions table upon sucessful login
     * 
     * @param mixed $username
     * @param mixed $password
     * @param mixed $session_id
     * @return boolean
     */
    public function insertSession($username, $password, $session_id)
    {
        $insert = new Insert('sessions');

        $insert->columns(array(
            'username', 'password', 'active', 'session_id'
        ))->values(array('username' => $username, 'password' => $password, 'active' => 1, 'session_id' => $session_id));


        $this->adapter->query(
            $this->sql->buildSqlString($insert),
            Adapter::QUERY_MODE_EXECUTE
        );

        $this->insertIntoGroupMembersOnline($username);


        return true;
    }


    /**
     * Inserts session info into group members online table
     * 
     * @param string $username
     * @throws \Exception
     * @return boolean
     */
    public function insertIntoGroupMembersOnline($username)
    {
        // select the user id from the members table
        // and fetch the groups the user is a part of
        // from the group_members table
        $select = new Select();

        $select->columns(array('id'))
        ->from('members')
        ->where(array('username' => $username));

        $query = $this->adapter->query(
            $this->sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );

        $user_id = array();

        foreach ($query as $value) {
            $user_id[] = $value['id'];
        }

        if (count($user_id) > 0) {
            // user id found
            // find the ocrresponding group id(s)
            $group_select = new Select();

            $group_select->columns(array('member_id', 'group_id'))
            ->from('group_members')
            ->where('member_id = ' . intval($user_id[0]));

            $group_query = $this->adapter->query(
                $this->sql->buildSqlString($group_select),
                Adapter::QUERY_MODE_EXECUTE
            );

            $group_ids = array();

            foreach ($group_query as $value) {
                $group_ids[] = array('member_id' => $value['member_id'], 'group_id' => $value['group_id']);
            }

            if (count($group_ids) > 0) {
                // group_id found
                // insert into the group_members_online table
                $insert = new Insert('group_members_online');

                foreach ($group_ids as $g_value) {
                    $insert->columns(array('member_id', 'group_id', 'status'))
                    ->values(array('member_id' => $g_value['member_id'],
                        'group_id' => $g_value['group_id'],
                        'status' => 1));

                    $insert_query = $this->adapter->query(
                        $this->sql->buildSqlString($insert),
                        Adapter::QUERY_MODE_EXECUTE
                    );
                }

                // check to see if $insert_query was ok
                if ($insert_query->count() > 0) {
                    return true;
                } else {
                    throw new \Exception("Error setting member online for the group specified.");
                }
            } else {
                throw new \Exception("User is not a part of any groups.");
            }
        } else {
            throw new \Exception("User was not located in the database.");
        }
    }
    
    
    /**
     * Inserts user id into the friends online table
     * 
     * @param string $username
     * @throws \Exception
     * @return boolean
     */
    public function insertIntoFriendsOnline($username)
    {
        // select the id from the members table
        // and use it as the user_id for insert
        // into friends_online table
        $select = new Select('members');
        
        $select->columns(array('id'))
        ->where(array('username' => $username));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            foreach ($query as $value) {
                $user_id = $value['id'];
            }
            
            // insert 
            $insert = new Insert('friends_online');
            
            $insert->columns(array('user_id'))
            ->values(array('user_id' => $user_id));
            
            $query = $this->sql->getAdapter()->query(
                $this->sql->buildSqlString($insert),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if ($query->count() > 0) {
                return true;
            } else {
                throw new \Exception("Error setting your status on online for friends.");
            }
        } else {
            throw new \Exception("Error retrieving member id.");
        }
    }
}