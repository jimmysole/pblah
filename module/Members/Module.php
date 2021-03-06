<?php
namespace Members;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Http;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;

use Members\Model\ProfileModel;
use Members\Model\GroupsModel;
use Members\Model\EventsModel;
use Members\Model\StatusModel;
use Members\Model\FriendsModel;
use Members\Model\FeedModel;
use Members\Model\ChatModel;
use Members\Model\MessagesModel;
use Members\Model\Filters\Messages;

class Module implements AutoloaderProviderInterface
{

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php'
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . str_replace('\\', '/', __NAMESPACE__)
                )
            )
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function onBootstrap(MvcEvent $e)
    {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array(
            $this,
            'checkCredentials'
        ));
        $eventManager->attach(MvcEvent::EVENT_ROUTE, array(
            $this,
            'configureLayout'
        ));
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
        
        $auth_service = $e->getApplication()
            ->getServiceManager()
            ->get('pblah-auth');
        
        if (!$auth_service->hasIdentity()) {
            $response = $e->getResponse();

            $response->setStatusCode(302);

            $response->getHeaders()->addHeaderLine('Location', $e->getRouter()
                ->assemble([], array(
                'name' => 'home/member-login'
            )));
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
                },
                
                'Members\Model\EventsModel' => function ($sm) {
                    $table_gateway = $sm->get('EventsService');
                    $events_model = new EventsModel($table_gateway, $sm->get('pblah-auth')->getIdentity());
                    
                    return $events_model;
                },
                
                'EventsService' => function ($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new TableGateway('events', $db_adapter);
                },
                
                'Members\Model\StatusModel' => function ($sm) {
                    $table_gateway = $sm->get('StatusService');
                    $members_model = new StatusModel($table_gateway, $sm->get('pblah-auth')->getIdentity());
                    
                    return $members_model;
                },
                
                'StatusService' => function ($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new TableGateway('status', $db_adapter);
                },
                
                'Members\Model\FriendsModel' => function ($sm) {
                    $table_gateway = $sm->get('FriendsService');
                    $friends_model = new FriendsModel($table_gateway, $sm->get('pblah-auth')->getIdentity());
                    
                    return $friends_model;
                },
                
                'FriendsService' => function ($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new TableGateway('friends', $db_adapter);
                },
                
                'Members\Model\FeedModel' => function($sm) {
                    $table_gateway = $sm->get('FeedService');
                    $feed_model = new FeedModel($table_gateway, $sm->get('pblah-auth')->getIdentity());
                    
                    return $feed_model;
                },
                
                'FeedService' => function($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new TableGateway('status', $db_adapter);
                },
                
                'Members\Model\ChatModel' => function($sm) {
                    $table_gateway = $sm->get('ChatService');
                    $chat_model = new ChatModel($table_gateway, $sm->get('pblah-auth')->getIdentity());
                    
                    return $chat_model;
                },
                
                'ChatService' => function($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    return new TableGateway('chat', $db_adapter);
                },
                
                'Members\Model\MessagesModel' => function($sm) {
                    $table_gateway = $sm->get('MessagesService');
                    $members_model = new MessagesModel($table_gateway, $sm->get('pblah-auth')->getIdentity());
                    
                    return $members_model;
                },
                
                'MessagesService' => function($sm) {
                    $db_adapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $result_set_prototype = new ResultSet();
                    $result_set_prototype->setArrayObjectPrototype(new Messages());
                    return new TableGateway('private_messages', $db_adapter, null, $result_set_prototype);
                }
            )
        );
    }
}
