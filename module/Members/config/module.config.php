<?php
use Members\Form\AddPhotosForm;
use Members\Form\Factory\AddPhotosFormFactory;
use Members\Form\RemovePhotosForm;
use Members\Form\Factory\RemovePhotosFormFactory;
use Members\Form\EditPhotosForm;
use Members\Form\Factory\EditPhotosFormFactory;

return array(
    'controllers' => array(
        'invokables' => array(
            'Members\Controller\Members'        => 'Members\Controller\MembersController',
            'Members\Controller\Account'        => 'Members\Controller\AccountController',
            'Members\Controller\Messages'       => 'Members\Controller\MessagesController',
            'Members\Controller\Profile'        => 'Members\Controller\ProfileController',
            'Members\Controller\Groups'         => 'Members\Controller\GroupsController',
            'Members\Controller\Events'         => 'Members\Controller\EventsController',
            'Members\Controller\Status'         => 'Members\Controller\StatusController',
            'Members\Controller\Friends'        => 'Members\Controller\FriendsController',
            'Members\Controller\ListsGroups'    => 'Members\Controller\ListsGroupsController',
            'Members\Controller\Feed'           => 'Members\Controller\FeedController',
            'Members\Controller\Chat'           => 'Members\Controller\ChatController',
            'Members\Controller\SendMessage'    => 'Members\Controller\SendMessageController',
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

                    'status' => array(
                        'type'    => 'segment',
                        'options' => array(
                            'route' => '/status[/:action]',
                            'defaults' => array(
                                'controller' => 'Members\Controller\Status',
                                'action' => 'index',
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
                            'route' => '/messages[/page/:page]',
                            'defaults' => array(
                                'controller' => 'Members\Controller\Messages',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    
                    'send-message' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/send-message',
                            'defaults' => array(
                                'controller' => 'Members\Controller\SendMessage',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    
                    /*
                    'list-messages' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/list-messages[/page/:page]',
                            
                            'defaults' => array(
                                'controller' => 'Members\Controller\ListMessages',
                                'action' => 'index',
                            ),
                        ),
                    ), */

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
                    
                    'friends' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/friends[/:action]',
                            'defaults' => array(
                                'controller' => 'Members\Controller\Friends',
                                'action' => 'index',
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
                            
                            'defaults' => array(
                                'controller' => 'Members\Controller\GroupAdmin',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    
                    'lists-groups' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/lists-groups[/page/:page]',
                            
                            'defaults' => array(
                                'controller' => 'Members\Controller\ListsGroups',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    
                    'feed' => array(
                        'type'    => 'Segment',
                        'options' => array(
                            'route' => '/feed[/:action]',
                            
                            'defaults' => array(
                                'controller' => 'Members\Controller\Feed',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                    
                    'chat' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/chat[/:action][/:id]',
                            
                            'defaults' => array(
                                'controller' => 'Members\Controller\Chat',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    
    'form_elements' => array(
        'factories' => array(
            AddPhotosForm::class     => AddPhotosFormFactory::class,
            RemovePhotosForm::class  => RemovePhotosFormFactory::class,
            EditPhotosForm::class    => EditPhotosFormFactory::class,
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
