<?php

namespace Members\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ChatController extends AbstractActionController
{
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
            
            $layout = $this->layout();
            
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
}