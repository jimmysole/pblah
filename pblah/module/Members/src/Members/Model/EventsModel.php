<?php

namespace Members\Model;

use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Adapter\Adapter;


use Members\Model\Filters\CreateEvent;
use Members\Model\Interfaces\EventsInterface;
use Members\Model\Exceptions\EventsException;



class EventsModel implements EventsInterface
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
     * @var ConnectionInterface
     */
    public $connection;
    
    
    /**
     * Constructor method for EventsModel class
     * @param TableGateway $gateway
     * @param string $user
     */
    public function __construct(TableGateway $gateway, $user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        $this->user = $user;
        
        $this->sql = new Sql($this->gateway->getAdapter());
        
        $this->select = new Select();
        
        $this->connection = $this->sql->getAdapter()->getDriver()->getConnection();
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\EventsInterface::createEvent()
     */
    public function createEvent(CreateEvent $event)
    {
        // assign data to array for insert into database
        $holder = array(
            'member_id'         => '',
            'event_name'        => $event->event_name,
            'event_description' => $event->event_description,
            'start_date'        => $event->start_date,
            'end_date'          => $event->end_date
        );
        
        if ($this->gateway->insert($holder) > 0) {
            return true;
        } else {
            throw new EventsException("Error creating the event, please try again.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\EventsInterface::editEvent()
     */
    public function editEvent($event_id, array $event_edits)
    {
        if (empty($event_id)) {
            throw new EventsException("Event id cannot be left empty");
        } else {
            // locate the event by the event id passed
            $select = $this->gateway->select(array('id' => intval($event_id)));
            
            if ($select->count() > 0) {
                // rows were found
                // grab them and handle any updating
                $rowset = array();
                
                foreach ($select as $rows) {
                    $rowset[] = array(
                        'event_name'        => $rows['event_name'],
                        'event_description' => $rows['event_description'],
                        'start_date'        => $rows['start_date'],
                        'end_date'          => $rows['end_date'],
                    );
                }
                
                // check the event edits
                if (count($event_edits) > 0) {
                    // loop through the edits provided and update accordingly
                    $updated_event = array();
                    
                    foreach ($event_edits as $key => $value) {
                        $updated_event[$key] = $value;
                    }
                    
                    // check if the updates haven't changed from the original
                    // if not, keep the original
                    // if so, use updated ones
                    $data = array(
                        'event_name'        => $rowset['event_name'] == $updated_event['event_name']               ? $rowset['event_name']        : $updated_event['event_name'],
                        'event_description' => $rowset['event_description'] == $updated_event['event_description'] ? $rowset['event_description'] : $updated_event['event_description'],
                        'start_date'        => $rowset['start_date'] == $updated_event['start_date']               ? $rowset['start_date']        : date('Y-m-d H:i:s', strtotime($updated_event['start_date'])),
                        'end_date'          => $rowset['end_date']   == $updated_event['end_date']                 ? $rowset['end_date']          : date('Y-m-d H:i:s', strtotime($updated_event['end_date'])),
                    );
                    
                    if ($this->gateway->update(array($data), array('id' => intval($event_id)))) {
                        return true;
                    } else {
                        throw new EventsException("Error updating the event, please try again.");
                    }
                } else {
                    throw new EventsException(sprintf("No edits to the event were passed to %s", $rowset['event_name']));
                }
            } else {
                throw new EventsException(sprintf("No events were found with the event id %d", $event_id));
            }
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\EventsInterface::viewEvents()
     */
    public function viewEvents()
    {
        $query = $this->connection->execute("SELECT events.id AS event_id, events.event_name AS ename, events.event_description AS event_desc,
                                             events.start_date AS sdate, events.end_date AS edate FROM events
                                             INNER JOIN members ON events.member_id = members.id
                                             WHERE members.id = " . $this->getUserId()['id'] . " ORDER BY events.id LIMIT 5");
        
        if ($query->count() > 0) {
            $events_holder = array();
            
            foreach ($query as $key => $value) {
                $events_holder[$key] = $value;
            }
            
            return $events_holder;
        } else {
            throw new EventsException("No events were found.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\EventsInterface::viewAllEvents()
     */
    public function viewAllEvents()
    {
        $query = $this->connection->execute("SELECT events.id AS event_id, events.event_name AS ename, events.event_description AS event_desc,
                                             events.start_date AS sdate, events.end_date AS edate FROM events
                                             INNER JOIN members ON events.member_id = members.id
                                             WHERE members.id = " . $this->getUserId()['id'] . " ORDER BY events.id");
        
        if ($query->count() > 0) {
            $event_id   = array();
            $event_name = array();
            
            foreach ($query as $value) {
                // list the event id and name
                $event_id[]   = $value['event_id'];
                $event_name[] = $value['ename'];
            }
            
            return array('event_id' => $event_id, 'event_name' => $event_name);
        } else {
            throw new EventsException("No events either created by you or that you are a part of were found.");
        }
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Members\Model\Interfaces\EventsInterface::getOtherEvents()
     */
    public function getOtherEvents()
    {
        $select = new Select('events');
        $select->columns(array('id', 'member_id', 'event_name'));
        $select->where('member_id != ' . $this->getUserId()['id']);
        
        $query = $this->gateway->selectWith($select);
        
        if ($query->count() > 0) {
            $all_events_holder = array();
            
            foreach ($query as $key => $value) {
                $all_events_holder[$key] = $value;
            }
            
            return $all_events_holder;
        } else {
            throw new EventsException("No other events were found.");
        }
    }
    
    
    /**
     * Gets the user id
     * 
     * @return ResultSet|boolean
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
            foreach ($query as $result) {
                $row = $result;
            }
            
            return $row;
        }
        
        return false;
    }
}