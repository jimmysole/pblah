<?php

namespace Members\Form\Factory;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Members\Form\AddPhotosForm;


class AddPhotosFormFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $service_locator)
    {
        $locator = $service_locator->getServiceLocator();
        
        $directories = array();
        
        $dir_iterator = new \DirectoryIterator(getcwd() . '/public/images/profile/' . $locator->get('pblah-auth')->getIdentity() . '/albums/');

        // get all directories in the path
        foreach ($dir_iterator as $file_info) {
            if ($file_info->isDir() && !$file_info->isDot()) {
                $directories[$file_info->getFilename()] = $file_info->getFilename();
            }
        }
        
        // set the option attributes
        $form = new AddPhotosForm();
        
        $form->get('album-name')->setAttribute('options', $directories);
        
        $form->get('copy-from-album')->setAttribute('options', $directories);
        
        return $form;
    }
}