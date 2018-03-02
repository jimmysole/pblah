<?php
namespace Members\Model\Filters;


use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilter;


use Zend\Filter\StripTags;
use Zend\Validator\StringLength;


class ChangeDisplayName implements InputFilterAwareInterface
{

    /**
     * @var string
     */
    public $display_name;



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

            $this->input_filter = $input_filter;
        }

        return $this->input_filter;
    }
}