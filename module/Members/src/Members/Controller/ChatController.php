<?php

namespace Members\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Members\Model\ChatModel;
use Members\Model\Exceptions\ChatException;



class ChatController extends AbstractActionController
{
    public $chat_service;
    
    
    public function indexAction()
    {
        $view_model = new ViewModel();
        
        $params = $this->identity();
        
        $layout = $this->layout();
        
        $dir = @array_diff(scandir(getcwd() . '/public/images/profile/' . $params . '/', 1), array('.', '..', 'current', '.htaccess', 'albums', 'edited_photos', 'videos', 'status'));
        
        if (count($dir) > 0) {
            $images = array();
            
            foreach ($dir as $value) {
                $images[] = "<img src=\"/images/profile/$params/$value\" class=\"w3-margin-bottom w3-round w3-border\" style=\"width: 100%; height: 88px;\">";
            }
            
            natsort($images);
            
            $layout->setVariable('my_images', $images);
        } else {
            $images[] = "<img src=\"/images/profile/avatar2.png\" class=\"w3-margin-bottom w3-round w3-border\" style=\"width: 100%; height: 88px;\">";
            
            
            $layout->setVariable('my_images', $images);
        }
        
        $video_dir = @array_diff(scandir(getcwd() . '/public/images/profile/' . $params . '/videos/', 1), array('.', '..'));
        
        if (count($video_dir) > 0) {
            $videos = array();
            
            foreach ($video_dir as $video) {
                $videos[] = "<video style=\"width: 100%; height: 100%;\" class=\"w3-margin-bottom w3-round w3-border\">
                <source src=\"/images/profile/$params/videos/$video\" type=\"video/mp4\">
                </video>";
            }
            
            natsort($videos);
            
            $layout->setVariable('my_videos', $videos);
        }
    }
    
    
    public function startchatAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        $params = $this->params()->fromPost('startWho');
        
        try {
            if ($this->getChatService()->startChat($params) instanceof ChatModel) {
                // ok to go
            } else {
                throw new ChatException("An error occurred while attempt to start the chat session, please try again.");
            }
        } catch (ChatException $e) {
            echo $e->getMessage();
        }
        
        return $view_model;
    }
    
    public function sendmessageAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        $who = $this->params()->fromPost('who');
        $message = $this->params()->fromPost('message');
        
        try {
            $this->getChatService()->sendMessage($who, $message);
        } catch (ChatException $e) {
            echo $e->getMessage();
        }
        
        return $view_model;
    }
    
    
    public function getmessagesAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        $params = $this->params()->fromQuery('user');
        
        echo $this->getChatService()->listChatMessages($params);
        
        return $view_model;
    }
    
    
    public function endchatAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);
        
        $view_model = new ViewModel();
        $view_model->setTerminal(true);
        
        $friend = $this->params()->fromPost('friend');
        
        try {
            $this->getChatService()->endChat($friend);
        } catch (ChatException $e) {
            echo $e->getMessage();
        }
        
        return $view_model;
    }
    
    
    
    
    public function getChatService()
    {
        if (!$this->chat_service) {
            $this->chat_service = $this->getServiceLocator()->get('Members\Model\ChatModel');
        }
        
        return $this->chat_service;
    }
}