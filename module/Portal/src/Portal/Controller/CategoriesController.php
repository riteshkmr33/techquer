<?php

namespace Portal\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Portal\Form\AdminForm;
use Portal\Model\Admins;

class CategoriesController extends AbstractActionController {

    public $form;
    public $adminsTable;
    public $errors = array();

    private function getForm() {
        if (!$this->form) {
            $this->form = new AdminForm();
        }

        return $this->form;
    }

    private function getAdminsTable() {
        if (!$this->adminsTable) {
            $this->adminsTable = $this->getServiceLocator()->get('Portal\Model\AdminsTable');
        }

        return $this->adminsTable;
    }

    public function indexAction() {
        $search = $this->request->getQuery('search');
        $paginator = $this->getAdminsTable()->fetchAll(true, array('search'=>$search));
        //echo '<pre>'; print_r($paginator->getTotalItemCount()); exit;
        $paginator->setCurrentPageNumber((int) $this->Params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);

        return new ViewModel(array('admins' => $paginator,
            'successMsgs' => $this->flashMessenger()->getCurrentSuccessMessages(),
            'errors' => $this->flashMessenger()->getCurrentErrorMessages()
        ));
    }

    public function addAction() {


        $form = $this->getForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $users = new Admins;
            // Adding already exist validation on runtime
            $users->getInputFilter()->get('userName')->getValidatorChain()->attach(new \Zend\Validator\Db\NoRecordExists(array('table' => 'admins', 'field' => 'userName', 'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'))));
            
            $form->setInputFilter($users->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $users->exchangeArray($form->getData());
                $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
                $this->getAdminsTable()->saveAdmins($users, $loggedInUser->adminId, $loggedInUser->adminId);
                $this->flashMessenger()->addSuccessMessage('User added successfully..!!');

                // Redirect to list of pages
                return $this->redirect()->toRoute('portal/admins');
            }
        }

        return new ViewModel(array('form' => $form, 'salt' => $this->generateSalt()));
    }

    public function editAction() {

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('portal/admins');
        }

        if (!$users = $this->getAdminsTable()->getAdmin($id)) {
            $this->flashMessenger()->addErrorMessage('No user found..!!');
            return $this->redirect()->toRoute('portal/admins');
        }

        $form = $this->getForm();
        $form->bind($users);
        $request = $this->getRequest();

        if ($request->isPost()) {

            // Adding already exist validation on runtime excluding the current record
            $users->getInputFilter()->get('userName')->getValidatorChain()->attach(new \Zend\Validator\Db\NoRecordExists(array('table' => 'admins', 'field' => 'userName', 'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), 'exclude' => array('field' => 'adminId', 'value' => $id))));
            $users->getInputFilter()->get('password')->setRequired(false);

            $form->setInputFilter($users->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
                $this->getAdminsTable()->saveAdmins($form->getData(), '', $loggedInUser->adminId);
                $this->flashMessenger()->addSuccessMessage('User updated successfully..!!');

                // Redirect to listing pages
                return $this->redirect()->toRoute('portal/admins');
            }
        }

        return new ViewModel(array('form' => $form));
    }

    public function deleteAction() {
        $ids = array_filter(explode(',',$this->request->getQuery('ids',0)));
        $id = (count($ids) == 0)?(int) $this->params()->fromRoute('id', 0):$ids;
        
        if (!$id) {
            $this->flashMessenger()->addErrorMessage('Invalid Ids..!!');
            return $this->redirect()->toRoute('portal/admins');
        }
        
        $this->getAdminsTable()->changeStatus($id, 4);
        $this->flashMessenger()->addSuccessMessage('User deleted successfully..!!');

        // Redirect to listing page
        return $this->redirect()->toRoute('portal/admins');
    }
    
    public function statusAction() {
        $ids = array_filter(explode(',',$this->request->getQuery('ids',0)));
        
        if (count($ids) == 0) {
            $this->flashMessenger()->addErrorMessage('Invalid Ids..!!');
            return $this->redirect()->toRoute('portal/admins');
        }
        
        $this->getAdminsTable()->changeStatus($ids, $this->request->getQuery('status',1));
        $this->flashMessenger()->addSuccessMessage('Status updated successfully..!!');

        // Redirect to listing page
        return $this->redirect()->toRoute('portal/admins');
    }

    private function generateSalt($length = 32) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }

}
