<?php
namespace Members\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Members\Model\Exceptions\MessagesException;


class MarkAsReadController extends AbstractActionController
{
    protected $messages_service;
    
    
    public function indexAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        try {
            $id = $this->params()->fromPost('id');
            
            echo $this->getMessagesService()->markAsRead($id);
        } catch (MessagesException $e) {
            echo $e->getMessage();
        }
        
        return $view_model;
    }
    
    
    public function getMessagesService()
    {
        if (!$this->messages_service) {
            $this->messages_service = $this->getServiceLocator()->get('Members\Model\MessagesModel');
        }
        
        return $this->messages_service;
    }
}