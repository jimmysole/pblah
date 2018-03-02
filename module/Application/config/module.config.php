<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index'       => 'Application\Controller\IndexController',
            'Application\Controller\AdminLogin'  => 'Application\Controller\AdminLoginController',
            'Application\Controller\MemberLogin' => 'Application\Controller\MemberLoginController',
            'Application\Controller\Setup'       => 'Application\Controller\SetupController',
            'Application\Controller\Logout'      => 'Application\Controller\LogoutController',
            'Application\Controller\Register'    => 'Application\Controller\RegisterController',
            'Application\Controller\Verify'      => 'Application\Controller\VerifyController',
            'Application\Controller\Forum'       => 'Application\Controller\ForumController',
        ),
    ),

    // define the routes for each controller (see above for the list of controllers)
    'router' => array(
        'routes' => array(
            'home' => array(
                'type'    => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),

                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'       => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),

                            'defaults' => array(

                            ),
                        ),
                    ),

                    'setup' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route' => 'setup[/:action]',
                            'defaults' => array(
                                'controller' => 'Application\Controller\Setup',
                                'action'     => 'index',
                            ),
                        ),
                    ),

                    'admin-login' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route' => 'admin-login[/:action]',
                            'defaults' => array(
                                'controller' => 'Application\Controller\AdminLogin',
                                'action'     => 'index',
                            ),
                        ),
                    ),

                    'member-login' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route' => 'member-login[/:action]',
                            'defaults' => array(
                                'controller' => 'Application\Controller\MemberLogin',
                                'action'     => 'index',
                            ),
                        ),
                    ),

                    'logout' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route' => 'logout[/:action]',
                            'defaults' => array(
                                'controller' => 'Application\Controller\Logout',
                                'action'     => 'index',
                            ),
                        ),
                    ),

                    'register' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route' => 'register[/:action]',
                            'defaults' => array(
                                'controller' => 'Application\Controller\Register',
                                'action'     => 'index',
                            ),
                        ),
                    ),

                    'verify' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route' => 'verify/:code[/:action]',
                            'constraints' => array(
                                'code'   => '[a-zA-Z0-9][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),

                            'defaults' => array(
                                'controller' => 'Application\Controller\Verify',
                                'action'     => 'index',
                            ),
                        ),
                    ),

                    'forum' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route' => 'forum[/:action[/:id]]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'     => '[0-9]+',
                            ),

                            'defaults' => array(
                                'controller' => 'Application\Controller\Forum',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),


    'service_manager' => array(
        'abstract_factories' => array(
            'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'aliases' => array(
            'translator' => 'MvcTranslator',
        ),
    ),

    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),


    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => false,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'layout/dberror'          => __DIR__ . '/../view/layout/dberror.phtml',
            'layout/verify'           => __DIR__ . '/../view/layout/verify.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
            'error/dberrorpage'       => __DIR__ . '/../view/error/dberrorpage.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),

        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),

    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
