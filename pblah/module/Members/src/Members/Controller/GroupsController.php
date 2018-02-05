<?php

namespace Members\Controller;

use Zend\Mvc\Controller\AbstractActionController;

use Zend\View\Model\ViewModel;

//use Zend\Paginator\Paginator;
//use Zend\Paginator\Adapter\DbTableGateway;

use Zend\Db\TableGateway\TableGateway;

use Members\Model\Exceptions\GroupsException;
use Members\Model\Exceptions\GroupMembersOnlineException;

use Members\Form\CreateGroupForm;
use Members\Form\JoinGroupForm;

use Members\Model\Filters\CreateGroup;
use Members\Model\Filters\JoinGroup;




class GroupsController extends AbstractActionController
{
    protected $groups_service;

    protected $groups_table;
    

    public function indexAction()
    {
        $view_model = new ViewModel();
        
        try {
            $view_model->setVariable('groups', $this->getGroupsService()->getGroupsIndex());
        } catch (GroupsException $e) {
            $view_model->setVariable('groups', $e->getMessage());
        }
        
        return $view_model;
    } 
    
    
    public function viewallaction()
    {
        return new ViewModel(array('groups' => $this->getGroupsService()->getAllUserGroups()));
    }
    
    
    public function viewmoreAction()
    {
        try {
            $view_model = new ViewModel();
            
            $view_model->setVariable('groups', $this->getGroupsService()->getGroups());
        } catch (GroupsException $e) {
            $view_model->setVariable('groups', $e->getMessage());
        }
        
        return $view_model;
    }


    public function getgroupsAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);


        try {
            echo json_encode($this->getGroupsService()->getGroups());
        } catch (GroupsException $e) {
            echo json_encode($e->getMessage());  
        }
       
        return $view_model;
    }
    
    
    public function getgroupmembersonlineAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        try {
            echo json_encode(array('display_name' => $this->getGroupsService()->getGroupMembersOnline()));
        } catch (GroupMembersOnlineException $e) {
            echo json_encode(array('message' => $e->getMessage()));
        }

        return $view_model;
    }


    public function grouphomeAction()
    {
        $id = $this->params()->fromRoute('id', 0);

        if (0 === $id) {
            return $this->redirect()->toRoute('members/groups', array('action' => 'index'));
        }

        if (!$this->getGroupsService()->getGroupInformation(intval($id))) {
            return $this->redirect()->toRoute('members/groups', array('action' => 'index'));
        }

        return new ViewModel(array('group_info' => $this->getGroupsService()->getGroupInformation($id)));
    }


    public function getonegroupmembersonlineAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        $id = $this->params()->fromRoute('id');

        try {
            echo json_encode($this->getGroupsService()->getGroupMembersOnline($id));
        } catch (GroupMembersOnlineException $e) {
            echo json_encode($e->getMessage());
        }

        return $view_model;
    }


    public function leavegroupAction()
    {
        $layout = $this->layout();
        $layout->setTerminal(true);

        $view_model = new ViewModel();
        $view_model->setTerminal(true);

        $group_id = $this->params()->fromRoute('id');

        try {
            echo json_encode($this->getGroupsService()->leaveGroup($group_id));
        } catch (GroupsException $e) {
            echo json_encode($e->getMessage());
        }

        return $view_model;
    }
    
    
    public function creategroupAction()
    {
        
        $form = new CreateGroupForm();
        
        return new ViewModel(array(
            'form' => $form
        ));
    }
    
    
    public function cgroupAction() 
    {
        $form = new CreateGroupForm();
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $create_group = new CreateGroup();
            
            $form->setInputFilter($create_group->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $create_group->exchangeArray($form->getData());
                
                try {
                    if ($this->getGroupsService()->createGroup($create_group)) {
                        $this->flashMessenger()->addSuccessMessage("Group was created successfully!");
                    
                        return $this->redirect()->toUrl('create-group-success');
                    } 
                } catch (GroupsException $e) {
                    $this->flashMessenger()->addErrorMessage((string)$e->getMessage());
                    
                    return $this->redirect()->toUrl('create-group-failure');
                }
            } else {
                $this->flashMessenger()->addErrorMessage("Invalid form. Please correct this and try again.");
                
                return $this->redirect()->toUrl('create-group-failure');
            }
        }
    }
    
    
    public function postgroupmessageAction()
    {
        
    }
    
    
    public function postgroupeventAction()
    {
        
    }
    
    
    public function joingroupAction()
    {
        $id = $this->params()->fromRoute('id');
        
        $form = new JoinGroupForm();
        
        return new ViewModel(array('form' => $form, 'id' => $id));
    }
    
    
    public function jgroupAction()
    {
       
        $form = new JoinGroupForm();
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            $join_group = new JoinGroup();
            
            
            $form->setInputFilter($join_group->getInputFilter());
            $form->setData($request->getPost());
            
            
            if ($form->isValid()) { 
                $join_group->exchangeArray($form->getData());
                
                try {
                    if (false !== $this->getGroupsService()->joinGroup($_POST['group_id'], $join_group)) {
                        $this->flashMessenger()->addSuccessMessage("Request to join group sent.");
                        
                        return $this->redirect()->toUrl('join-group-success');
                    }
                } catch (GroupsException $e) {
                    $this->flashMessenger()->addErrorMessage((string)$e->getMessage());
                    
                    return $this->redirect()->toUrl('join-group-failure');
                }
            } else {
                $messages = $form->getMessages();
                
                $this->flashMessenger()->addErrorMessage("Invalid form. Please correct this and try again.");
                
                return $this->redirect()->toUrl('join-group-failure'); 
            } 
        }
    }
    
    
    public function joingroupsuccessAction()
    {
        
    }
    
    
    public function joingroupfailureAction()
    {
        
    }
    
    
    public function nogroupsAction()
    {
        
    }
        
    
    public function viewgroupsAction()
    {
        try {
            $groups = $this->getGroupsService()->getAllGroups();
            
            return new ViewModel(array('groups' => $groups));
        } catch (GroupsException $e) {
            $this->flashMessenger()->addErrorMessage((string)$e->getMessage());
            
            return $this->redirect()->toUrl('no-groups');
        }
    }
    
    
    public function creategroupsuccessAction()
    {
        
    }
    
    
    public function creategroupfailureAction()
    {
        
    }



    public function getGroupsService()
    {
        if (!$this->groups_service) {
            $this->groups_service = $this->getServiceLocator()->get('Members\Model\GroupsModel');
        }

        return $this->groups_service;
    }
    
    
    public function getGroupsTable()
    {
        if (!$this->groups_table) {
            $this->groups_table = new TableGateway('group_members', $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
        }
        
        return $this->groups_table;
    }
}