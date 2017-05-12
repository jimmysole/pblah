<?php


namespace Members\Controller;

use Zend\Mvc\Controller\AbstractActionController;



class MembersController extends AbstractActionController
{
    protected $profile_service;
    protected $groups_service;


    public function indexAction()
    {
        if (!$this->getProfileService()->checkIfProfileSet()) {
            return $this->redirect()->toRoute('members/profile', array('action' => 'create-profile'));
        }
        
        $params = $this->identity();

        $dir = @array_diff(scandir(getcwd() . '/public/images/profile/' . $params . '/', 1), array('.', '..', 'current', '.htaccess'));

        if (count($dir) > 0) {
            $images = array();

            foreach ($dir as $value) {
                $images[] = "<img src=\"/images/profile/$params/$value\" class=\"w3-margin-bottom w3-round w3-border\" style=\"width: 100%; height: 88px;\">";
            }

            $layout = $this->layout();

            natsort($images);

            $layout->setVariable('my_images', $images);
        } 
    }


    public function getProfileService()
    {
        if (!$this->profile_service) {
            $this->profile_service = $this->getServiceLocator()->get('Members\Model\ProfileModel');
        }

        return $this->profile_service;
    }


    public function getGroupsService()
    {
        if (!$this->groups_service) {
            $this->groups_service = $this->getServiceLocator()->get('Members\Model\GroupsModel');
        }

        return $this->groups_service;
    }
}
