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
        
        $this->user = $user;
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\FeedInterface::listFriendsStatus()
     */
    public function listFriendsStatus()
    {
        $user_id = $this->getUserId()['id'];
        
        // get the friend ids based on user id
        // and then compare the friend id to the id in status table
        $friend_query = new Select('friends');
        
        $friend_query->columns(array('friend_id'))
        ->where(array('user_id' => $user_id));  
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($friend_query),
            Adapter::QUERY_MODE_EXECUTE
        );
            
        if ($query->count() > 0) {
            $friend_id = array();
            
            foreach ($query as $result) {
                $friend_id[] = $result['friend_id'];
            }
            
            
            $status = new Select('status');
            
            $status->columns(array('status'))
            ->where(array('id' => $friend_id)); 
            
            $status_query = $this->sql->getAdapter()->query(
                $this->sql->buildSqlString($status),
                Adapter::QUERY_MODE_EXECUTE
            );
                
            if ($status_query->count() > 0) {
                // check if a image was used
                $members = new Select('members');
                
                $members->columns(array('username'))
                ->where(array('id' => $friend_id));
                
                $image_query = $this->sql->getAdapter()->query(
                    $this->sql->buildSqlString($members),
                    Adapter::QUERY_MODE_EXECUTE
                );
                
                $images = array();
                
                if ($image_query->count() > 0) {
                    foreach ($image_query as $value) {
                        $status_dir = '/images/profile/' . $value['username'] . '/status/';
                        
                        $real_dir = getcwd() .  '/public/' . $status_dir;
                        
                        if (is_dir($real_dir)) {
                            // retrieve the image inside the status directory
                            foreach (array_diff(scandir($real_dir, 1), array('.', '..')) as $values) {
                                $images[] = $status_dir . $values;
                            }
                        }
                    }
                } else {
                    throw new FeedException("The user does not exist in the user table.");
                }
                
                $status = array();
                    
                // get all the statuses
                foreach ($status_query as $rows) {
                    $status[] = $rows['status'];    
                }
                    
                return array('username' => ucfirst($value['username']), 'status' => $status, 'images' => $images); 
            } else {
                throw new FeedException("No status was found for your friends.");
            }
         } else {
            throw new FeedException(sprintf("Could not locate any friends for %s", $this->user));
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
        $select = new Select('members');
        
        $select->columns(array('*'))
        ->where(array('username' => $this->user));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            foreach ($query as $result) {
                $row = $result;
            }
            
            return $row;
        }
        
        return false;
    }
}