<?php


namespace Members\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

use Members\Model\Classes\Events;
use Members\Model\Filters\CreateEvent;



class EventsModel extends Events
{
    /**
     * @var TableGateway
     */
    public $gateway;
    
    /**
     * Constructor method for EventsModel class
     * @param TableGateway $gateway
     * @param string $user
     */
    public function __construct(TableGateway $gateway, $user)
    {
        $this->gateway = $gateway instanceof TableGateway ? $gateway : null;
        
        parent::getTableGateway($this->gateway);
        parent::getSQLClass();
        parent::setUser($user);
    }
    
    
    /**
     * Creates a event 
     * @param CreateEvent $event
     * @return boolean
     */
    public function createAEvent(CreateEvent $event)
    {
        return parent::createEvent($event);
    }
    
    
    /**
     * Retrieves events associated with the user
     * @return array
     */
    public function view()
    {
        return parent::viewEvents();
    }
    
    
    /**
     * Retrieves all the events
     * @return string[]
     */
    public function viewAll()
    {
        return parent::viewAllEvents();
    }
    
    
    /**
     * Gets the events that the user is not a part of
     * @return string[]
     */
    public function viewOtherEvents()
    {
        return parent::getOtherEvents();
    }
    
    
    /**
     * Gets all the event information
     * @param int $event_id
     * @return boolean|array[]|ArrayObject[]|NULL[]
     */
    public function getEventInformation($event_id)
    {
        // get the events
        $fetch = $this->gateway->select(array(
            'id' => $event_id
        ));
        
        $row = $fetch->current();
        
        if (!$row) {
            return false;
        }
        
        return array(
            'info' => $row,
        );
    }
}