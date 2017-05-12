<?php

namespace Application\Model\Filters;

// import the namespaces we will use
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

use Zend\Filter\StripTags;
use Zend\Filter\StringTrim;

use Zend\Validator\StringLength;
use Zend\Validator\EmailAddress;


class Register implements InputFilterAwareInterface
{
    /**
     * @var mixed
     */
    public $username;


    /**
     * @var mixed
     */
    public $password;

    /**
     * @var mixed
     */
    public $email_address;


    /**
     * @var InputFilter|null
     */
    protected $input_filter;


    /**
     * Assigns the data array to the objects username and password
     * @param array $data
     */
    public function exchangeArray($data)
    {
        $this->username       = (!empty($data['username']))      ? $data['username']      : null;
        $this->password       = (!empty($data['password']))      ? $data['password']      : null;
        $this->email_address  = (!empty($data['email_address'])) ? $data['email_address'] : null;
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
                'name'     => 'username',
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
                            'min'      => 6,
                            'max'      => 15,
                        ),
                    ),
                ),
            )));


            $input_filter->add($factory->createInput(array(
                'name'     => 'password',
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
                            'min'      => 6,
                            'max'      => 15,
                        ),
                    ),
                ),
            )));

            $input_filter->add($factory->createInput(array(
                'name'      => 'email_address',
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
                            'max'      => 75,
                        ),
                    ),


                    array(
                        'name'    => EmailAddress::class,
                        'options' => array(
                            'domain'   => true,
                            'hostname' => true,
                            'mx'       => true,
                            'deep'     => true,
                            'message'  => 'Invalid Email Address',
                        ),
                    ),
                ),
            )));

            $this->input_filter = $input_filter;
        }

        return $this->input_filter;
    }
}