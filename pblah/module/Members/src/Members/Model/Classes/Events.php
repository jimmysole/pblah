<?php
namespace Members\Model\Classes;

use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Update;
use Zend\Db\Adapter\Adapter;
use Members\Model\Classes\Exceptions\EventsException;

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
     * @param array $event_details            
     * @throws EventsException
     * @return bool
     */
    public static function createEvent(array $event_details)
    {
        if (count($event_details, 1) > 0) {
            foreach ($event_details as $key => $value) {
                self::$created_event_details[$key] = $value;
            }
            
            // grab the event details
            // (event_name, event_description, start_date and end_date)
            // verify they are valid and then insert into the database
            $member_id = parent::getUserId()['id'];
            
            $event_name = ! empty(self::$created_event_details['event_name']) ? self::$created_event_details['event_name'] : 'My Event';
            $event_desc = ! empty(self::$created_event_details['event_desc']) ? self::$created_event_details['event_desc'] : 'My Event Description';
            $event_start_date = ! empty(self::$created_event_details['event_start_date']) ? date('Y-m-d H:i:s', strtotime(self::$created_event_details['event_start_date'])) : date('Y-m-d H:i:s', strtotime("now")); // if no start date provided, set today by default
            $event_end_date = ! empty(self::$created_event_details['event_end_date']) ? date('Y-m-d H:i:s', strtotime(self::$created_event_details['event_end_date'])) : date('Y-m-d H:i:s', strtotime("+1 week")); // if no end date provided, set a week by default
            
            $insert = new Insert('events');
            
            $insert->columns(array(
                'member_id',
                'event_name',
                'event_description',
                'start_date',
                'end_date'
            ))->values(array(
                'member_id' => $member_id,
                'event_name' => $event_name,
                'event_description' => $event_desc,
                'start_date' => $event_start_date,
                'end_date' => $event_end_date
            ));
            
            $query = parent::$sql->getAdapter()->query(parent::$sql->buildSqlString($insert), Adapter::QUERY_MODE_EXECUTE);
            
            if ($query->count() > 0) {
                return true;
            } else {
                throw new EventsException("Error inserting event, please try again.");
            }
        } else {
            throw new EventsException("To create an event, you must fill out all the required event details.");
        }
    }

    /**
     * Allows for editing of an event
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
                    
                    
                    $query = parent::$sql->getAdapter()->query(
                        parent::$sql->buildSqlString($update),
                        Adapter::QUERY_MODE_EXECUTE
                    );
                    
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
}