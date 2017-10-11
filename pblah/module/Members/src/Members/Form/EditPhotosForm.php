<?php
namespace Members\Form;

use Zend\Form\Form;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Select;
use Zend\Form\Element\Button;


class EditPhotosForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('pblah-edit-photos');
        
        // set the form attributes
        $this->setAttribute('method', 'post')
        ->setAttribute('data-role', 'form');
        
        // create the form elements
        $this->add(array(
            'name' => 'album-name',
            'type' => Select::class,
            'options' => array(
                'label' => 'Album to edit photos from',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
                
                'empty_option' => 'Choose an album',
            ),
            
            'attributes' => array(
                'placeholder' => 'Album',
                'id'          => 'album-name',
                'class'       => 'w3-input w3-border w3-round',
            ),
        ));
        
        
        $this->add(new Csrf('csrf_security'));
        
        
        $this->add(array(
            'name' => 'submit',
            'type' => Button::class,
            'options' => array(
                'label' => 'Edit',
            ),
            
            'attributes' => array(
                'id'    => 'submit',
                'class' => 'w3-btn w3-white w3-border w3-border-blue w3-round w3-right',
                'value' => ''
            )
        ));
    }
}