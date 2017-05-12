<?php
namespace Application;

// import all the namespaces we will use
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Zend\ModuleManager\ModuleManager;

use Zend\Log\Logger;

use Zend\Authentication\Storage;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter as DbTableAuthAdapter;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Ddl;
use Zend\Db\Sql\Ddl\Column;
use Zend\Db\Sql\Ddl\Constraint;

use Zend\View\Model\ViewModel;

use Application\Model\Filters\Setup;
use Application\Model\Filters\Login;
use Application\Model\Storage\LoginAuthStorage;
use Application\Model\SetupModel;
use Application\Model\LoginModel;
use Application\Model\RegisterModel;
use Application\Model\Filters\Register;
use Application\Model\VerifyModel;
use Application\Model\LogoutModel;


class Module
{

    public function onBootstrap(MvcEvent $e)
    {
        
        try {
            $application = $e->getApplication();
            $sm = $application->getServiceManager();

            $db = $sm->get('Zend\Db\Adapter\Adapter');

            if ($db->getDriver()
                ->getConnection()
                ->connect()
                ->isConnected()) {
                $sql = new Sql($db);

                $check = $db->getDriver()
                    ->getConnection()
                    ->execute("SHOW TABLES LIKE 'errors'");

                if ($check->count() > 0) {
                    $eventManager = $e->getApplication()->getEventManager();

                    $sharedManager = $application->getEventManager()->getSharedManager();

                    $sharedManager->attach('Zend\Mvc\Application', 'dispatch.error', function ($e) use ($sm) {
                        if ($e->getParam('exception')) {
                            $sm->get('Zend\Log\Logger')
                                ->crit($e->getParam('exception'));
                        }
                    });
                } else {
                    $errors_table = new Ddl\CreateTable('errors');
                    $errors_table->addColumn(new Column\Integer('id', false, null, array(
                        'auto_increment' => true,
                        'unsigned' => true
                    )));

                    $errors_table->addColumn(new Column\Datetime('timestamp', false));
                    $errors_table->addColumn(new Column\Text('error_msg'));

                    $errors_table->addConstraint(new Constraint\PrimaryKey('id'));

                    $adapter = $sql->getAdapter();

                    $adapter->query($sql->buildSqlString($errors_table), Adapter::QUERY_MODE_EXECUTE);

                    $eventManager = $e->getApplication()->getEventManager();

                    $sharedManager = $application->getEventManager()->getSharedManager();

                    $sharedManager->attach('Zend\Mvc\Application', 'dispatch.error', function ($e) use ($sm) {
                        if ($e->getParam('exception')) {
                            $sm->get('Zend\Log\Logger')
                                ->crit($e->getParam('exception'));
                        }
                    });
                }

                $moduleRouteListener = new ModuleRouteListener();
                $moduleRouteListener->attach($eventManager);
            }
        } catch (\Exception $ex) {
            $target = $e->getTarget();
            $service_mgr = $target->getServiceManager();

            $view_model = $e->getViewModel();
            $view_model->setTemplate('layout/dberror');

            $content = new ViewModel();
            $content->setTemplate('error/dberrorpage');

            $view_model->setVariable('content', $service_mgr->get('ViewRenderer')
                ->render($content));

            echo $service_mgr->get('ViewRenderer')->render($view_model);

            $e->stopPropagation();
        }
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
                )
            )
        );
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Application\Model\Storage\LoginAuthStorage' => function ($sm) {
                    return new LoginAuthStorage();
                },

                'AuthService' => function ($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $auth_adapter = new DbTableAuthAdapter($db_adapter, 'admins', 'username', 'password');

                    $auth_service = new AuthenticationService();
                    $auth_service->setAdapter($auth_adapter);
                    $auth_service->setStorage($sm->get('Application\Model\Storage\LoginAuthStorage'));

                    return $auth_service;
                },

                'MemberAuthService' => function ($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $auth_adapter = new DbTableAuthAdapter($db_adapter, 'members', 'username', 'password');

                    $auth_service = new AuthenticationService();
                    $auth_service->setAdapter($auth_adapter);
                    $auth_service->setStorage($sm->get('Application\Model\Storage\LoginAuthStorage'));

                    return $auth_service;
                },

                'Application\Model\SetupModel' => function ($sm) {
                    $table_gateway = $sm->get('SetupService');
                    $setup = new SetupModel($table_gateway);
                    return $setup;
                },

                'SetupService' => function ($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $result_set_prototype = new ResultSet();
                    $result_set_prototype->setArrayObjectPrototype(new Setup());
                    return new TableGateway('admins', $db_adapter, null, $result_set_prototype);
                },

                'Application\Model\LoginModel' => function ($sm) {
                    $table_gateway = $sm->get('LoginService');
                    $table = new LoginModel($table_gateway);
                    return $table;
                },

                'LoginService' => function ($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $result_set_prototype = new ResultSet();
                    $result_set_prototype->setArrayObjectPrototype(new Login());
                    return new TableGateway('sessions', $db_adapter, null, $result_set_prototype);
                },

                'Application\Model\RegisterModel' => function ($sm) {
                    $table_gateway = $sm->get('RegisterService');
                    $table = new RegisterModel($table_gateway);
                    return $table;
                },

                'RegisterService' => function ($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $result_set_prototype = new ResultSet();
                    $result_set_prototype->setArrayObjectPrototype(new Register());
                    return new TableGateway('pending_members', $db_adapter, null, $result_set_prototype);
                },

                'Application\Model\VerifyModel' => function($sm) {
                    $table_gateway = $sm->get('VerifyService');
                    $table = new VerifyModel($table_gateway);
                    return $table;
                },

                'VerifyService' => function($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new TableGateway('pending_users', $db_adapter);
                },

                'Application\Model\LogoutModel' => function($sm) {
                    $table_gateway = $sm->get('LogoutService');
                    $table = new LogoutModel($table_gateway);
                    return $table;
                },

                'LogoutService' => function($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new TableGateway('sessions', $db_adapter);
                }
            )
        );
    }

    public function init(ModuleManager $manager)
    {
        $events = $manager->getEventManager();

        $shared_events = $events->getSharedManager();

        $shared_events->attach(__NAMESPACE__, 'dispatch', function ($e) {
            $controller = $e->getTarget();

            if (get_class($controller) == 'Application\Controller\SetupController') {
                $controller->layout('layout/setup');
            } else if (get_class($controller) == 'Application\Controller\MemberLoginController' || get_class($controller) == 'Application\Controller\AdminLoginController') {
                $controller->layout('layout/login');
            } else if (get_class($controller) == 'Application\Controller\RegisterController') {
                $controller->layout('layout/register');
            } else if (get_class($controller) == 'Application\Controller\VerifyController') {
                $controller->layout('layout/verify');
            }
        }, 100);
    }
}
