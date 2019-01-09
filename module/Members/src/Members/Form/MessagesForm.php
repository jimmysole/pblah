<?php

namespace Members\Form;

use Zend\Form\Form;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Text;
use Zend\Form\Element\Textarea;
use Zend\Form\Element\Submit;


class MessagesForm extends Form
{
    public function __construct($name = null) 
    {
        parent::__construct('pblah_send-message');
        
        $this->setAttribute('method', 'post')
        ->setAttribute('data-role', 'form')
        ->setAttribute('autocomplete', false);
        
        $this->add(array(
            'name' => 'subject',
            'type' => Text::class,
            'options' => array(
                'label' => 'Subject',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
            ),
            
            'attributes' => array(
                'placeholder'  => 'Subject',
                'id'           => 'subject',
                'class'        => 'w3-input w3-border w3-round',
                'autocomplete' => '',
            ),
        ));
        
        
        $this->add(array(
            'name' => 'message',
            'type' => Textarea::class,
            'options' => array(
                'label' => 'Message',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
            ),
            
            'attributes' => array(
                'rows'         => 10,
                'cols'         => 75,
                'placeholder'  => 'Message',
                'id'           => 'message',
                'class'        => 'w3-input w3-border w3-round',
            ),
        ));
        
        
        $this->add(new Csrf('csrf_security'));
        
        
        $this->add(array(
            'name' => 'submit',
            'type' => Submit::class,
            
            'attributes' => array(
                'id'    => 'submit',
                'class' =>  'w3-btn w3-white w3-border w3-border-blue w3-round w3-left',
                'value' => 'Send Message',
            ),
        ));
    }
}