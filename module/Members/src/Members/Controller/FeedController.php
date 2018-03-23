<?php

namespace Members\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Members\Model\Exceptions\FeedException;



class FeedController extends AbstractActionController
{
    public $status_service;
    
    
    public function indexAction()
    {
        
    }
    
    
    public function listownstatusAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        try {
            echo json_encode(array('feed' => $this->getStatusService()->listIndividualStatus()));
        } catch (FeedException $e) {
            echo json_encode(array('fail' => $e->getMessage())); 
        }
        
        return $view_model;
    }
    
    
    
    public function getStatusService()
    {
        if (!$this->status_service) {
            $this->status_service = $this->getServiceLocator()->get('Members\Model\FeedModel');
        }
        
        return $this->status_service;
    }
}