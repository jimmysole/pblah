<?php

namespace Members\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Members\Model\Exceptions\FriendsException;


class FriendsController extends AbstractActionController
{
    protected $friends_service;
    
    
    public function indexAction()
    {
        
    }
    
    public function getfriendsonlineAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        try {
            echo json_encode(array('friends' => $this->getFriendsService()->getFriendsOnline()));
        } catch (FriendsException $e) {
            echo json_encode(array('message' => $e->getMessage()));
        }
        
        return $view_model;
    }
    
    
    public function getfriendsAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        try {
            echo json_encode(array('friend_list' => $this->getFriendsService()->friendList()));
        } catch (FriendsException $e) {
            echo json_encode(array('message' => $e->getMessage()));  
        }
        
        return $view_model;
    }
    
    
    public function buildchatAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        try {
            echo json_encode($this->getFriendsService()->populateChatList());
        } catch (FriendsException $e) {
            echo json_encode(array('message' => $e->getMessage()));
        }
        
        return $view_model;
    }
    
    
    public function getFriendsService()
    {
        if (!$this->friends_service) {
            $this->friends_service = $this->getServiceLocator()->get('Members\Model\FriendsModel');
        }
        
        return $this->friends_service;
    }
}