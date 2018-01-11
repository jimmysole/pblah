<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\StatusException;

use Zend\Db\TableGateway\TableGateway;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;

use Zend\Db\Adapter\Adapter;


class Members
{
    /**
     * @var string
     */
    public static $user;
    
    /**
     * @var TableGateway
     */
    public static $table_gateway;
    
    /**
     * @var Sql
     */
    public static $sql;
    
    
    /**
     * Setter method
     * @param string $user
     * @return \Members\Profile
     */
    public static function setUser($user)
    {
        self::$user = $user;
        
        return new self();
    }
    
    
    /**
     * Getter method
     * @return string
     */
    public static function getUser()
    {
        return self::$user;
    }
    
    
    /**
     * Gets the user id
     * @return ResultSet|boolean
     */
    public static function getUserId()
    {
        $select = new Select('members');
        
        $select->columns(array('*'))
        ->where(array('username' => self::getUser()));
        
        
        $query = self::getSQLClass()->getAdapter()->query(
            self::$sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if (count($query) > 0) {
            foreach ($query as $result) {
                $row = $result;
            }
            
            return $row;
        }
        
        return false;
    }
    
    
    /**
     * Posts the current status for the user 
     * @param mixed $status
     * @param mixed $user
     * @throws MembersException
     * @return boolean
     */
    public function postStatus($status)
    {
        if (empty($status)) {
            throw new StatusException("Status text cannot be left empty.");
        } else {
            // get the user's id based on $user
            $select = new Select('members');
            
            $select->columns(array('id'))
            ->where(array('username' => self::getUser()));
            
            $query = self::getSQLClass()->getAdapter()->query(
                self::$sql->buildSqlString($select),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if (count($query) > 0) {
                foreach ($query as $result) {
                    $row = $result;
                }
                
                $select_status = new Select('status');
                
                $select_status->columns(array('id', 'status'))
                ->where(array('id' => $row['id']));
                
                $query = self::getSQLClass()->getAdapter()->query(
                    self::$sql->buildSqlString($select_status),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                if (count($query) > 0) {
                    // do update
                    $update = new Update('status');
                    
                    $update->set(array('status' => $status))
                    ->where(array('id' => $row['id']));
                    
                    $query = self::getSQLClass()->getAdapter()->query(
                        self::$sql->buildSqlString($update),
                        Adapter::QUERY_MODE_EXECUTE
                    );
                    
                    if (!$query) {
                        throw new StatusException("Error posting status.");
                    }
                    
                    return true;
                } else {
                    // use $row['id'] to insert the status
                    $insert = new Insert('status');
                
                    $insert->columns(array('id', 'status'))
                    ->values(array('id' => $row['id'], 'status' => $status));
                
                    $query = self::getSQLClass()->getAdapter()->query(
                        self::$sql->buildSqlString($insert),
                        Adapter::QUERY_MODE_EXECUTE
                    );
                
                    if (!$query) {
                        throw new StatusException("Error posting status.");
                    } 
                
                    return true;
                }
            } else {
                throw new StatusException("Invalid username passed.");
            }
        }
        
        return false;
    }
    
    
    public function getStatus($user)
    {
        if (empty($user)) {
            throw new StatusException("Invalid username passed.");
        } else {
            // get the user id based on $user
            $select = new Select('members');
            
            $select->columns(array('id'))
            ->where(array('username' => $user));
            
            $query = self::getSQLClass()->getAdapter()->query(
                self::$sql->buildSqlString($select),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if (!$query) {
                throw new StatusException("Status for $user was not found.");
            }
            
            // get the current status for the user
            foreach ($query as $result) {
                $row = $result;
            }
            
            $get_status = new Select('status');
            
            $get_status->columns(array('status'))
            ->where(array('id' => $row['id']));
            
            $query = self::getSQLClass()->getAdapter()->query(
                self::$sql->buildSqlString($get_status),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if (count($query) <= 0) {
                throw new StatusException("Looks like you haven't posted a status yet!");
            }
            
            foreach ($query as $user_status) {
                $status_row = $user_status;
            }
            
            return $status_row;
        }
    }
    
    
    
    
    /**
     * Gets table gateway instance
     * @param TableGateway $gateway
     * @return NULL|\Zend\Db\TableGateway\TableGateway
     */
    public static function getTableGateway(TableGateway $gateway)
    {
        self::$table_gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        return self::$table_gateway;
    }
    
    
    /**
     * gets sql instance
     * @return \Zend\Db\Sql\Sql
     */
    public static function getSQLClass()
    {
        self::$sql = new Sql(self::$table_gateway->getAdapter());
        
        return self::$sql;
    }
}