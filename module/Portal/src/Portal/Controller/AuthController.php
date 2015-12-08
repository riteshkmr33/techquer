<?php

namespace Portal\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Http\Header\SetCookie;
use Zend\Http\Header\Cookie;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use Zend\Session\Container;
use Portal\Form\LoginForm;

class AuthController extends AbstractActionController {

    protected $form;
    protected $storage;
    protected $authservice;

    public function getAuthService() {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }

        return $this->authservice;
    }

    public function getSessionStorage() {
        if (!$this->storage) {
            $this->storage = $this->getServiceLocator()->get('Portal\Model\AuthStorage');
        }

        return $this->storage;
    }

    public function getForm() {
        if (!$this->form) {
            $this->form = new LoginForm();
        }

        return $this->form;
    }

    public function loginAction() {
        //if already login, redirect to success page 
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('portal');
        }

        return new ViewModel(array('form' => $this->getForm(),
            'messages' => $this->flashmessenger()->getMessages()
        ));
    }

    public function authenticateAction() {
        $form = $this->getForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $this->getAuthService()->getAdapter()
                    ->setIdentity($request->getPost('username'))
                    ->setCredential($request->getPost('password').$request->getPost('loginKey'));

            $result = $this->getAuthService()->authenticate();

            if ($result->isValid()) {
                echo json_encode(array('status' => 1, 'message' => 'Logged in successfully. Redirecting..!!'));
            } else {
                echo json_encode(array('status' => 0, 'message' => 'Invalid username/password combination..!!'));
            }
        }

        exit;
    }

    public function logoutAction() {
        if ($this->getAuthService()->hasIdentity()) {
            $this->getSessionStorage()->forgetMe();
            $this->getAuthService()->clearIdentity();

            $this->flashmessenger()->addMessage("You've been logged out");
        }

        return $this->redirect()->toRoute('portal/auth', array('action' => 'login'));
    }

}
