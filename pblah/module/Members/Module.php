<?php


namespace Members;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Http;
use Members\Model\ProfileModel;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Members\Model\Filters\EditProfile;
use Members\Model\EditProfileModel;
use Members\Model\GroupsModel;


class Module implements AutoloaderProviderInterface
{

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/' , __NAMESPACE__),
                ),
            ),
        );
    }


    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }


    public function onBootstrap(MvcEvent $e)
    {
        $eventManager        = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);

        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'checkCredentials'));
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array($this, 'configureLayout'));
    }


    public function checkCredentials(MvcEvent $e)
    {
        $matches = $e->getRouteMatch();

        if (!$matches) {
            return $e;
        }

        $route = $matches->getMatchedRouteName();

        if (0 !== strpos($route, 'members/') && $route !== 'members') {
            return $e;
        }

        $auth_service = $e->getApplication()->getServiceManager()->get('pblah-auth');

        if (!$auth_service->hasIdentity()) {
            $response = $e->getResponse();
            $response->setStatusCode(302);
            $response->getHeaders()
            ->addHeaderLine('Location', $e->getRouter()->assemble([], array('name' => 'home/member-login')));
            $response->sendHeaders();
            return $response;
        }

        return $e;
    }


    public function configureLayout(MvcEvent $e)
    {
        if ($e->getError()) {
            return $e;
        }

        $request = $e->getRequest();

        if (!$request instanceof Http\Request || $request->isXmlHttpRequest()) {
            return $e;
        }

        $matches = $e->getRouteMatch();

        if (!$matches) {
            return $e;
        }

        $app = $e->getParam('application');
        $layout = $app->getMvcEvent()->getViewModel();

        $controller = $matches->getParam('controller');

        $module = strtolower(explode('\\', $controller)[0]);

        if ('members' === $module) {
            $layout->setTemplate('layout/members');
        }
    }


    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Members\Module\EditProfileModel' => function ($sm) {
                    $table_gateway = $sm->get('EditProfileService');
                    $profile = new EditProfileModel($table_gateway);
                    return $profile;
                },

                'EditProfileService' => function ($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $result_set_prototype = new ResultSet();
                    $result_set_prototype->setArrayObjectPrototype(new EditProfile());
                    return new TableGateway('profiles', $db_adapter, null, $result_set_prototype);
                },

                'Members\Model\ProfileModel' => function ($sm) {
                    $table_gateway = $sm->get('ProfileService');
                    $profile = new ProfileModel($table_gateway, $sm->get('pblah-auth')->getIdentity());

                    return $profile;
                },

                'ProfileService' => function ($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new TableGateway('profiles', $db_adapter);
                },

                'Members\Model\GroupsModel' => function ($sm) {
                    $table_gateway = $sm->get('GroupsService');
                    $group_model = new GroupsModel($table_gateway, $sm->get('pblah-auth')->getIdentity());

                    return $group_model;
                },

                'GroupsService' => function ($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new TableGateway('groups', $db_adapter);
                }
            ),
        );
    }
}