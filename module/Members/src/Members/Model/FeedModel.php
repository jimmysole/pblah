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
        
        // get the friend statuses of the logged in user
        $connection = $this->sql->getAdapter()->getDriver()->getConnection();
        
        $query = $connection->execute("SELECT status.id, status, time_status, members.username FROM status
            INNER JOIN friends ON friends.friend_id = status.id 
            INNER JOIN members ON members.id = status.id
            WHERE friends.user_id = " . $this->getUserId()['id']);
        
        if ($query->count() > 0) {
            $status_holder = array();
            
            foreach ($query as $key => $value) {
                $status_holder[$key] = $value;
            }
            
            for ($i = 0; $i<count($status_holder); $i++) {
                $status_dir = '/images/profile/' . $status_holder[$i]['username'] . '/status/' . $status_holder[$i]['time_status'] . '/';
                
                $real_dir = getcwd() . '/public/' . $status_dir;
                
                if (is_dir($real_dir)) {
                    foreach (array_diff(scandir($real_dir, 1), array('.', '..')) as $images) {
                        // add the images to the $status_holder array
                        array_merge($status_holder, array('images' => $images));
                    }
                } 
            }
               
            return $status_holder;
        } else {
            throw new FeedException("No friend statuses were found.");
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
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\FeedInterface::listIndividualStatus()
     */
    public function listIndividualStatus()
    {
        $user_id = $this->getUserId()['id'];
        
        // base the status user id on $user_id
        // retrieved from $this->getUserId()
        $select = new Select('members');
        
        $select->columns(array('*'))
        ->where(array('id' => $user_id));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            $select = $this->gateway->select(array('id' => $user_id));
            
            $rowset = $select->current();
            
            foreach ($query as $value) {
                $status_dir = '/images/profile/' . $value['username'] . '/status/' . $rowset['time_status'] . '/';
                
                $real_dir = getcwd() .  '/public/' . $status_dir;
                
                if (is_dir($real_dir)) {
                    // retrieve the image inside the status directory
                    foreach (array_diff(scandir($real_dir, 1), array('.', '..')) as $values) {
                        $images[] = $status_dir . $values;
                    }
                }
            }
            
            // get the status
            $status_query = new Select('status');
            
            $status_query->columns(array('status'))
            ->where(array('id' => $user_id));
            
            $query = $this->sql->getAdapter()->query(
                $this->sql->buildSqlString($status_query),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            $status = null;
            
            foreach ($query as $row) {
                $status = $row['status'];
            }
            
            return array('username' => ucfirst($value['username']), 'status' => $status, 'images' => $images);
        } else {
            throw new FeedException(sprintf("Could not locate %s", $this->user));
        }
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