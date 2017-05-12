<?php

namespace Members\Model\Filters;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Zend\Filter\StripTags;
use Zend\Filter\StringTrim;
use Zend\Validator\StringLength;



class CreateGroup implements InputFilterAwareInterface
{
    /**
     * @var mixed
     */
    public $group_name;
    
    
    /**
     * @var string
     */
    public $group_settings;
    
    
    /**
     * @var string
     */
    public $group_settings2;
    
    
    /**
     * @var InputFilter|null
     */
    protected $input_filter;
    
    
    /**
     * Assigns the data array to the objects
     * @param array $data
     */
    public function exchangeArray($data)
    {
        $this->group_name      = (!empty($data['group-name']))          ? $data['group-name']          : null;
        $this->group_settings  = (!empty($data['join_authorization']))  ? $data['join_authorization']  : null;
        $this->group_settings2 = (!empty($data['closed_to_public']))    ? $data['closed_to_public']    : null;
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
                'name'      => 'group-name',
                'required'  => true,
                'filters'   => array(
                    array('name' => StripTags::class),
                    array('name' => StringTrim::class),
                ),
                
                'validators' => array(
                    array(
                        'name'    => StringLength::class,
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 10,
                            'max'      => 100,
                        ),
                    ),
                ),
            )));
            
            
            $input_filter->add($factory->createInput(array(
                'name'     => 'join_authorization',
                'required' => false,
            )));
            
            
            $input_filter->add($factory->createInput(array(
                'name'     => 'closed_to_public',
                'required' => false,
            )));
            
            
            $this->input_filter = $input_filter;
        }
        
        return $this->input_filter;
    }
}