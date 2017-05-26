<?php
namespace Members\Model\Classes;

use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\Db\Adapter\Adapter;
use Members\Model\Classes\Exceptions\EventsException;
use Members\Model\Filters\CreateEvent;

class Events extends Profile
{

    /**
     *
     * @var array
     */
    public static $created_event_details = array();

    /**
     * Creates a event
     *
     * @param CreateEvent $event            
     * @throws EventsException
     * @return bool
     */
    public static function createEvent(CreateEvent $event)
    {
        // assign data to array
        // for insertion
        $holder = array(
            'member_id' => parent::getUserId()['id'],
            'event_name' => $event->event_name,
            'event_description' => $event->event_description,
            'start_date' => $event->start_date,
            'end_date' => $event->end_date
        );
        
        // insert event details into the event table
        $insert = new Insert('events');
        
        $insert->columns(array(
            'member_id',
            'event_name',
            'event_description',
            'start_date',
            'end_date'
        ))->values(array(
            'member_id' => $holder['member_id'],
            'event_name' => $holder['event_name'],
            'event_description' => $holder['event_description'],
            'start_date' => $holder['start_date'],
            'end_date' => $holder['end_date']
        ));
        
        $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($insert), Adapter::QUERY_MODE_EXECUTE);
        
        if (count($query) > 0) {
            return true;
        } else {
            throw new EventsException("Error creating the event, please try again.");
        }
    }

    /**
     * Allows for editing of an event
     * 
     * @param int $event_id            
     * @param array $event_edits            
     * @throws EventsException
     * @return boolean
     */
    public static function editEvent($event_id, array $event_edits)
    {
        if (empty($event_id)) {
            throw new EventsException("Event id cannot be left empty.");
        } else {
            // locate the event by the event id passed in $event_id
            $select = new Select('events');
            
            $select->columns(array(
                '*'
            ))->where(array(
                'id' => intval($event_id)
            ));
            
            $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($select), Adapter::QUERY_MODE_EXECUTE);
            
            if ($query->count() > 0) {
                // rows were found
                // grab them and handle any updating
                $row_set = array();
                
                foreach ($query as $rows) {
                    $row_set[] = array(
                        'event_name' => $rows['event_name'],
                        'event_description' => $rows['event_description'],
                        'start_date' => $rows['start_date'],
                        'end_date' => $rows['end_date']
                    );
                }
                
                if (count($event_edits) > 0) {
                    // loop through the edits provided and update accordingly
                    $updated_event = array();
                    
                    foreach ($event_edits as $key => $value) {
                        $updated_event[$key] = $value;
                    }
                    
                    // check if the updates haven't changed from the original
                    // if not, keep the original
                    // if so, use updated ones
                    $update = new Update('events');
                    
                    $data = array(
                        'event_name' => $row_set['event_name'] == $updated_event['event_name'] ? $row_set['event_name'] : $updated_event['event_name'],
                        'event_description' => $row_set['event_description'] == $updated_event['event_description'] ? $row_set['event_description'] : $updated_event['event_description'],
                        'start_date' => $row_set['start_date'] == $updated_event['start_date'] ? $row_set['start_date'] : date('Y-m-d H:i:s', strtotime($updated_event['start_date'])),
                        'end_date' => $row_set['end_date'] == $updated_event['end_date'] ? $row_set['end_date'] : date('Y-m-d H:i:s', strtotime($updated_event['end_date']))
                    );
                    
                    $update->set(array(
                        'event_name' => $data['event_name'],
                        'event_description' => $data['event_description'],
                        'start_date' => $data['start_date'],
                        'end_date' => $data['end_date']
                    ))->where(array(
                        'id' => intval($event_id)
                    ));
                    
                    $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($update), Adapter::QUERY_MODE_EXECUTE);
                    
                    // check to be sure the query executed okay
                    if ($query->count() > 0) {
                        return true;
                    } else {
                        // query wasn't executed
                        // throw events exception
                        throw new EventsException("Error updating the event, please try again.");
                    }
                } else {
                    throw new EventsException(sprintf("No edits to the event were passed to %s", $row_set['event_name']));
                }
            } else {
                throw new EventsException(sprintf("No events were found with the event id %d", $event_id));
            }
        }
    }

    /**
     * Gets the events the user is a part of or has created (first 5)
     * 
     * @return array
     */
    public static function viewEvents()
    {
        $connection = parent::$sql->getAdapter()
            ->getDriver()
            ->getConnection();
        
        $query = $connection->execute("SELECT events.id AS event_id, events.event_name AS ename, events.event_description AS event_desc,
                                       events.start_date AS sdate, events.end_date AS edate FROM events
                                       INNER JOIN members ON events.member_id = members.id
                                       WHERE members.id = " . parent::getUserId()['id'] . " ORDER BY events.id LIMIT 5");
        
        if (count($query) > 0) {
            $events_holder = array();
            
            foreach ($query as $key => $value) {
                $events_holder[$key] = $value;
            }
            
            return $events_holder;
        }
    }

    public static function viewAllEvents()
    {
        $connection = parent::$sql->getAdapter()
            ->getDriver()
            ->getConnection();
        
        $query = $connection->execute("SELECT events.id AS event_id, events.event_name AS ename, events.event_description AS event_desc,
                                       events.start_date AS sdate, events.end_date AS edate FROM events
                                       INNER JOIN members ON events.member_id = members.id
                                       WHERE members.id = " . parent::getUserId()['id'] . " ORDER BY events.id");
        
        if (count($query) > 0) {
            $events_holder = array();
            
            foreach ($query as $key => $value) {
                $events_holder[$key] = $value;
            }
            
            return $events_holder;
        }
    }
}