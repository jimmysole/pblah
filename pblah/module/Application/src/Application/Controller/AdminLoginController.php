<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Application\Form\LoginForm;
use Application\Model\Filters\Login;


class AdminLoginController extends AbstractActionController
{
    protected $storage;
    protected $auth_service;
    protected $login_service;
    protected $mem_service;


    public function indexAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toUrl('/admin');
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
            $data = $form->getData();

            $login = new Login();

            $form->setInputFilter($login->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $login->exchangeArray($data);

                // first make a quick password_verify check
                if (!$this->getLoginService()->verifyPassword($login)) {
                    return $this->redirect()->toUrl('login-failure');
                }

                $this->getAuthService()->getAdapter()
                ->setIdentity($this->getLoginService()->verifyPassword($login)['pass'])
                ->setCredential($login->password);

                $result = $this->getAuthService()->authenticate();

                foreach ($result->getMessages() as $message) {
                    $this->flashMessenger()->addMessage($message);
                }

                if ($result->isValid()) {
                    if ($login->remember_me == 1) {
                        $this->getSessionStorage()->rememberUser(1);
                        $this->getAuthService()->setStorage($this->getServiceLocator()->get('Application\Model\Storage\LoginAuthStorage'));

                        $this->getLoginService()->insertSession($login->username,
                            $this->getLoginService()->verifyPassword($login)['pass'], session_id());
                    } else if ($login->remember_me == 0) {
                        $this->getAuthService()->setStorage($this->getServiceLocator()->get('Application\Model\Storage\LoginAuthStorage'));

                        $this->getLoginService()->insertSession($login->username,
                            $this->getLoginService()->verifyPassword($login)['pass'], session_id());
                    }

                    $this->getAuthService()->getStorage()->write($login->username);

                    return $this->redirect()->toUrl('/admin');
                } else {
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
            $this->auth_service = $this->getServiceLocator()->get('AuthService');
        }

        return $this->auth_service;
    }


    public function getSessionStorage()
    {
        if (!$this->storage) {
            $this->storage = $this->getServiceLocator()->get('Application\Model\Storage\LoginAuthStorage');
        }

        return $this->storage;
    }


    public function getLoginService()
    {
        if (!$this->login_service) {
            $this->storage = $this->getServiceLocator()->get('Application\Model\LoginModel');
        }

        return $this->storage;
    }
}