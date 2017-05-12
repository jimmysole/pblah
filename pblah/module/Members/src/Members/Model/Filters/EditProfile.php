<?php


namespace Members\Model\Filters;


use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;

use Zend\Filter\ToInt;
use Zend\Filter\StripTags;
use Zend\Filter\StringTrim;
use Zend\Validator\StringLength;


class EditProfile implements InputFilterAwareInterface
{

    /**
     * @var string
     */
    public $display_name;


    /**
     * @var string
     */
    public $email_address;


    /**
     * @var int
     */
    public $age;


    /**
     * @var string
     */
    public $location;


    /**
     * @var string
     */
    public $bio;


    /**
     * @var InputFilter|null
     */
    protected $input_filter;


    /**
     * Assigns the data array to the objects related to editing profile
     * @param array $data
     */
    public function exchangeArray($data)
    {
        $this->display_name  = (!empty($data['display_name']))  ? $data['display_name']  : null;
        $this->email_address = (!empty($data['email_address'])) ? $data['email_address'] : null;
        $this->age           = (!empty($data['age']))           ? $data['age']           : null;
        $this->location      = (!empty($data['location']))      ? $data['location']      : null;
        $this->bio           = (!empty($data['bio']))           ? $data['bio']           : null;
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
                'name'     => 'display_name',
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
                'name'     => 'email_address',
                'required' => false,
                'filters'  => array(
                    array('name' => StripTags::class),
                    array('name' => StringTrim::class),
                ),

                'validators' => array(
                    array(
                        'name'    => StringLength::class,
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 10,
                            'max'      => 75,
                        ),
                    ),
                ),
            )));


            $input_filter->add($factory->createInput(array(
                'name'     => 'age',
                'required' => false,
                'filters'  => array(
                    array('name' => StripTags::class),
                    array('name' => StringTrim::class),
                    array('name' => ToInt::class),
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
                'name'     => 'location',
                'required' => false,
                'filters'  => array(
                    array('name' => StripTags::class),
                ),

                'validators' => array(
                    array(
                        'name'    => StringLength::class,
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 10,
                            'max'      => 150,
                        ),
                    ),
                ),
            )));


            $input_filter->add($factory->createInput(array(
                'name'      => 'bio',
                'required'  => false,
                'filters'   => array(
                    array('name' => StripTags::class),
                ),

                'validators' => array(
                    array(
                        'name'    => StringLength::class,
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 10,
                            'max'      => 3000,
                        ),
                    ),
                ),
            )));
        }

        return $this->input_filter;
    }
}