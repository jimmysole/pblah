<?php
namespace Application\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;


class LogoutModel
{
    protected $table_gateway;

    protected $sql;

    protected $adapter;


    public function __construct(TableGateway $gateway)
    {
        $gateway instanceof TableGateway ? $this->table_gateway = $gateway : $this->table_gateway = null;

        $this->sql = new Sql($this->table_gateway->getAdapter());

        $this->adapter = $this->table_gateway->getAdapter();
    }


    public function deleteSession($username)
    {
        $delete = new Delete('sessions');

        $delete->where(array(
            'username'   => $username,
            'session_id' => session_id()
        ));


        $select = new Select('members');
        
        $select->columns(array('id'))
        ->where(array('username' => $username));
        
        $query = $this->adapter->query(
            $this->sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        $id = array();
        
        foreach ($query as $row) {
            $id[] = intval($row['id']);
        }
        
        // var_dump($id); exit;
        
        $delete_from_online = new Delete('group_members_online');
        
        $delete_from_online->where(array('member_id' => $id[0]));
        
        $delete_from_friends_online = new Delete('friends_online');
        
        $delete_from_friends_online->where(array('user_id' => $id[0]));
        
        $this->adapter->query(
            $this->sql->buildSqlString($delete_from_online),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        $this->adapter->query(
            $this->sql->buildSqlString($delete_from_friends_online),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        $this->adapter->query(
            $this->sql->buildSqlString($delete),
            Adapter::QUERY_MODE_EXECUTE
        );

        return true;
    }
}