<?php

namespace Portal\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Portal\Form\RoleForm;
use Portal\Model\Roles;
use Zend\InputFilter\InputFilter;

class RolesController extends AbstractActionController {

    public $form;
    public $fields;
    public $sections;
    public $rolesTable;
    public $adminsTable;
    public $errors = array();

    private function getForm() {
        if (!$this->form) {
            $this->form = new RoleForm();
        }

        $filter = new InputFilter;

        $modules = $this->getRolesTable()->getModules();
        $this->sections = $this->getRolesTable()->getSections();

        $filter->add(array('name' => 'role', 'required' => true));
        $filter->add(array('name' => 'status', 'required' => true));

        foreach ($modules as $module) {
            $this->fields[$module->moduleId] = array('name'=>$module->moduleName, 'sections' => array());
            foreach ($this->sections as $section) {
                $this->form->add(array(
                    'type' => 'Zend\Form\Element\Checkbox',
                    'name' => 'permissions[' . $module->moduleId . ']['.$section->sectionId.']',
                ));

                $filter->add(array('name' => 'permissions[' . $module->moduleId . ']['.$section->sectionId.']', 'required' => false,));
                $this->fields[$module->moduleId]['sections'][$section->sectionId] = $section->section;
            }
        }

        $this->form->setInputFilter($filter);

        return $this->form;
    }

    private function getRolesTable() {
        if (!$this->rolesTable) {
            $this->rolesTable = $this->getServiceLocator()->get('Portal\Model\RolesTable');
        }

        return $this->rolesTable;
    }

    private function getAdminsTable() {
        if (!$this->adminsTable) {
            $this->adminsTable = $this->getServiceLocator()->get('Portal\Model\AdminsTable');
        }

        return $this->adminsTable;
    }

    public function indexAction() {
        $search = $this->request->getQuery('search');
        $paginator = $this->getRolesTable()->fetchAll(true, array('search' => $search));
        //echo '<pre>'; print_r($paginator->getTotalItemCount()); exit;
        $paginator->setCurrentPageNumber((int) $this->Params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);

        return new ViewModel(array('roles' => $paginator,
            'successMsgs' => $this->flashMessenger()->getCurrentSuccessMessages(),
            'errors' => $this->flashMessenger()->getCurrentErrorMessages()
        ));
    }

    public function addAction() {


        $form = $this->getForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $roles = new Roles;
            
            $form->setData($request->getPost());
            $permissions = $request->getPost('permissions');

            if ($form->isValid()) {
                $roles->exchangeArray($form->getData());
                $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
                $this->getRolesTable()->saveRoles($roles, $permissions, $loggedInUser->adminId, $loggedInUser->adminId);
                $this->flashMessenger()->addSuccessMessage('Role added successfully..!!');

                // Redirect to listing
                return $this->redirect()->toRoute('portal/roles');
            }
        }

        return new ViewModel(array('form' => $form, 'fields' => $this->fields, 'sections' => $this->sections));
    }

    public function editAction() {

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('portal/roles');
        }

        if (!$roles = $this->getRolesTable()->getRole($id)) {
            $this->flashMessenger()->addErrorMessage('No role found..!!');
            return $this->redirect()->toRoute('portal/roles');
        }

        $form = $this->getForm();
        $request = $this->getRequest();
        $rights = $this->getRolesTable()->getRolePermissions($id);

        if ($request->isPost()) {

            $permissions = $request->getPost('permissions');
            
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $roles->exchangeArray($form->getData());
                $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
                
                $this->getRolesTable()->saveRoles($roles, $permissions, '', $loggedInUser->adminId);
                $this->flashMessenger()->addSuccessMessage('Role updated successfully..!!');

                // Redirect to listing
                return $this->redirect()->toRoute('portal/roles');
            } else {
                $this->errors = $form->getMessages();
            }
        }
        $form->bind($roles);

        return new ViewModel(array('form' => $form,'permissions' => $rights,'fields' => $this->fields, 'sections' => $this->sections));
    }

    public function deleteAction() {
        $ids = array_filter(explode(',', $this->request->getQuery('ids', 0)));
        $id = (count($ids) == 0) ? (int) $this->params()->fromRoute('id', 0) : $ids;

        if (!$id) {
            $this->flashMessenger()->addErrorMessage('Invalid Ids..!!');
            return $this->redirect()->toRoute('portal/roles');
        }

        $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
        $this->getRolesTable()->changeStatus($id, 4, $loggedInUser->adminId);
        $this->flashMessenger()->addSuccessMessage('Role(s) deleted successfully..!!');

        // Redirect to listing
        return $this->redirect()->toRoute('portal/roles');
    }

    public function statusAction() {
        $ids = array_filter(explode(',', $this->request->getQuery('ids', 0)));

        if (count($ids) == 0) {
            $this->flashMessenger()->addErrorMessage('Invalid Ids..!!');
            return $this->redirect()->toRoute('portal/roles');
        }

        $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
        $this->getRolesTable()->changeStatus($ids, $this->request->getQuery('status', 1), $loggedInUser->adminId);
        $this->flashMessenger()->addSuccessMessage('Status updated successfully..!!');

        // Redirect to listing
        return $this->redirect()->toRoute('portal/roles');
    }

}
