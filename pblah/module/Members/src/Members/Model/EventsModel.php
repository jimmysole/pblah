<?php


namespace Members\Model;

use Zend\Db\TableGateway\TableGateway;

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
    
    
    public function createAEvent(CreateEvent $event)
    {
        return parent::createEvent($event);
    }
    
    
    public function viewEvents()
    {
        
    }
}