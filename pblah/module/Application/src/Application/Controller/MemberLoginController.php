<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Application\Form\LoginForm;
use Application\Model\Filters\Login;
use Application\Model\Storage\LoginAuthStorage;


class MemberLoginController extends AbstractActionController
{
    protected $storage;
    protected $auth_service;
    protected $login_service;
    protected $mem_service;


    public function indexAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toUrl('/members');
        }

        $form = new LoginForm();

        return new ViewModel(array(
            'form' => $form
        ));
    }


    public function authAction()
    {
        $form = new LoginForm();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $login = new Login();

            $form->setInputFilter($login->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $login->exchangeArray($form->getData());
                // first make a quick password_verify check
                if (!$this->getLoginService()->verifyPassword($login)) {
                    $this->flashMessenger()->addErrorMessage('Invalid username and/or password');

                    return $this->redirect()->toUrl('login-failure');
                }

                // check first if a session is already active
                if (!$this->getLoginService()->checkSession($login->username)) {
                    $this->flashMessenger()->addErrorMessage("A session is already active with that username.");
                    return $this->redirect()->toUrl('login-failure');
                }
                
                $this->getAuthService()->getAdapter()
                ->setIdentity($login->username)
                ->setCredential($this->getLoginService()->verifyPassword($login)['pass']);

                
                $result = $this->getAuthService()->authenticate();

                foreach ($result->getMessages() as $message) {
                    $this->flashMessenger()->addMessage($message);
                }

                if ($result->isValid()) {
                    $this->getLoginService()->insertIntoFriendsOnline($login->username);
                    $this->getLoginService()->insertIntoGroupMembersOnline($login->username);
                    
                    if ($login->remember_me == 1) {
                        try {
                            $this->getServiceLocator()->get('Application\Model\Storage\LoginAuthStorage')->rememberUser(1);

                            $this->getAuthService()->setStorage($this->getServiceLocator()->get('Application\Model\Storage\LoginAuthStorage'));

                            $this->getLoginService()->insertSession($login->username,
                                $this->getLoginService()->verifyPassword($login)['pass'], session_id());
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                        }
                    } else if ($login->remember_me == 0) {
                        try {
                            $this->getServiceLocator()->get('Application\Model\Storage\LoginAuthStorage')->rememberUser(0);

                            $this->getAuthService()->setStorage($this->getServiceLocator()->get('Application\Model\Storage\LoginAuthStorage'));

                            $this->getLoginService()->insertSession($login->username,
                                $this->getLoginService()->verifyPassword($login)['pass'], session_id());
                        } catch (\Exception $e) {
                            echo $e->getMessage();
                        }
                    }

                    $this->getAuthService()->getStorage()->write($login->username);

                    return $this->redirect()->toUrl('/members');
                } else {
                    $this->flashMessenger()->addErrorMessage('Invalid username and/or password');

                    return $this->redirect()->toUrl('login-failure');
                }
            } else {
                return new ViewModel(array('form_error' => 'Validation Error while logging in, please try again.'));
            }
        }
    }


    public function loginfailureAction()
    {
        return new ViewModel(array());
    }


    public function getAuthService()
    {
        if (!$this->auth_service) {
            $this->auth_service = $this->getServiceLocator()->get('MemberAuthService');
        }

        return $this->auth_service;
    }


    public function getLoginService()
    {
        if (!$this->login_service) {
            $this->storage = $this->getServiceLocator()->get('Application\Model\LoginModel');
        }

        return $this->storage;
    }
}