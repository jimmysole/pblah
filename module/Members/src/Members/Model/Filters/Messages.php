<?php
namespace Members\Model\Filters;


use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;


use Zend\Filter\StripTags;
use Zend\Validator\StringLength;


class Messages implements InputFilterAwareInterface
{
    /**
     * @var string
     */
    public $subject;
    
    
    /**
     * @var string
     */
    public $message;
    
    
    /**
     * @var InputFilter|null
     */
    protected $input_filter;
    
    
    public function exchangeArray($data)
    {
        $this->subject = (!empty($data['subject'])) ? $data['subject'] : null;
        $this->message = (!empty($data['message'])) ? $data['message'] : null;
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
                'name'     => 'subject',
                'required' => true,
                'filters'  => array(
                    array('name' => StripTags::class),
                ),
                
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 10,
                            'max'      => 120,
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
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 100,
                            'max'      => 1500,
                        ),
                    ),
                ),
            )));
            
            $this->input_filter = $input_filter;
        }
        
        return $this->input_filter;
    }
}