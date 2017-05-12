<?php

namespace Members\Model\Classes;

use Members\Model\Classes\Exceptions\EventsException;


class Events
{

   public static function createEvent(array $event_details)
   {
       if (count($event_details, 1) > 0) {
           
       } else {
           throw new EventsException("To create an event, you must fill out all the required event details");
       }
   }
}