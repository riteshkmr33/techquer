<?php

ini_set('display_errors', 1);
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
return array(
    'router' => array(
        'routes' => array(
            'portal' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
                'options' => array(
                    'route' => '/portal',
                    'defaults' => array(
                        '__NAMESPACE__' => 'Portal\Controller',
                        'controller' => 'Portal\Controller\Index',
                        'action' => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'default' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/[:controller[/:action]]',
                            'constraints' => array(
                                'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                            ),
                        ),
                    ),
                    /* Admins route */
                    'admins' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/admins[/:action][/:id][/]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Portal\Controller\Admins',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    /* Articles route */
                    'articles' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/articles[/:action][/:id][/]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Portal\Controller\Articles',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    /* Auth route */
                    'auth' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/auth[/:action][/]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            ),
                            'defaults' => array(
                                'controller' => 'Portal\Controller\Auth',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    /* Categories route */
                    'categories' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/categories[/:action][/:id][/]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Portal\Controller\Categories',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    /* Roles route */
                    'roles' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/roles[/:action][/:id][/]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Portal\Controller\Roles',
                                'action' => 'index',
                            ),
                        ),
                    ),
                    /* Tags route */
                    'tags' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/tags[/:action][/:id][/]',
                            'constraints' => array(
                                'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                                'id' => '[0-9]*'
                            ),
                            'defaults' => array(
                                'controller' => 'Portal\Controller\Tags',
                                'action' => 'index',
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
                'type' => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern' => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Portal\Controller\Admins' => 'Portal\Controller\AdminsController',
            'Portal\Controller\Articles' => 'Portal\Controller\ArticlesController',
            'Portal\Controller\Auth' => 'Portal\Controller\AuthController',
            'Portal\Controller\Categories' => 'Portal\Controller\CategoriesController',
            'Portal\Controller\Index' => 'Portal\Controller\IndexController',
            'Portal\Controller\Roles' => 'Portal\Controller\RolesController',
            'Portal\Controller\Tags' => 'Portal\Controller\TagsController',
        ),
    ),
    'view_manager' => array(
        'template_map' => array(
            'portal/header' => __DIR__ . '/../view/partial/portalHeader.phtml',
            'portal/pagination' => __DIR__ . '/../view/partial/portalPagination.phtml',
            'sidebar' => __DIR__ . '/../view/partial/sidebar.phtml',
        ),
        'template_path_stack' => array(
            'Portal' => __DIR__ . '/../view',
        ),
    ),
    // Placeholder for console routes
    'console' => array(
        'router' => array(
            'routes' => array(
            ),
        ),
    ),
);
