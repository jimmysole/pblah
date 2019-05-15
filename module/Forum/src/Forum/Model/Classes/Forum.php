<?php

namespace Forum\Model\Classes;

use Forum\Model\Interfaces\ForumInterface;


use Zend\Db\Sql\Sql;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Delete;
use Zend\Db\Adapter\Adapter;
use Forum\Model\Exceptions\ForumException;


class Forum implements ForumInterface
{
    private $sql;
    private $select;
    private $insert;
    private $update;
    private $delete;
    
    
    public function __construct(TableGateway $gateway) 
    {
        $this->sql = new Sql($gateway->getAdapter());   
        
        $this->select = new Select();
        
        $this->insert = new Insert();
        
        $this->update = new Update();
        
        $this->delete = new Delete();
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Forum\Model\Interfaces\ForumInterface::getMessages()
     */
    public function getMessages($board_id)
    {
        $select = $this->select->columns(array('messages'))
        ->from('board_messages')
        ->where(array('board_id' => $board_id));
        
        $query = $this->sql->getAdapter()->query(
            $this->sql->buildSqlString($select),
            Adapter::QUERY_MODE_EXECUTE
        );
        
        if ($query->count() > 0) {
            // return all messages as an array
            $messages = [];
            
            foreach ($query as $row) {
                $messages = $row;
            }
            
            return $messages;
        } else {
            throw new ForumException("No messages were found.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Forum\Model\Interfaces\ForumInterface::getTopics()
     */
    public function getTopics()
    {
        
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Forum\Model\Interfaces\ForumInterface::getNumOfReplies()
     */
    public function getNumOfReplies()
    {
        
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Forum\Model\Interfaces\ForumInterface::getRepliesText()
     */
    public function getRepliesText()
    {
        
    }
    
    
    public function __destruct()
    {
        $this->sql = null;
    }
}