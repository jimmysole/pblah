<?php
namespace Application\Controller;


use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Application\Form\SetupForm;
use Application\Model\Filters\Setup;


class SetupController extends AbstractActionController
{
    public $setup;


    public function indexAction()
    {
        // check if the setup has already been run
        // if so, redirect to login page
        if ($this->getSetupInstance()->checkIfSetupRan() === true) {
            return $this->redirect()->toUrl('admin-login');
        }

        $form = new SetupForm();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $setup = new Setup();

            $form->setInputFilter($setup->getInputFilter());
            $form->setData($request->getPost());


            if ($form->isValid()) {
                $setup->exchangeArray($form->getData());

                if ($this->getSetupInstance()->createTables()) {
                    if ($this->getSetupInstance()->makeAdmin($setup) && $this->getSetupInstance()->makeMember($setup)) {
                        $this->flashMessenger()->addSuccessMessage("pblah was setup successfully!");
                        return $this->redirect()->toUrl('setup/success');
                    } else {
                        $this->flashMessenger()->addErrorMessage("pblah encountered a error while creating the admin information, please try again.");
                        return $this->redirect()->toUrl('setup/failure');
                    }
                } else {
                    $this->flashMessenger()->addErrorMessage("pblah encountered a error while creating the database tables, please try again.");
                    return $this->redirect()->toUrl('setup/failure');
                }
            }
        }

        return new ViewModel(array('form' => $form));
    }


    public function successAction()
    {

    }


    public function failureAction()
    {

    }





    public function getSetupInstance()
    {
        if (!$this->setup) {
            $sm = $this->getServiceLocator();
            $this->setup = $sm->get('Application\Model\SetupModel');
        }

        return $this->setup;
    }
}