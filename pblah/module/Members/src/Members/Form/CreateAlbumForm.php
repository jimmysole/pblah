<?php

namespace Members\Form;

use Zend\Form\Form;
use Zend\Form\Element\Text;
use Zend\Form\Element\File;
use Zend\Form\Element\Submit;
use Zend\Form\Element\Csrf;


class CreateAlbumForm extends Form
{
    public function __construct($name = null)
    {
        parent::__construct('p-blah_create-album');
        
        // set the attributes for the form
        $this->setAttribute('method', 'post')
        ->setAttribute('data-role', 'form')
        ->setAttribute('autocomplete', false);
        
        
        // create the form elements
        $this->add(array(
            'name' => 'album-name',
            'type' => Text::class,
            'options' => array(
                'label' => 'Album Name',
                'label_attributes' => array(
                    'class' => 'w3-label w3-left',
                ),
            ),
            
            'attributes' => array(
                'placeholder'  => 'Album Name',
                'id'           => 'album-name',
                'class'        => 'w3-input w3-border w3-round',
                'autocomplete' => 'new-album-name', // google chrome hack
            ),
        ));
        
        $this->add(array(
            'name' => 'photos',
            'type' => File::class,
            'options' => array(
                'label' => 'Add photos',
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