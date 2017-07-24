<?php

namespace Members\Form\Factory;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;


use Members\Form\RemovePhotosForm;


class RemovePhotosFormFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $service_locator)
    {
        $locator = $service_locator->getServiceLocator();
        
        $directories = array();
        
        $directory_iterator = new \DirectoryIterator(getcwd() . '/public/images/profile/' . $locator->get('pblah-auth')->getIdentity() . '/albums/');
        
        // get the albums
        foreach ($directory_iterator as $dirs) {
            if ($dirs->isDir() && !$dirs->isDot()) {
                $directories[$dirs->getFilename()] = $dirs->getFilename();
            }
        }
        
        // set the select option attributes
        $form = new RemovePhotosForm();
        
        $form->get('album-name')->setAttribute('options', $directories);
        
        return $form;
    }
}