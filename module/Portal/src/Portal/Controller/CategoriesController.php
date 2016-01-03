<?php

namespace Portal\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Portal\Form\CategoryForm;
use Portal\Model\Categories;

class CategoriesController extends AbstractActionController {

    public $form;
    public $adminsTable;
    public $categoriesTable;
    public $errors = array();

    private function getForm() {
        if (!$this->form) {
            $this->form = new CategoryForm($this->getServiceLocator()->get('Portal\Model\CategoriesTable'));
        }

        return $this->form;
    }
    
    private function getAdminsTable() {
        if (!$this->adminsTable) {
            $this->adminsTable = $this->getServiceLocator()->get('Portal\Model\AdminsTable');
        }

        return $this->adminsTable;
    }

    private function getCategoriesTable() {
        if (!$this->categoriesTable) {
            $this->categoriesTable = $this->getServiceLocator()->get('Portal\Model\CategoriesTable');
        }

        return $this->categoriesTable;
    }

    public function indexAction() {
        $search = $this->request->getQuery('search');
        $paginator = $this->getCategoriesTable()->fetchAll(true, array('search'=>$search));
        //echo '<pre>'; print_r($paginator->getTotalItemCount()); exit;
        $paginator->setCurrentPageNumber((int) $this->Params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);

        return new ViewModel(array('categories' => $paginator,
            'successMsgs' => $this->flashMessenger()->getCurrentSuccessMessages(),
            'errors' => $this->flashMessenger()->getCurrentErrorMessages()
        ));
    }

    public function addAction() {


        $form = $this->getForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $cats = new Categories;
            // Adding already exist validation on runtime
            $cats->getInputFilter()->get('category')->getValidatorChain()->attach(new \Zend\Validator\Db\NoRecordExists(array('table' => 'categories', 'field' => 'category', 'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'))));
            
            $form->setInputFilter($cats->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $cats->exchangeArray($form->getData());
                $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
                $this->getCategoriesTable()->saveCategories($cats, $loggedInUser->adminId, $loggedInUser->adminId);
                $this->flashMessenger()->addSuccessMessage('Category added successfully..!!');

                // Redirect to list of pages
                return $this->redirect()->toRoute('portal/categories');
            }
        }

        return new ViewModel(array('form' => $form));
    }

    public function editAction() {

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('portal/categories');
        }

        if (!$cats = $this->getCategoriesTable()->getCategory($id)) {
            $this->flashMessenger()->addErrorMessage('No category found..!!');
            return $this->redirect()->toRoute('portal/categories');
        }

        $form = $this->getForm();
        $form->bind($cats);
        $request = $this->getRequest();

        if ($request->isPost()) {

            // Adding already exist validation on runtime excluding the current record
            $cats->getInputFilter()->get('category')->getValidatorChain()->attach(new \Zend\Validator\Db\NoRecordExists(array('table' => 'categories', 'field' => 'category', 'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), 'exclude' => array('field' => 'catId', 'value' => $id))));

            $form->setInputFilter($cats->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
                $this->getCategoriesTable()->saveCategories($form->getData(), '', $loggedInUser->adminId);
                $this->flashMessenger()->addSuccessMessage('Category updated successfully..!!');

                // Redirect to listing pages
                return $this->redirect()->toRoute('portal/categories');
            }
        }

        return new ViewModel(array('form' => $form));
    }

    public function deleteAction() {
        $ids = array_filter(explode(',',$this->request->getQuery('ids',0)));
        $id = (count($ids) == 0)?(int) $this->params()->fromRoute('id', 0):$ids;
        
        if (!$id) {
            $this->flashMessenger()->addErrorMessage('Invalid Ids..!!');
            return $this->redirect()->toRoute('portal/categories');
        }
        
        $this->getCategoriesTable()->changeStatus($id, 4);
        $this->flashMessenger()->addSuccessMessage('Category deleted successfully..!!');

        // Redirect to listing page
        return $this->redirect()->toRoute('portal/categories');
    }
    
    public function statusAction() {
        $ids = array_filter(explode(',',$this->request->getQuery('ids',0)));
        
        if (count($ids) == 0) {
            $this->flashMessenger()->addErrorMessage('Invalid Ids..!!');
            return $this->redirect()->toRoute('portal/categories');
        }
        
        $this->getCategoriesTable()->changeStatus($ids, $this->request->getQuery('status',1));
        $this->flashMessenger()->addSuccessMessage('Status updated successfully..!!');

        // Redirect to listing page
        return $this->redirect()->toRoute('portal/categories');
    }

}
