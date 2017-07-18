<?php
use Members\Form\AddPhotosForm;
use Members\Form\Factory\AddPhotosFormFactory;

return array(
    'controllers' => array(
        'invokables' => array(
            'Members\Controller\Members'  => 'Members\Controller\MembersController',
            'Members\Controller\Account'  => 'Members\Controller\AccountController',
            'Members\Controller\Messages' => 'Members\Controller\MessagesController',
            'Members\Controller\Profile'  => 'Members\Controller\ProfileController',
            'Members\Controller\Groups'   => 'Members\Controller\GroupsController',
            'Members\Controller\Events'   => 'Members\Controller\EventsController',
        ),
    ),


    'router' => array(
        'routes' => array(
            'members' => array(
                'type'    => 'Literal',
                'options' => array(
                    'route'    => '/members',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Members\Controller',
                        'controller'    => 'Members',
                        'action'        => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route'    => '/[:controller[/:action[/:id]]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id'         => '[0-9]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),


                    'edit-profile' => array(
                        'type'     => 'Segment',
                        'options'  => array(
                            'route' => '/edit-profile[/:action]',
                             'defaults' => array(
                                'controller' => 'Members\Controller\Profile',
                                'action'     => 'edit-profile',
                            ),
                        )
                    ),

                    'account' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route' => '/account[/:action]',
                            'defaults' => array(
                                'controller' => 'Members\Controller\Account',
                                'action'     => 'index',
                            ),
                        ),
                    ),

                    'messages' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route' => '/messages[/:action]',
                            'defaults' => array(
                                'controller' => 'Members\Controller\Messages',
                                'action'     => 'index',
                            ),
                        ),
                    ),

                    'profile' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route' => '/profile[/:action]',
                            'defaults' => array(
                                'controller' => 'Members\Controller\Profile',
                                'action'     => 'index',
                            ),
                        ),
                    ),

                    'groups' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route' => '/groups[/:action][/:id]',
                            'constraints' => array(
                                'id'       => '[0-9]+',
                            ), 

                            'defaults' => array(
                                'controller' => 'Members\Controller\Groups',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    
                    'events' => array(
                        'type'     => 'Segment',
                        'options'  => array(
                            'route' => '/events[/:action][/:id]',
                            'constraints' => array(
                                'id' => '[0-9]+',
                            ),

                            'defaults' => array(
                                'controller' => 'Members\Controller\Events',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    
                    'group-admin' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/group-admin[/:action][/:id]',
                            'constraints' => array(
                                'id' => '[0-9]+',
                            ),
                        ),
                        
                        'defaults' => array(
                            'controller' => 'Members\Controller\GroupAdmin',
                            'index'      => 'index',
                        ),
                    ),
                ),
            ),
            
            
            'paginator' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/groups/view-more/[page/:page]',
                    'constraints' => array(
                        'page'     => '[0-9]*',
                    ),
                ),
                
                'defaults' => array(
                    'controller' => 'Members\Controller\Groups',
                    'action'     => 'view-more',
                ),
            ), 
        ),
    ),
    
    'form_elements' => array(
        'factories' => array(
            AddPhotosForm::class => AddPhotosFormFactory::class,
        ),
    ),
    
    'view_manager' => array(
        'template_path_stack' => array(
            'Members' => __DIR__ . '/../view',
        ),
        
        'template_map' => array(
            'paginator' => __DIR__ . '/../view/layout/paginator.phtml',
        )
    ),
);
