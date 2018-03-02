<?php

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;


class VerifyController extends AbstractActionController
{
    public $verify;


    public function indexAction()
    {
        try {
            $code = $this->params()->fromRoute('code');

            if ($this->getVerifyInstance()->authenticateCode($code) !== false) {

                $this->flashMessenger()->addSuccessMessage("Verification Successful, you can now login!");

                return $this->redirect()->toRoute('home/verify', array('code' => $code, 'action' => 'success'));
            }
        } catch (\RuntimeException $e) {
            $this->flashMessenger()->addErrorMessage($e->getMessage());

            return $this->redirect()->toRoute('home/verify', array('code' => $code, 'action' => 'failure'));
        }
    }


    public function successAction()
    {

    }

    public function failureAction()
    {

    }


    public function getVerifyInstance()
    {
        if (!$this->verify) {
            $sm = $this->getServiceLocator();
            $this->verify = $sm->get('Application\Model\VerifyModel');
        }

        return $this->verify;
    }
}