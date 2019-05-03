<?php

namespace Forum;


use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Http;

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
            'configureLayout'
        ));
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

        if ('forum' === $module) {
            $layout->setTemplate('layout/forum');
        }
    }
}