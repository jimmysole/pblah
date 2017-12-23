<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;

use Zend\Db\Sql\Ddl;
use Zend\Db\Sql\Ddl\Column;
use Zend\Db\Sql\Ddl\Constraint;

use Zend\Db\Adapter\Adapter;

use Application\Model\Filters\Setup;


class SetupModel
{
    /**
     * @var TableGateway|null
     */
    protected $table_gateway;


    /**
     *
     * @var Sql
     */
    protected $sql;


    /**
     * @var AdapterInterface
     */
    protected $adapter;


    /**
     * Constructor method for SetupModel class
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
     * Checks if the setup has already been ran by searching for the 'admins' table
     * @return boolean
     */
    public function checkIfSetupRan()
    {
        $select = $this->table_gateway->getAdapter()
            ->getDriver()->getConnection()
            ->execute("SHOW TABLES LIKE 'admins'");


        if ($select->count() > 0) {
            return true;
        }

        return false;
    }

    public function createTables()
    {
        // create all the db tables needed for the application
        // create the admin table
        $admin_table = new Ddl\CreateTable('admins');
        $admin_table->addColumn(new Column\Integer('id', false, null, array('auto_increment' => true, 'unsigned' => true)));
        $admin_table->addColumn(new Column\Char('username', 15));
        $admin_table->addColumn(new Column\Char('password', 255));
        $admin_table->addColumn(new Column\Integer('setup_ran', false, null, array('unsigned' => true)));

        // add the constraints
        $admin_table->addConstraint(new Constraint\UniqueKey('username'));
        $admin_table->addConstraint(new Constraint\PrimaryKey('id'));


        // create the members table
        $members_table = new Ddl\CreateTable('members');
        $members_table->addColumn(new Column\Integer('id', false, null, array('auto_increment' => true, 'unsigned' => true)));
        $members_table->addColumn(new Column\Char('username', 15));
        $members_table->addColumn(new Column\Char('password', 150));
        $members_table->addColumn(new Column\Blob('avatar', true));
        $members_table->addColumn(new Column\Boolean('new', false, null, array('unsigned' => false)));

        // add the constraints
        $members_table->addConstraint(new Constraint\UniqueKey('username'));
        $members_table->addConstraint(new Constraint\PrimaryKey('id'));


        // create the profile table
        $profile_table = new Ddl\CreateTable('profiles');
        $profile_table->addColumn(new Column\Integer('profile_id', false, null, array('unsigned' => true)));
        $profile_table->addColumn(new Column\Char('display_name', 75));
        $profile_table->addColumn(new Column\Char('email_address', 75));
        $profile_table->addColumn(new Column\Integer('age', false, null, array('unsigned' => true)));
        $profile_table->addColumn(new Column\Char('location', 150));
        $profile_table->addColumn(new Column\Text('bio'));

        // add the constraints
        $profile_table->addConstraint(new Constraint\UniqueKey('display_name'));
        $profile_table->addConstraint(new Constraint\UniqueKey('email_address'));
        $profile_table->addConstraint(new Constraint\PrimaryKey('profile_id'));


        // create the profile settings table
        $profile_settings_table = new Ddl\CreateTable('profile_settings');
        $profile_settings_table->addColumn(new Column\Integer('profile_id', false, null, array('unsigned' => true)));
        $profile_settings_table->addColumn(new Column\Char('setting', 75));
        $profile_settings_table->addColumn(new Column\Integer('value', false, null, array('unsigned' => false)));

        // add the constraints
        $profile_settings_table->addConstraint(new Constraint\ForeignKey('profile_id_fk', 'profile_id', 'profiles', 'profile_id', 'cascade', 'cascade'));
        $profile_settings_table->addConstraint(new Constraint\UniqueKey('setting'));
        $profile_settings_table->addConstraint(new Constraint\PrimaryKey('profile_id'));


        // create the groups table
        $groups_table = new Ddl\CreateTable('groups');
        $groups_table->addColumn(new Column\Integer('id', false, null, array('unsigned' => true, 'auto_increment' => true)));
        $groups_table->addColumn(new Column\Char('group_name', 100));
        $groups_table->addColumn(new Column\Char('group_creator', 15));
        $groups_table->addColumn(new Column\Datetime('group_created_date'));
        $groups_table->addColumn(new Column\Text('group_description'));

        // add the constraints
        $groups_table->addConstraint(new Constraint\UniqueKey('group_name'));
        $groups_table->addConstraint(new Constraint\PrimaryKey('id'));


        // create the group settings table
        $group_settings_table = new Ddl\CreateTable('group_settings');
        $group_settings_table->addColumn(new Column\Integer('group_id', false, null, array('unsigned' => true)));
        $group_settings_table->addColumn(new Column\Char('setting', 75));

        // add the constraints
        $group_settings_table->addConstraint(new Constraint\ForeignKey('group_settings_id', 'group_id', 'groups', 'id', 'cascade', 'cascade'));
        $group_settings_table->addConstraint(new Constraint\PrimaryKey('group_id'));


        // create the group_members table
        $group_mems_table = new Ddl\CreateTable('group_members');
        $group_mems_table->addColumn(new Column\Integer('group_id', false, null, array('unsigned' => true)));
        $group_mems_table->addColumn(new Column\Integer('member_id', false, null, array('unsigned' => true)));
        $group_mems_table->addColumn(new Column\Boolean('banned', false, 0));
        $group_mems_table->addColumn(new Column\Boolean('suspended', false, 0));

        // add the constraints
        $group_mems_table->addConstraint(new Constraint\ForeignKey('group_id_fk', 'group_id', 'groups', 'id', 'cascade', 'cascade'));
        $group_mems_table->addConstraint(new Constraint\ForeignKey('member_id_fk', 'member_id', 'members', 'id', 'cascade', 'cascade'));
        $group_mems_table->addConstraint(new Constraint\PrimaryKey(array('group_id', 'member_id')));


        // create the group_admin table
        $group_admins_table = new Ddl\CreateTable('group_admins');
        $group_admins_table->addColumn(new Column\Integer('group_id', false, null, array('unsigned' => true)));
        $group_admins_table->addColumn(new Column\Integer('user_id', false, null, array('unsigned' => true)));

        // add the constraints
        $group_admins_table->addConstraint(new Constraint\ForeignKey('fk_group_id', 'group_id', 'groups', 'id', 'cascade', 'cascade'));
        $group_admins_table->addConstraint(new Constraint\ForeignKey('fk_user_id', 'user_id', 'members', 'id', 'cascade', 'cascade'));
        $group_admins_table->addConstraint(new Constraint\PrimaryKey(array('group_id', 'user_id')));


        // create the group_ranks table
        $group_ranks_table = new Ddl\CreateTable('group_ranks');
        $group_ranks_table->addColumn(new Column\Integer('group_id', false, null, array('unsigned' => true)));
        $group_ranks_table->addColumn(new Column\Integer('user_id', false, null, array('unsigned' => true)));
        $group_ranks_table->addColumn(new Column\Integer('rank', false, null, array('unsigned' => true)));

        // add the constraints
        $group_ranks_table->addConstraint(new Constraint\ForeignKey('fk_group_id_rank', 'group_id', 'groups', 'id', 'cascade', 'cascade'));
        $group_ranks_table->addConstraint(new Constraint\ForeignKey('fk_user_id_rank', 'user_id', 'members', 'id', 'cascade', 'cascade'));
        $group_ranks_table->addConstraint(new Constraint\PrimaryKey(array('group_id', 'user_id')));


        // create the group members online table
        $group_members_online_table = new Ddl\CreateTable('group_members_online');
        $group_members_online_table->addColumn(new Column\Integer('member_id', false, null, array('unsigned' => true)));
        $group_members_online_table->addColumn(new Column\Integer('group_id', false, null, array('unsigned' => true)));
        $group_members_online_table->addColumn(new Column\Boolean('status', true, null, array('unsigned' => false)));

        // add the constraints
        $group_members_online_table->addConstraint(new Constraint\ForeignKey('fk_group_user_id', 'member_id', 'group_members', 'member_id', 'cascade', 'cascade'));
        $group_members_online_table->addConstraint(new Constraint\ForeignKey('fk_groups_online', 'group_id', 'groups', 'id', 'cascade', 'cascade'));
        $group_members_online_table->addConstraint(new Constraint\PrimaryKey(array('member_id', 'group_id')));

        
        // create the group join requests table
        $group_join_requests = new Ddl\CreateTable('group_join_requests');
        $group_join_requests->addColumn(new Column\Integer('group_id', false, null, array('unsigned' => true)));
        $group_join_requests->addColumn(new Column\Integer('member_id', false, null, array('unsigned' => true)));
        $group_join_requests->addColumn(new Column\Text('user_data'));
        
        // add the constraints
        $group_join_requests->addConstraint(new Constraint\PrimaryKey(array('group_id', 'member_id')));
        

        // create the boards table
        $boards_table = new Ddl\CreateTable('boards');
        $boards_table->addColumn(new Column\Integer('id', false, null, array('auto_increment' => true, 'unsigned' => true)));
        $boards_table->addColumn(new Column\Char('board_name', 150));
        $boards_table->addColumn(new Column\Text('board_moderators'));

        // add the constraints
        $boards_table->addConstraint(new Constraint\UniqueKey('board_name'));
        $boards_table->addConstraint(new Constraint\PrimaryKey('id'));


        // create the board messages table
        $boards_msg_table = new Ddl\CreateTable('board_messages');
        $boards_msg_table->addColumn(new Column\Integer('message_id', false, null, array('auto_increment' => true, 'unsigned' => true)));
        $boards_msg_table->addColumn(new Column\Integer('board_id', false, null, array('unsigned' => true)));
        $boards_msg_table->addColumn(new Column\Integer('num_of_posts', false, 0, array('unsigned' => false)));
        $boards_msg_table->addColumn(new Column\Char('author', 15));
        $boards_msg_table->addColumn(new Column\Char('subject', 150));
        $boards_msg_table->addColumn(new Column\Text('messages'));
        $boards_msg_table->addColumn(new Column\Text('replies'));
        $boards_msg_table->addColumn(new Column\Blob('attachments'));

        // add the constraints
        $boards_msg_table->addConstraint(new Constraint\ForeignKey('fk_board_id', 'board_id', 'boards', 'id', 'cascade', 'cascade'));
        $boards_msg_table->addConstraint(new Constraint\PrimaryKey('message_id'));


        // create the trending topics table
        $trending_topics = new Ddl\CreateTable('trending_topics');
        $trending_topics->addColumn(new Column\Integer('id', false, null, array('auto_increment' => true, 'unsigned' => true)));
        $trending_topics->addColumn(new Column\Char('topic', 150));
        $trending_topics->addColumn(new Column\Char('author', 15));
        $trending_topics->addColumn(new Column\Integer('number_of_views', false, null, array('unsigned' => true)));
        $trending_topics->addColumn(new Column\Text('topic_message'));

        // add the constraints
        $trending_topics->addConstraint(new Constraint\UniqueKey('topic'));
        $trending_topics->addConstraint(new Constraint\PrimaryKey('id'));


        // create the private messages table
        $private_messages = new Ddl\CreateTable('private_messages');
        $private_messages->addColumn(new Column\Integer('id', false, null, array('auto_increment' => true, 'unsigned' => true)));
        $private_messages->addColumn(new Column\Text('to'));
        $private_messages->addColumn(new Column\Char('from', 15));
        $private_messages->addColumn(new Column\Text('message'));
        $private_messages->addColumn(new Column\Datetime('date_received'));
        $private_messages->addColumn(new Column\Integer('active', true, null));
        $private_messages->addColumn(new Column\Integer('archived', true, null));

        // add the constraints
        $private_messages->addConstraint(new Constraint\PrimaryKey('id'));


        // create the themes table
        $themes_table = new Ddl\CreateTable('themes');
        $themes_table->addColumn(new Column\Integer('id', false, null, array('auto_increment' => true, 'unsigned' => true)));
        $themes_table->addColumn(new Column\Char('theme_name', 150));
        $themes_table->addColumn(new Column\Char('theme_author'. 15));
        $themes_table->addColumn(new Column\Char('theme_css_file', 200));
        $themes_table->addColumn(new Column\Char('theme_images', 200));

        // add the constraints
        $themes_table->addConstraint(new Constraint\UniqueKey('theme_name'));
        $themes_table->addConstraint(new Constraint\PrimaryKey('id'));


        // create the events table
        $events_table = new Ddl\CreateTable('events');
        $events_table->addColumn(new Column\Integer('id', false, null, array('auto_increment' => true, 'unsigned' => true)));
        $events_table->addColumn(new Column\Integer('member_id', false, null, array('unsigned' => true)));
        $events_table->addColumn(new Column\Char('event_name', 150));
        $events_table->addColumn(new Column\Text('event_description'));
        $events_table->addColumn(new Column\Datetime('start_date'));
        $events_table->addColumn(new Column\Datetime('end_date'));

        // add the constraints
        $events_table->addConstraint(new Constraint\ForeignKey('fk_events', 'member_id', 'members', 'id', 'cascade', 'cascade'));
        $events_table->addConstraint(new Constraint\PrimaryKey(array('id', 'member_id')));

        $pending_users_table = new Ddl\CreateTable('pending_users');
        $pending_users_table->addColumn(new Column\Integer('id', false, null, array('auto_increment' => true, 'unsigned' => true)));
        $pending_users_table->addColumn(new Column\Char('username', 15));
        $pending_users_table->addColumn(new Column\Char('password', 255));
        $pending_users_table->addColumn(new Column\Char('email', 75));
        $pending_users_table->addColumn(new Column\Char('pending_code', 255));

        // add the constraints
        $pending_users_table->addConstraint(new Constraint\UniqueKey('username'));
        $pending_users_table->addConstraint(new Constraint\PrimaryKey('id'));


        // create the sessions table
        $sessions_table = new Ddl\CreateTable('sessions');
        $sessions_table->addColumn(new Column\Char('username', 30));
        $sessions_table->addColumn(new Column\Char('password', 255));
        $sessions_table->addColumn(new Column\Integer('active', false, null, array('unsigned' => true)));
        $sessions_table->addColumn(new Column\Char('session_id', 150));

        // add the constraints
        $sessions_table->addConstraint(new Constraint\UniqueKey('username'));

        
        // create the status table
        $status_table = new Ddl\CreateTable('status');
        $status_table->addColumn(new Column\Integer('id', false, null, array('auto_increment' => false, 'unsigned' => true)));
        $status_table->addColumn(new Column\Char('status', 150));
        
        // add the constraints
        $status_table->addConstraint(new Constraint\PrimaryKey('id'));

        
        // create the friends table
        $friends_table = new Ddl\CreateTable('friends');
        $friends_table->addColumn(new Column\Integer('friend_id', false, null, array('auto_increment' => false, 'unsigned' => true)));
        
        // add the constraints
        $friends_table->addConstraint(new Constraint\ForeignKey('fk_friend_id', 'friend_id', 'members', 'id', 'cascade', 'cascade'));
        $friends_table->addConstraint(new Constraint\UniqueKey('friend_id'));
        


        // make the tables
        $this->query(array(
            $admin_table,
            $members_table,
            $profile_table,
            $profile_settings_table,
            $groups_table,
            $group_settings_table,
            $group_mems_table,
            $group_admins_table,
            $group_ranks_table,
            $group_members_online_table,
            $group_join_requests,
            $boards_table,
            $boards_msg_table,
            $trending_topics,
            $private_messages,
            $themes_table,
            $events_table,
            $pending_users_table,
            $sessions_table,
            $status_table,
            $friends_table,
        ));

        return true;
    }

