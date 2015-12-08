<?php

namespace Portal;

use Zend\Mvc\MvcEvent;
use Zend\ModuleManager\Listener\ModuleLoaderListener;
use Zend\Mvc\ModuleRouteListener;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

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
            $viewModel->setVariable('controller', str_replace("Portal\Controller", "", $e->getRouteMatch()->getParam('controller')));
            $viewModel->setVariable('action', $e->getRouteMatch()->getParam('action'));
            /* Controller and action name getting code ends here */
            $controller = $e->getTarget();
            if ($controller instanceof Controller\Authcontroller) {
                $controller->layout('layout/login.phtml');
            }
            /* Login check starts here */
            if (stristr($e->getRouteMatch()->getParam("__NAMESPACE__"), 'Portal') != false && !$controller instanceof Controller\Authcontroller) {
                if (!$e->getApplication()->getServiceManager()->get('AuthService')->hasIdentity()) {
                    return $e->getTarget()->plugin('redirect')->toRoute('portal/auth',array('action'=>'login'));
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
                }
            )
        );
    }

}
