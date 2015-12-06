<?php

namespace Portal\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Http\Header\SetCookie;
use Zend\Http\Header\Cookie;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use Admin\Model\Users;
use Zend\Session\Container;
use Application\Model\Api;
use Application\Model\FrontEndAuth;
use Admin\Model\User;

class AuthController extends AbstractActionController
{

    protected $form;
    protected $storage;
    protected $authservice;
    protected $UsersTable;

    public function getAuthService()
    {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }

        return $this->authservice;
    }

    public function getSessionStorage()
    {
        if (!$this->storage) {
            $this->storage = $this->getServiceLocator()->get('Portal\Model\AuthStorage');
        }

        return $this->storage;
    }
    
    public function getUsersTable()
    {
        if (!$this->UsersTable) {
            $sm = $this->getServiceLocator();
            $this->UsersTable = $sm->get('Portal\Model\AdminsTable');
        }

        return $this->UsersTable;
    }

    public function loginAction()
    {
        //if already login, redirect to success page 
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('portal');
        }

        return new ViewModel(array(
            'messages' => $this->flashmessenger()->getMessages()
        ));
    }

    public function authenticateAction()
    {
        $form = $this->getForm();
        $redirect = 'admin/login';

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                //check authentication...
                $this->getAuthService()->getAdapter()
                        ->setIdentity($request->getPost('username'))
                        ->setCredential($request->getPost('password'));

                $result = $this->getAuthService()->authenticate();
                foreach ($result->getMessages() as $message) {
                    //save message temporary into flashmessenger
                    $this->flashmessenger()->addMessage($message);
                }

                if ($result->isValid()) {
                    $redirect = 'admin';
                    /* //check if it has rememberMe :
                      if ($request->getPost('rememberme') == 1 ) {
                      $this->getSessionStorage()
                      ->setRememberMe(1);
                      //set storage again
                      $this->getAuthService()->setStorage($this->getSessionStorage());
                      } */
                    // SET Cookies
                    $time = ($request->getPost('rememberme') == 1) ? (time() + 365 * 60 * 60 * 24) : (time() - 4);
                    $cookie = new SetCookie('username', $request->getPost('username'), $time); // now + 1 year
                    $cookie1 = new SetCookie('password', $request->getPost('password'), $time); // now + 1 year
                    $cookie2 = new SetCookie('rememberme', $request->getPost('rememberme'), $time); // now + 1 year
                    $response = $this->getResponse()->getHeaders();
                    $response->addHeader($cookie);
                    $response->addHeader($cookie1);
                    $response->addHeader($cookie2);

                    // End set cookies
                    $this->getAuthService()->setStorage($this->getSessionStorage());
                    $this->getAuthService()->getStorage()->write($request->getPost('username'));

                    $wp_auth = new FrontEndAuth();
                    $wp_auth->wordpress_login($request->getPost('username'));  // logging in wordpress account

                    /* Setting logged in user details in session */
                    $user_details = new Container('user_details');
                    $user_details->details = array('user_id' => $result->user_id, 'user_type_id' => $result->user_type_id, 'user_name' => $result->getIdentity());
                    $user_permission = new Container('user_permission');
                    $user_permission->rights = $this->getServiceLocator()->get('Admin\Model\UserRightsTable')->getUserRightsArr($result->user_id);

                    /* set last login time for user - starts here */
                    $username = $request->getPost('username');
                    $result = $this->getUsersTable()->getUser($username, 'user_name');
                    $result->last_login = date('Y-m-d H:i:s', time());
                    $this->getUsersTable()->saveUser($result, 'update_last_login');
                    /* set last login time for user - ends here */
                }
            }
        }
        /* $session = new Container('user_permission');
          print_r($session['rights']); exit; */
        return $this->redirect()->toRoute($redirect);
    }

    public function logoutAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            $this->getSessionStorage()->forgetMe();
            $this->getAuthService()->clearIdentity();

            $this->flashmessenger()->addMessage("You've been logged out");
        }

        return $this->redirect()->toRoute('portal/login');
    }

}
