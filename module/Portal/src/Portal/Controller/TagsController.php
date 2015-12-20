<?php

namespace Portal\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Portal\Form\TagForm;
use Portal\Model\Tags;

class TagsController extends AbstractActionController {

    public $form;
    public $tagsTable;
    public $adminsTable;
    public $errors = array();

    private function getForm() {
        if (!$this->form) {
            $this->form = new TagForm();
        }

        return $this->form;
    }

    private function getTagsTable() {
        if (!$this->tagsTable) {
            $this->tagsTable = $this->getServiceLocator()->get('Portal\Model\TagsTable');
        }

        return $this->tagsTable;
    }
    
    private function getAdminsTable() {
        if (!$this->adminsTable) {
            $this->adminsTable = $this->getServiceLocator()->get('Portal\Model\AdminsTable');
        }

        return $this->adminsTable;
    }

    public function indexAction() {
        $search = $this->request->getQuery('search');
        $paginator = $this->getTagsTable()->fetchAll(true, array('search'=>$search));
        //echo '<pre>'; print_r($paginator->getTotalItemCount()); exit;
        $paginator->setCurrentPageNumber((int) $this->Params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);

        return new ViewModel(array('tags' => $paginator,
            'successMsgs' => $this->flashMessenger()->getCurrentSuccessMessages(),
            'errors' => $this->flashMessenger()->getCurrentErrorMessages()
        ));
    }

    public function addAction() {


        $form = $this->getForm();
        $request = $this->getRequest();

        if ($request->isPost()) {
            $tags = new Tags;
            // Adding already exist validation on runtime
            $tags->getInputFilter()->get('tag')->getValidatorChain()->attach(new \Zend\Validator\Db\NoRecordExists(array('table' => 'tags', 'field' => 'tag', 'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'))));
            
            $form->setInputFilter($tags->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {
                $tags->exchangeArray($form->getData());
                $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
                $this->getTagsTable()->saveTags($tags, $loggedInUser->adminId, $loggedInUser->adminId);
                $this->flashMessenger()->addSuccessMessage('Tag added successfully..!!');

                // Redirect to listing
                return $this->redirect()->toRoute('portal/tags');
            }
        }

        return new ViewModel(array('form' => $form));
    }

    public function editAction() {

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('portal/tags');
        }

        if (!$tags = $this->getTagsTable()->getTag($id)) {
            $this->flashMessenger()->addErrorMessage('No tag found..!!');
            return $this->redirect()->toRoute('portal/tags');
        }

        $form = $this->getForm();
        $form->bind($tags);
        $request = $this->getRequest();

        if ($request->isPost()) {

            // Adding already exist validation on runtime excluding the current record
            $tags->getInputFilter()->get('tag')->getValidatorChain()->attach(new \Zend\Validator\Db\NoRecordExists(array('table' => 'tags', 'field' => 'tag', 'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), 'exclude' => array('field' => 'tagId', 'value' => $id))));
            
            $form->setInputFilter($tags->getInputFilter());
            $form->setData($request->getPost());

            if ($form->isValid()) {

                $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
                $this->getTagsTable()->saveTags($form->getData(), '', $loggedInUser->adminId);
                $this->flashMessenger()->addSuccessMessage('Tag updated successfully..!!');

                // Redirect to listing
                return $this->redirect()->toRoute('portal/tags');
            }
        }

        return new ViewModel(array('form' => $form));
    }

    public function deleteAction() {
        $ids = array_filter(explode(',',$this->request->getQuery('ids',0)));
        $id = (count($ids) == 0)?(int) $this->params()->fromRoute('id', 0):$ids;
        
        if (!$id) {
            $this->flashMessenger()->addErrorMessage('Invalid Ids..!!');
            return $this->redirect()->toRoute('portal/tags');
        }
        
        $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
        $this->getTagsTable()->changeStatus($id, 4, $loggedInUser->adminId);
        $this->flashMessenger()->addSuccessMessage('Tag(s) deleted successfully..!!');

        // Redirect to listing
        return $this->redirect()->toRoute('portal/tags');
    }
    
    public function statusAction() {
        $ids = array_filter(explode(',',$this->request->getQuery('ids',0)));
        
        if (count($ids) == 0) {
            $this->flashMessenger()->addErrorMessage('Invalid Ids..!!');
            return $this->redirect()->toRoute('portal/tags');
        }
        
        $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
        $this->getTagsTable()->changeStatus($ids, $this->request->getQuery('status',1), $loggedInUser->adminId);
        $this->flashMessenger()->addSuccessMessage('Status updated successfully..!!');

        // Redirect to listing
        return $this->redirect()->toRoute('portal/tags');
    }

}
