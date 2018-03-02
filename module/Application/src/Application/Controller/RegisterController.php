<?php
namespace Application\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Application\Form\RegisterForm;
use Application\Model\Filters\Register;




class RegisterController extends AbstractActionController
{
    public $register;


    public function indexAction()
    {
        $form = new RegisterForm();

        return new ViewModel(array(
            'form' => $form
        ));
    }


    public function regAction()
    {
        $form = new RegisterForm();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $register = new Register();

            $form->setInputFilter($register->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $register->exchangeArray($form->getData());

                if ($this->getRegisterInstance()->handleRegistration($register) !== false) {
                    $this->flashMessenger()->addSuccessMessage("Registration Successful! Please check your email address provided for a verification link to finish the reigstration process.");

                    return $this->redirect()->toUrl('success');
                } else {
                    $this->flashMessenger()->addErrorMessage("Oops! Something went wrong while attempting to complete the registration process, please try again.");

                    return $this->redirect()->toUrl('failure');
                }
            }
        }
    }


    public function failureAction()
    {
        return;
    }


    public function successAction()
    {
        return;
    }



    public function getRegisterInstance()
    {
        if (!$this->register) {
            $sm = $this->getServiceLocator();
            $this->register = $sm->get('Application\Model\RegisterModel');
        }

        return $this->register;
    }
}