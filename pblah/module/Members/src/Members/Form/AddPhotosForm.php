<?php


namespace Members\Form;


use Zend\Form\Form;
use Zend\Form\Element\File;
use Zend\Form\Element\Csrf;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Select;


class AddPhotosForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('pblah-add-photos');
        
        // set the form attributes
        $this->setAttribute('method', 'post')
        ->setAttribute('data-role', 'form');
        
        // create the form elements
        $this->add(array(
            'name' => 'album-name',
            'type' => Select::class,
            'options' => array(
                'label' => 'Album to add to',
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
        
        
        
        $this->add(array(
            'name' => 'copy-from-album',
            'type' => Select::class,
            'options' => array(
                'label' => 'Copy from another album',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
                
                'empty_option' => 'Choose an album to copy from',
            ),
            
            'attributes' => array(
                'placeholder' => 'Other Album Name',
                'id'          => 'copy-from-album',
                'class'       => 'w3-input w3-border w3-round',
            ),
        ));
        
        
        $this->add(array(
            'name' => 'photos',
            'type' => File::class,
            'options' => array(
                'label' => 'Album photos',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
            ),
            
            'attributes' => array(
                'id'       => 'upload-files',
                'class'    => 'w3-input w3-border w3-round',
                'multiple' => true,
            ),
        ));
        
        $this->add(new Csrf('csrf_security'));
        
        
        $this->add(array(
            'name' => 'submit',
            'type' => Submit::class,
            
            'attributes' => array(
                'id'    => 'submit',
                'class' => 'w3-btn w3-white w3-border w3-border-blue w3-round w3-left',
                'value' => 'Submit',
            )
        ));
    }
}