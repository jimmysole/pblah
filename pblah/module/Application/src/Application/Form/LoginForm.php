<?php

namespace Application\Form;

use Zend\Form\Form;
use Zend\Form\Element\Csrf;


class LoginForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('p-blah_login');

        // set the attributes for the form
        $this->setAttribute('method', 'post')
        ->setAttribute('data-role', 'form')
        ->setAttribute('autocomplete', false)
        ->setAttribute('class', 'form-horizontal');


        // make the form elements
        $this->add(array(
            'name' => 'username',
            'type' => 'Zend\Form\Element\Text',
            'options' => array(
                'label' => 'Username',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
            ),

            'attributes' => array(
                'id'           => 'username',
                'class'        => 'w3-input',
                'autocomplete' => 'new-username', // google chrome hack
            )
        ));

        $this->add(array(
            'name' => 'password',
            'type' => 'Zend\Form\Element\Password',
            'options' => array(
                'label' => 'Password',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
            ),

            'attributes' => array(
                'id'           => 'password',
                'class'        => 'w3-input',
                'autocomplete' => 'new-password', // google chrome hack
            )
        ));

        $this->add(array(
            'name'    => 'remember_me',
            'type'    => 'Zend\Form\Element\Checkbox',
            'options' => array(
                'label' => 'Remember Me',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left w3-validate',
                ),
            ),

            'attributes' => array(
                'id'    => 'remember_me',
                'class' => 'w3-check w3-left',
                'style' => 'margin-left: 10px; margin-top: -6px;',
            ),
        ));


        $this->add(new Csrf('csrf_security'));


        $this->add(array(
            'name' => 'submit',
            'type' => 'Zend\Form\Element\Submit',

            'attributes' => array(
                'id'    => 'submit',
                'class' => 'w3-btn w3-white w3-border w3-border-blue w3-round w3-left',
                'type'  => 'submit',
                'value' => 'Login',
            ),
        ));
    }

}