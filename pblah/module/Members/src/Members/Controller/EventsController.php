<?php

namespace Members\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Members\Form\CreateEventForm;
use Members\Model\Filters\CreateEvent;
use Members\Model\Classes\Exceptions\EventsException;



class EventsController extends AbstractActionController
{
    public $events_service;
    
    
    public function indexAction()
    {
        return new ViewModel(array('events' => $this->getEventService()->view()));
    }
    
    
    public function eventhomeAction()
    {
        $id = $this->params()->fromRoute('id', 0);
        
        if (0 === $id) {
            return $this->redirect()->toRoute('members/events', array('action' => 'index'));
        }
        
        if (!$this->getEventService()->getEventInformation($id)) {
            return $this->redirect()->toRoute('members/events', array('action' => 'index'));
        }
        
        return new ViewModel(array('event_info' => $this->getEventService()->getEventInformation($id)));
    }
    
    
    public function createeventAction()
    {
        $form = new CreateEventForm();
        
        return new ViewModel(array(
            'form' => $form
        ));
    }
    
    
    public function ceventAction()
    {
        $form = new CreateEventForm();
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $create_event = new CreateEvent();
            
            $form->setInputFilter($create_event->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $create_event->exchangeArray($form->getData());
                
                try {
                    if (false !== $this->getEventService()->createAEvent($create_event)) {
                        $this->flashMessenger()->addSuccessMessage("Event created okay!");
                        
                        return $this->redirect()->toUrl('create-event-success');
                    }
                } catch (EventsException $e) {
                    $this->flashMessenger()->addErrorMessage((string)$e->getMessage());
                    
                    return $this->redirect()->toUrl('create-event-failure');
                }
            } else {
                $messages = $form->getMessages();
                
                $this->flashMessenger()->addErrorMessage("Invalid form. Please correct this and try again.");
                
                return $this->redirect()->toUrl('create-event-failure');
            }
        }
    }
    
    
    public function createeventsuccessAction()
    {
        return;
    }
    
    
    public function createeventfailureAction()
    {
        return;
    }
    
    
    public function vieweventsAction()
    {
        try {
            $events = $this->getEventService()->viewOtherEvents();
            
            return new ViewModel(array('events' => $events));
        } catch (EventsException $e) {
            $this->flashMessenger()->addErrorMessage((string)$e->getMessage());
            
            return $this->redirect()->toUrl('no-events');
        }
    }
    
    
    public function noeventsAction()
    {
        return;
    }
    
    
    public function viewallAction()
    {
        return new ViewModel(array('events' => $this->getEventService()->viewAllEvents()));
    }
    
    
    
    public function getEventService()
    {
        if (!$this->events_service) {
            $this->events_service = $this->getServiceLocator()->get('Members\Model\EventsModel');
        }
        
        return $this->events_service;
    }
}