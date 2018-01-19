<?php

namespace Members\Model\Interfaces;

use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;


interface MembersInterface 
{
    /**
     * Gets Zend\Db\TableGateway\TableGateway Instance
     *
     * @param TableGateway $gateway
     * @return TableGateway
     */
    public function getTableGateway(TableGateway $gateway);
    
    
    /**
     * Gets Zend\Db\Sql\Sql Instance
     *
     * @param Sql $sql
     * @return Sql
     */
    public function getSQLClass(Sql $sql);
    
    
    /**
     * Sets the current user
     * 
     * @param string $user
     * @return MembersInterface
     */
    public function setUser($user);
    
    
    /**
     * Gets the current user
     * 
     * @return string
     */
    public function getUser();
    
    
    /**
     * Gets the current user's id
     * 
     * @return ResultSet|boolean
     */
    public function getUserId();
    
    
    /**
     * Posts the current status for the user
     * 
     * @param string $status
     * @throws StatusException
     */
    public function postStatus($status);
    
    
    /**
     * Gets the current status of the user
     * 
     * @param string $user
     * @throws StatusException
     * @return array
     */
    public function getStatus($user);
}