    /**
     * Inserts the admin credentials into the admins table and sets setup_ran to 1
     * to avoid the setup process from being run again
     * @param Setup $setup
     * @return boolean
     */
    public function makeAdmin(Setup $setup)
    {
        $select = new Select();

        // first check to see if the setup has already been run
        // by checking the admin table
        $select->columns(array(
            'setup_ran'))
            ->from('admins')
            ->where(array(
                'setup_ran' => 1));


            $rowset = $this->adapter->query(
                $this->sql->buildSqlString($select),
                Adapter::QUERY_MODE_EXECUTE
            );

            if (count($rowset) > 0) {
                return false;
            }

            $this->insertAdminValues(new Insert('admins'), array('username', 'password'),
                array(
                    'username'  => $setup->username,
                    'password'  => password_hash($setup->password, PASSWORD_DEFAULT),
                ));

            return true;
    }

    /**
     * Runs a query
     * @return bool
     */
    public function query(array $tables)
    {
        foreach ($tables as $value) {
            $this->adapter->query(
                $this->sql->buildSqlString($value),
                Adapter::QUERY_MODE_EXECUTE
            );
        }

        return true;
    }


    /**
     * Method that handles insertion of admin credentials
     * @param Insert $table
     * @param array $columns
     * @param array $data
     * @return boolean
     */
    public function insertAdminValues(Insert $table, array $columns, array $data)
    {
        $table->columns(array($columns[0], $columns[1]))
        ->values(array(
            'username'  => $data['username'],
            'password'  => $data['password'],
            'setup_ran' => 1,
        ));

        $this->adapter->query(
            $this->sql->buildSqlString($table),
            Adapter::QUERY_MODE_EXECUTE
        );

        return true;
    }
}