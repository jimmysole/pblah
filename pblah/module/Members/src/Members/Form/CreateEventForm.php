<?php

namespace Members\Form;

use Zend\Form\Form;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Date;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;
use Zend\Form\Element\Submit;

class CreateEventForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('pblah_create-event');
        
        // set the attributes for the form
        $this->setAttribute('method', 'post')
        ->setAttribute('data-role', 'form')
        ->setAttribute('autocomplete', false);
        
        
        // make the form elements
        $this->add(array(
            'name' => 'event-name',
            'type' => Text::class,
            'options' => array(
                'label' => 'Event Name',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
            ),
            
            'attributes' => array(
                'placeholder'  => 'Event Name',
                'id'           => 'event-name',
                'class'        => 'w3-input w3-border w3-round',
                'autocomplete' => 'new-event-name', // google chrome hack
            ),
        ));
        
        
        $this->add(array(
            'name' => 'event-description',
            'type' => Textarea::class,
            'options' => array(
                'label' => 'Event Description',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
            ),
            
            'attributes' => array(
                'rows'  => 10,
                'cols'  => 75,
                'id'    => 'event-description',
                'class' => 'w3-input w3-border w3-round',
                'placeholder' => 'Event Description',
            ),
        ));
        
        
        $this->add(array(
            'name' => 'start-date',
            'type' => Date::class,
            'options' => array(
                'label' => 'Start Date',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
                'format' => 'Y-m-d',
            ),
            
            'attributes' => array(
                'id'    => 'start-date',
                'class' => 'w3-input w3-border w3-round',
                'min'   => date('Y-m-d', strtotime("now")),
                'max'   => date('Y-m-d', strtotime("+1 week")),
                'step'  => '1',
            ),
        ));
        
        $this->add(array(
            'name' => 'end-date',
            'type' => Date::class,
            'options' => array(
                'label' => 'End Date',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
                'format' => 'Y-m-d',
            ),
            
            'attributes' => array(
                'id'    => 'end-date',
                'class' => 'w3-input w3-border w3-round',
                'min'   => date('Y-m-d', strtotime("+2 days")),
                'max'   => date('Y-m-d', strtotime("+1 month")),
                'step'  => '1',
            ),
        ));
        
        
        
        $this->add(new Csrf('csrf_security'));
        
        
        $this->add(array(
            'name' => 'submit',
            'type' => Submit::class,
            
            'attributes' => array(
                'id'    => 'submit',
                'class' =>  'w3-btn w3-white w3-border w3-border-blue w3-round w3-left',
                'value' => 'Create Event',
            ),
        ));
    }
}