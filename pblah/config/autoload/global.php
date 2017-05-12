<?php

return array(
    'db' => array(
        'driver' => 'Pdo',
        'dsn'    => 'mysql:dbname=pblah;host=localhost', // change this to match your mysql database name and host
    ),

    'error_logging' => true,

    'service_manager' => array(
        'aliases' => array(
            'Zend\Authentication\AuthenticationService' => 'pblah-auth',
        ),

        'invokables' => array(
            'pblah-auth' => 'Zend\Authentication\AuthenticationService',
        ),

        'factories' => array(
            'Zend\Db\Adapter\Adapter'       => 'Zend\Db\Adapter\AdapterServiceFactory',
            'Navigation'                    => 'Zend\Navigation\Service\DefaultNavigationFactory',

            'Zend\Log\Logger' => function($sm) {
                $logger = new Zend\Log\Logger();


                $mapping = array(
                    'timestamp' => 'timestamp',
                    'message'   => 'error_msg',
                );

                $writer = new Zend\Log\Writer\Db($sm->get('Zend\Db\Adapter\Adapter'), 'errors', $mapping);

                $writer->setFormatter(new Zend\Log\Formatter\Db('Y-m-d H:i:s'));

                $logger->addWriter($writer);

                Zend\Log\Logger::registerErrorHandler($logger);

                return $logger;
            },
        ),

        'session' => array(
            'config' => array(
                'class' => 'Zend\Session\Config\SessionConfig',
                'options' => array(
                    'name' => 'p-blah',
                ),
            ),

            'storage' => 'Zend\Session\Storage\SessionArrayStorage',

            'validators' => array(
                'Zend\Session\Validator\RemoteAddr',
                'Zend\Session\Validator\HttpUserAgent',
            ),
        ),
    ),

    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
    ),
);
