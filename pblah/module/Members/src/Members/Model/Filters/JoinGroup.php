<?php


namespace Members\Model\Filters;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Zend\Filter\StripTags;
use Zend\Filter\StringTrim;

use Zend\Validator\StringLength;


class JoinGroup implements InputFilterAwareInterface
{
    /**
     * @var string
     */
    public $first_name;
    
    
    /**
     * @var string
     */
    public $last_name;
    
    
    /**
     * @var int
     */
    public $age;
    
    
    /**
     * @var string
     */
    public $message;
    
    
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
        $this->first_name = (!empty($data['first-name'])) ? $data['first-name'] : null;
        $this->last_name  = (!empty($data['last-name']))  ? $data['last-name']  : null;
        $this->age        = (!empty($data['age']))        ? $data['age']        : null;
        $this->message    = (!empty($data['message']))    ? $data['message']    : null;
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
                'name'     => 'first-name',
                'required' => true,
                'filters'  => array(
                    array('name' => StripTags::class),
                    array('name' => StringTrim::class),
                ),
                
                'validators' => array(
                    array(
                        'name'    => StringLength::class,
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 15,
                        ),
                    ),
                ),
            )));
            
            
            $input_filter->add($factory->createInput(array(
                'name'     => 'last-name',
                'required' => true,
                'filters'  => array(
                    array('name' => StripTags::class),
                    array('name' => StringTrim::class),
                ),
                
                'validators' => array(
                    array(
                        'name'    => StringLength::class,
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 3,
                            'max'      => 15,
                        ),
                    ),
                ),
            )));
            
            
            $input_filter->add($factory->createInput(array(
                'name'     => 'age',
                'required' => true,
                'filters'  => array(
                    array('name' => StripTags::class),
                    array('name' => StringTrim::class),
                ),
                
                'validators' => array(
                    array(
                        'name'    => StringLength::class,
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 2,
                            'max'      => 3,
                        ),
                    ),
                ),
            )));
            
            
            $input_filter->add($factory->createInput(array(
                'name'     => 'message',
                'required' => true,
                'filters'  => array(
                    array('name' => StripTags::class),
                ),
                
                'validators' => array(
                    array(
                        'name'    => StringLength::class,
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 15,
                            'max'      => 1000,
                        ),
                    ),
                ),
            )));
            
            
            
            $this->input_filter = $input_filter;
        }
        
        return $this->input_filter;
    }
}
