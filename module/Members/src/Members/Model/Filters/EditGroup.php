<?php

namespace Members\Model\Filters;


use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;


use Zend\Filter\StripTags;
use Zend\Filter\StringTrim;
use Zend\Validator\StringLength;


class EditGroup implements InputFilterAwareInterface
{
    
    /**
     * @var string
     */
    public $group_name;
    
    
    /**
     * @var string
     */
    public $group_description;
    
    
    
    /**
     * @var InputFilter|null
     */
    protected $input_filter;
    
    
    /**
     * Assigns the data array to the objects related to editing group
     * @param array $data
     */
    public function exchangeArray($data)
    {
        $this->group_name         = (!empty($data['group_name']))        ? $data['group_name']        : null;
        $this->group_description  = (!empty($data['group_description'])) ? $data['group_description'] : null;
    }
    
    /**
     * {@inheritDoc}
     * @see \Zend\InputFilter\InputFilterAwareInterface::setInputFilter()
     */
    public function setInputFilter(InputFilterInterface $input_filter)
    {
        return;
    }
    
    
    /**
     * {@inheritDoc}
     * @see \Zend\InputFilter\InputFilterAwareInterface::getInputFilter()
     */
    public function getInputFilter()
    {
        if (!$this->input_filter) {
            $input_filter = new InputFilter();
            $factory      = new InputFactory();
            
            $input_filter->add($factory->createInput(array(
                'name'     => 'group_name',
                'required' => false,
                'filters'  => array(
                    array('name' => StripTags::class),
                    array('name' => StringTrim::class),
                ),
                
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 6,
                            'max'      => 75,
                        ),
                    ),
                ),
            )));
            
            
            $input_filter->add($factory->createInput(array(
                'name'     => 'group_description',
                'required' => false,
                'filters'  => array(
                    array('name' => StripTags::class),
                ),
                
                'validators' => array(
                    array(
                        'name'    => StringLength::class,
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 50,
                            'max'      => 3000,
                        ),
                    ),
                ),
            )));
        }
        
        return $this->input_filter;
    }
}