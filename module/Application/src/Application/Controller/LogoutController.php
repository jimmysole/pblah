<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;


class LogoutController extends AbstractActionController
{
    public $logout_service;

    public $storage;

    public $auth_service;

    public $m_auth_service;


    public function indexAction()
    {
        return array();
    }


    public function adminlogoutAction()
    {
        // handle admin logouts
        $identity = $this->getAdminAuthService()->getIdentity();

        if (!$identity) {
            return $this->redirect()->toRoute('admin-login', array('action' => 'index'));
        } else {
            $this->getLogoutService()->deleteSession($identity);

            $this->getSessionStorage()->forgetUser();
            $this->getAdminAuthService()->clearIdentity();

            return $this->redirect()->toRoute('admin-login', array('action' => 'index'));
        }
    }


    public function memberlogoutAction()
    {
        // handle member logouts
        $identity = $this->getAdminAuthService()->getIdentity();

        if (!$identity) {
            return $this->redirect()->toRoute('home/member-login', array('action' => 'index'));
        } else {
            $this->getLogoutService()->deleteSession($identity);

            $this->getSessionStorage()->forgetUser();
            $this->getMemberAuthService()->clearIdentity();


            return $this->redirect()->toRoute('home/member-login', array('action' => 'index'));
        }
    }


    public function getAdminAuthService()
    {
        if (!$this->auth_service) {
            $this->auth_service = $this->getServiceLocator()->get('AuthService');
        }

        return $this->auth_service;
    }


    public function getMemberAuthService()
    {
        if (!$this->m_auth_service) {
            $this->m_auth_service = $this->getServiceLocator()->get('MemberAuthService');
        }

        return $this->m_auth_service;
    }


    public function getSessionStorage()
    {
        if (!$this->storage) {
            $this->storage = $this->getServiceLocator()->get('Application\Model\Storage\LoginAuthStorage');
        }

        return $this->storage;
    }


    public function getLogoutService()
    {
        if (!$this->logout_service) {
            $sm = $this->getServiceLocator();

            $this->logout_service = $sm->get('Application\Model\LogoutModel');
        }

        return $this->logout_service;
    }
}