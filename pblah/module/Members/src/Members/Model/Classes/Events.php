<?php
namespace Members\Model\Classes;

use Zend\Db\Sql\Insert;
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
            
            $event_name = !empty(self::$created_event_details['event_name']) ? self::$created_event_details['event_name'] : 'My Event';
            $event_desc = !empty(self::$created_event_details['event_desc']) ? self::$created_event_details['event_desc'] : 'My Event Description';
            $event_start_date = !empty(self::$created_event_details['event_start_date']) ? date('Y-m-d H:i:s', strtotime(self::$created_event_details['event_start_date'])) 
            : date('Y-m-d H:i:s', strtotime("now")); // if no start date provided, set today by default
            $event_end_date = !empty(self::$created_event_details['event_end_date']) ? date('Y-m-d H:i:s', strtotime(self::$created_event_details['event_end_date']))
            : date('Y-m-d H:i:s', strtotime("+1 week")); // if no end date provided, set a week by default
            
            
            $insert = new Insert('events');
            
            $insert->columns(array('member_id', 'event_name', 'event_description', 'start_date', 'end_date'))
            ->values(array('member_id' => $member_id, 'event_name' => $event_name, 'event_description' => $event_desc,
                'start_date' => $event_start_date, 'end_date' => $event_end_date
            ));
            
            $query = parent::$sql->getAdapter()->query(
                parent::$sql->buildSqlString($insert),
                Adapter::QUERY_MODE_EXECUTE
            );
            
            if ($query->count() > 0) {
                return true;
            } else {
                throw new EventsException("Error inserting event, please try again.");
            }
        } else {
            throw new EventsException("To create an event, you must fill out all the required event details.");
        }
    }
}