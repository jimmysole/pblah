<?php

namespace Members\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


use Members\Model\Exceptions\MessagesException;


class SendMessageController extends AbstractActionController
{
    protected $messages_service;
    
    
    public function indexAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        try {
            $to      = $this->params()->fromPost('to');
            $subject = $this->params()->fromPost('subject');
            $message = $this->params()->fromPost('message');
            
            if ($this->getMessagesService()->sendMessage($to, $subject, $message)) {
                echo json_encode(array('success' => 'Message sent successfully.'));
            }
        } catch (MessagesException $e) {
            echo json_encode(array('error' => $e->getMessage()));
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