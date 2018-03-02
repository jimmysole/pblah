<?php

namespace Members\Model\Filters;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;


class RemovePhotos implements InputFilterAwareInterface
{
    public $album_name;
    
    public $album_photos = array();
    
    protected $input_filter;
    
    
    /**
     * Assigns the data array to the objects
     * @param array $data
     */
    public function exchangeArray($data)
    {
        $this->album_name   = (!empty($data['album-name']))      ? $data['album-name']                                     : null;
        $this->album_photos = (count($data['album-photos']) > 0) ? array_merge($this->album_photos, $data['album-photos']) : null;
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
                'name'     => 'album-name',
                'required' => true,
            )));
            
            $this->input_filter = $input_filter;
        }
        
        return $input_filter;
    }
}