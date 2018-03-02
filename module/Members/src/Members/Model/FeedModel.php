<?php

namespace Members\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;

use Members\Model\Interfaces\FeedInterface;
use Members\Model\Exceptions\FeedException;


class FeedModel implements FeedInterface
{
    /**
     * @var TableGateway
     */
    public $gateway;
    
    /**
     * @var string
     */
    public $user;
    
    /**
     * @var Sql
     */
    public $sql;
    
    /**
     * @var Select
     */
    public $select;
    
    
    /**
     * Constructor method for FeedModel class
     * @param TableGateway $gateway
     * @param string $user
     */
    public function __construct(TableGateway $gateway, $user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        $this->select = new Select();
        
        $this->sql = new Sql($this->gateway->getAdapter());
        
        $this->user =  $user;
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\FeedInterface::listFriendsStatus()
     */
    public function listFriendsStatus()
    {
        try {
            // get the friend id based on user id
            // and then compare the friend id to the id in status table
            $this->select->columns(array('friend_id'))
            ->from('friends')
            ->where(array('user_id' => $this->getUserId()['id']));
            
            $query = $this->sql->getAdapter()->query(
                $this->sql->buildSqlString($this->select),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if ($query->count() > 0) {
                foreach ($query as $result) {
                    $friend_id = $result;
                }
                
                $status_query = $this->gateway->select(array('id' => $friend_id));
                
                if ($status_query->count() > 0) {
                    $status = array();
                    
                    // get all the statuses
                    foreach ($status_query as $rows) {
                        $status[] = $rows;    
                    }
                    
                    return $status;
                } else {
                    throw new FeedException("No status was found for your friends.");
                }
            } else {
                throw new FeedException(sprintf("Could not locate any friends for %s", $this->user));
            }
        } catch (FeedException $e) {
            return json_encode(array('fail' => $e->getMessage()));
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\FeedInterface::hideFriendsStatus()
     */
    public function hideFriendsStatus($friend_id)
    {
        
    }
    
    
    
    /**
     * Grabs the user id for the user
     * 
     * @return int|boolean
     */
    public function getUserId()
    {
        $this->select->columns(array('*'))
        ->from('members')
        ->where(array('username' => $this->user));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            $row = array();
            
            foreach ($query as $result) {
                $row[] = $result;
            }
            
            return $row;
        }
        
        return false;
    }
}