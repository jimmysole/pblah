<?php

namespace Members\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;


use Members\Model\Interfaces\StatusInterface;
use Members\Model\Exceptions\StatusException;


class StatusModel implements StatusInterface
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
     * @var Select
     */
    public $select;
    
    /**
     * @var Sql
     */
    public $sql;
    
    
    /**
     * Constructor method for StatusModel class
     * @param TableGateway $gateway
     * @param string $user
     */
    public function __construct(TableGateway $gateway, $user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        $this->user = $user;
        
        $this->sql = new Sql($this->gateway->getAdapter());
        
        $this->select = new Select('members');
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\StatusInterface::postStatus()
     */
    public function postStatus($status)
    {
        if (empty($status)) {
            throw new StatusException("Status text cannot be left empty.");
        } else {
            // get the user's id based on $this->user
            $this->select->columns(array('id'))
            ->where(array('username' => $this->user));
            
            $query = $this->sql->getAdapter()->query(
                $this->sql->buildSqlString($this->select),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if ($query->count() > 0) {
                foreach ($query as $result) {
                    $row = $result;
                }
                
                $select = $this->gateway->select(array('id' => $row['id']));
                
                if ($select->count() > 0) {
                    // update status
                    $update_data = array(
                        'status' => $status,
                    );
                    
                    $update = $this->gateway->update($update_data, array('id' => $row['id']));
                    
                    if ($update > 0) {
                        return true;
                    } else {
                        throw new StatusException("Error posting status.");
                    }
                } else {
                    // insert status
                    $insert_data = array(
                        'id'     => $row['id'],
                        'status' => $row['status'],
                    );
                    
                    $insert = $this->gateway->insert($insert_data);
                    
                    if ($insert > 0) {
                        return true;
                    } else {
                        throw new StatusException("Error posting status.");
                    }
                }
            } else {
                throw new StatusException("Invalid username passed.");
            }
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\StatusInterface::getStatus()
     */
    public function getStatus()
    {
        // get user's status based on $this->user
        $this->select->columns(array('id'))
        ->where(array('username' => $this->user));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($this->select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() < 0) {
            throw new StatusException(sprintf("Status was not found for %s", $this->user));
        }
        
        // get the current status for the user
        foreach ($query as $result) {
            $row = $result;
        }
        
        $get_status = $this->gateway->select(array('id' => $row['id']));
        
        if ($get_status->count() > 0) {
            foreach ($get_status as $user_status) {
                $status_row = $user_status;
            }
            
            return $status_row;
        } else {
            throw new StatusException("Error retrieving status.");
        }
    }
}