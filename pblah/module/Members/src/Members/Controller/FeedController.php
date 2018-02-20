<?php

namespace Members\Controller;

use Zend\Mvc\Controller\AbstractActionController;


class FeedController extends AbstractActionController
{
    public $status_service;
    
    
    public function indexAction()
    {
        
    }
    
    
    public function getStatusService()
    {
        if (!$this->status_service) {
            $this->status_service = $this->getServiceLocator()->get('Members\Model\FeedModel');
        }
        
        return $this->status_service;
    }
}