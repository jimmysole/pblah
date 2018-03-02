<?php

namespace Members\Model\Interfaces;

use Members\Model\Filters\CreateEvent;
use Members\Model\Exceptions\EventsException;


interface EventsInterface
{
    /**
     * Creates a event
     * 
     * @param CreateEvent $event
     * @throws EventsException
     * @return bool
     */
    public function createEvent(CreateEvent $event);
    
    
    /**
     * Lets a user edit an event
     * 
     * @param int $event_id
     * @param array $event_edits
     * @throws EventsException
     * @return bool
     */
    public function editEvent($event_id, array $event_edits);
    
    
    /**
     * Gets the event the user is a part of or has created (first 5)
     * 
     * @throws EventsException
     * @return array
     */
    public function viewEvents();
    
    
    /**
     * Shows all events for the user
     * 
     * @throws EventsException
     * @return array
     */
    public function viewAllEvents();
    
    
    /**
     * Gets the events that the user is not a part of
     * 
     * @throws EventsException
     * @return string[]
     */
    public function getOtherEvents();
}