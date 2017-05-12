<?php

namespace Members\Form;


use Zend\Form\Form;
use Zend\Form\Element\Csrf;


class CreateGroupForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('pblah-create-group');
        
        // set the attributes for the form
        $this->setAttribute('method', 'post')
        ->setAttribute('data-role', 'form')
        ->setAttribute('autocomplete', false);
        
        
        // make the form elements
        $this->add(array(
            'name' => 'group-name',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Group Name',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
            ),
            
            'attributes' => array(
                'id'           => 'group-name',
                'class'        => 'w3-input w3-border w3-round',
                'autocomplete' => 'new-group-name', // google chrome hack
            ),
        ));
        
        
        $this->add(array(
            'name' => 'join_authorization',
            'type' => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'use_hidden_element' => true,
                'checked_value'   => 1,
                'unchecked_value' => 0,
            ),
            
            'attributes' => array(
                'id'    => 'group-settings',
                'class' => 'w3-check',
            ),
        ));
        
        
        $this->add(array(
            'name' => 'closed_to_public',
            'type' => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'use_hidden_element' => true,
                'checked_value'   => 1,
                'unchecked_value' => 0,
            ),
            
            'attributes' => array(
                'id'    => 'group-settings-2',
                'class' => 'w3-check',
            ),
        ));
        
        
        $this->add(new Csrf('csrf_security'));
        
        
        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',
            
            'attributes' => array(
                'id'    => 'submit',
                'class' =>  'w3-btn w3-white w3-border w3-border-blue w3-round w3-left',
                'value' => 'Create Group',
            ),
        ));
    }
}
