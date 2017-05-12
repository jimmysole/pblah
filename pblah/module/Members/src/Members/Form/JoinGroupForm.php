<?php

namespace Members\Form;

use Zend\Form\Form;
use Zend\Form\Element\Csrf;


class JoinGroupForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('pblah-join-group');
        
        // set the form attributes
        $this->setAttribute('method', 'post')
        ->setAttribute('data-role', 'form')
        ->setAttribute('autocomplete', false);
        
        
        // make the form elements
        $this->add(array(
            'name' => 'first-name',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'First Name',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
            ),
            
            'attributes' => array(
                'id'     => 'first-name',
                'class'  => 'w3-input w3-border w3-round',
                'placeholder'  => 'First Name',
                'autocomplete' => 'm-first-name', // google chrome hack
            ),
        ));
        
        
        $this->add(array(
            'name' => 'last-name',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Last Name',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
            ),
                
            'attributes' => array(
                'id'    => 'last-name',
                'class' => 'w3-input w3-border w3-round',
                'placeholder'  => 'Last Name',
                'autocomplete' => 'm-last-name', // google chrome hack
            ),
        ));
        
        
        $this->add(array(
            'name' => 'age',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Age',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
            ),
                
            'attributes' => array(
                'id'    => 'age',
                'class' => 'w3-input w3-border w3-round',
                'placeholder'  => 'Age',
                'autocomplete' => 'm-age', // google chrome hack
            ),
        ));
        
        
        $this->add(array(
            'name' => 'message',
            'type' => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Message',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
            ),
            
            'attributes' => array(
                'rows'  => 10,
                'cols'  => 75,
                'id'    => 'message',
                'class' => 'w3-input w3-border w3-round',
                'placeholder' => 'Message',
            ),
        ));
        
        
        
        $this->add(new Csrf('csrf_security'));
        
        
        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            
            'attributes' => array(
                'id'    => 'submit',
                'class' => 'w3-btn w3-white w3-border w3-border-blue w3-round w3-left',
                'value' => 'Send Join Request'
            ),
        ));
    }
}