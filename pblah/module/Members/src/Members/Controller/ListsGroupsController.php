<?php

namespace Members\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;


class ListsGroupsController extends AbstractActionController
{
    protected $groups_service;
    
    
    public function indexAction()
    {
        $paginator = $this->getGroupsService()->browseAllGroups();
        
        $paginator->setCurrentPageNumber((int)$this->params()->fromRoute('page', 1));
        
        $paginator->setItemCountPerPage(10);
        
        return new ViewModel(array('paginator' => $paginator));
    }
    
    
    public function getGroupsService()
    {
        if (!$this->groups_service) {
            $this->groups_service = $this->getServiceLocator()->get('Members\Model\GroupsModel');
        }
        
        return $this->groups_service;
    }
}