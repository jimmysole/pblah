<?php
namespace Members\Model\Filters;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Zend\Filter\StripTags;
use Zend\Filter\StringTrim;

use Zend\Validator\StringLength;
use Zend\Validator\Date;


class CreateEvent implements InputFilterAwareInterface
{
    /**
     * @var mixed
     */
    public $event_name;
    
    
    /**
     * @var string
     */
    public $event_description;
    
    
    /**
     * @var string
     */
    public $start_date;
    
    
    /**
     * 
     * @var string
     */
    public $end_date;
    
    
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
        $this->event_name         = (!empty($data['event-name']))          ? $data['event-name']          : null;
        $this->event_description  = (!empty($data['event-description']))   ? $data['event-description']   : null;
        $this->start_date         = (!empty($data['start-date']))          ? $data['start-date']          : null;
        $this->end_date           = (!empty($data['end-date']))            ? $data['end-date']            : null;
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
                'name'      => 'event-name',
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
                'name'     => 'event-description',
                'required' => true,
                'filters'  => array(
                    array('name' => StripTags::class),
                ),
                
                'validators' => array(
                    array(
                        'name'    => StringLength::class,
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 30,
                            'max'      => 3000,
                        ),
                    ),
                ),
            )));
            
            
            $input_filter->add($factory->createInput(array(
                'name'     => 'start-date',
                'required' => true,
                
                'validators' => array(
                    array(
                        'name'    => Date::class,
                        'options' => array(
                            'format' => 'Y-m-d',
                        ),
                    ),
                ),
            )));
            
            $input_filter->add($factory->createInput(array(
                'name'     => 'end-date',
                'required' => true,
                
                'validators' => array(
                    array(
                        'name'    => Date::class,
                        'options' => array(
                            'format' => 'Y-m-d',
                        ),
                    ),
                ),
            )));
            
            
            $this->input_filter = $input_filter;
        }
        
        return $this->input_filter;
    }
}