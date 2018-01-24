<?php

namespace Members\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Members\Model\Exceptions\StatusException;


class StatusController extends AbstractActionController
{
    protected $status_service;
    
    
    public function indexAction()
    {
        
    }
    
    public function poststatusAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        if ($this->request->isPost()) {
            try {
                $params = $this->params()->fromPost('status');
                
                if ($this->getStatusService()->postStatus($params)) {
                    echo json_encode(array('success' => 'Status updated'));
                }
            } catch (StatusException $e) {
                echo json_encode(array('fail' => $e->getMessage()));
            }
        }
        
        return $view_model;
    }
    
    
    public function getstatusAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        try {
            echo json_encode($this->getStatusService()->getStatus());
        } catch (StatusException $e) {
            echo json_encode(array('fail' => $e->getMessage()));
        }
        
        return $view_model;
    }
    
    
    public function getStatusService()
    {
        if (!$this->status_service) {
            $this->status_service = $this->getServiceLocator()->get('Members\Model\StatusModel');
        }
        
        return $this->status_service;
    }
}