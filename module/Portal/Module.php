<?php

namespace Portal;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Listener\ModuleLoaderListener;
use Zend\Mvc\ModuleRouteListener;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Portal\Model\Admins;
use Portal\Model\AdminsTable;
use Portal\Model\Articles;
use Portal\Model\ArticlesTable;
use Portal\Model\Categories;
use Portal\Model\CategoriesTable;
use Portal\Model\Roles;
use Portal\Model\RolesTable;
use Portal\Model\Tags;
use Portal\Model\TagsTable;

class Module {

    public function getConfig() {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig() {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function onBootstrap(MvcEvent $e) {
        $eventManager = $e->getApplication()->getEventManager();
        $eventManager->attach('route', array($this, 'onRouteFinish'), -100);  // To get the routing details

        /* Admin Layout code starts here */
        $eventManager->getSharedManager()->attach('Zend\Mvc\Controller\AbstractActionController', 'dispatch', function($e) {
            $controller = $e->getTarget();
            $controllerClass = get_class($controller);
            $moduleNamespace = substr($controllerClass, 0, strpos($controllerClass, '\\'));
            $config = $e->getApplication()->getServiceManager()->get('config');
            if (isset($config['module_layouts'][$moduleNamespace])) {
                $controller->layout($config['module_layouts'][$moduleNamespace]);
            }
        }, 100);
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
        /* Portal Layout code ends here */

        $eventManager->attach(MvcEvent::EVENT_DISPATCH, function($e) {
            /* Controller and action name getting code starts here */
            $viewModel = $e->getViewModel();
            $admins = $e->getApplication()->getServiceManager()->get('Portal\Model\AdminsTable');
            $viewModel->setVariable('controller', str_replace("Portal\Controller", "", $e->getRouteMatch()->getParam('controller')));
            $viewModel->setVariable('action', $e->getRouteMatch()->getParam('action'));
            $viewModel->setVariable('permissions', $admins->getPermissionsByUsername($e->getApplication()->getServiceManager()->get('AuthService')->getIdentity()));
            /* Controller and action name getting code ends here */
            $controller = $e->getTarget();
            if ($controller instanceof Controller\Authcontroller) {
                $controller->layout('layout/login.phtml');
            }
            /* Login check starts here */
            if (stristr($e->getRouteMatch()->getParam("__NAMESPACE__"), 'Portal') != false && !$controller instanceof Controller\Authcontroller) {
                if (!$e->getApplication()->getServiceManager()->get('AuthService')->hasIdentity()) {
                    return $e->getTarget()->plugin('redirect')->toRoute('portal/auth', array('action' => 'login'));
                }
            }
            /* Login check ends here */
        });
    }

    public function onDispatch(MvcEvent $e) {
        $controller = $e->getTarget();
        $controller->layout('layout/layout');
    }

    /* Function to get the routing details */

    public function onRouteFinish($e) {
        $matches = $e->getRouteMatch();
        $controller = $matches->getParam('controller');
        //var_dump($matches);
    }

    public function getServiceConfig() {
        return array(
            'factories' => array(
                'Portal\Model\AuthStorage' => function($sm) {
                    return new \Portal\Model\AuthStorage('techquer');
                },
                'AuthService' => function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $dbTableAuthAdapter = new DbTableAuthAdapter($dbAdapter, 'admins', 'userName', 'password', 'SHA1(?)');

                    $authService = new AuthenticationService();
                    $authService->setAdapter($dbTableAuthAdapter);
                    $authService->setStorage($sm->get('Portal\Model\AuthStorage'));

                    return $authService;
                },
                /* Admins table starts */
                'Portal\Model\AdminsTable' => function($sm) {
                    $tableGateway = $sm->get('AdminsTableGateway');
                    $table = new AdminsTable($tableGateway);
                    return $table;
                },
                'AdminsTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Admins());
                    return new TableGateway('admins', $dbAdapter, null, $resultSetPrototype);
                },
                /* Admins table ends */
                /* Articles table starts */
                'Portal\Model\ArticlesTable' => function($sm) {
                    $tableGateway = $sm->get('ArticlesTableGateway');
                    $table = new ArticlesTable($tableGateway);
                    return $table;
                },
                'ArticlesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Articles());
                    return new TableGateway('articles', $dbAdapter, null, $resultSetPrototype);
                },
                /* Articles table ends */
                /* Categories table starts */
                'Portal\Model\CategoriesTable' => function($sm) {
                    $tableGateway = $sm->get('CategoriesTableGateway');
                    $table = new CategoriesTable($tableGateway);
                    return $table;
                },
                'CategoriesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Categories());
                    return new TableGateway('categories', $dbAdapter, null, $resultSetPrototype);
                },
                /* Categories table ends */
                /* Roles table starts */
                'Portal\Model\RolesTable' => function($sm) {
                    $tableGateway = $sm->get('RolesTableGateway');
                    $table = new RolesTable($tableGateway);
                    return $table;
                },
                'RolesTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Roles());
                    return new TableGateway('roles', $dbAdapter, null, $resultSetPrototype);
                },
                /* Roles table ends */
                /* Tags table starts */
                'Portal\Model\TagsTable' => function($sm) {
                    $tableGateway = $sm->get('TagsTableGateway');
                    $table = new TagsTable($tableGateway);
                    return $table;
                },
                'TagsTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Tags());
                    return new TableGateway('tags', $dbAdapter, null, $resultSetPrototype);
                },
            /* Tags table ends */
            )
        );
    }

}
