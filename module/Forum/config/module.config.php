<?php

return array(
    'controllers' => array(
        'invokables' => array(
            'Forum\Controller\Forum' => 'Forum\Controller\ForumController',
        ),
    ),

    'router' => array(
        'routes' => array(
            'forum' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/forum',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Forum\Controller',
                        'controller'    => 'Forum',
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
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'Forum' => __DIR__ . '/../view',
        ),
    ),
);
