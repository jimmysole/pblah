<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\MembersException;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;


class Members
{
    public static $table_gateway;
    
    public static $sql;
    
    
    /**
     * Posts the current status for the user 
     * @param mixed $status
     * @throws MembersException
     * @return boolean
     */
    public function postStatus($status)
    {
        if (empty($status)) {
            throw new MembersException("Status text cannot be left empty.");
        } else {
            
        }
        
        return false;
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