<?php

namespace Portal\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Portal\Form\ArticleForm;
use Portal\Model\Articles;
use Zend\Validator\File\Size;
use Zend\Validator\File\ImageSize;
use Zend\Validator\File\IsImage;
use Zend\Session\Container;
use Zend\Http\Request;

class ArticlesController extends AbstractActionController {

    public $form;
    public $adminsTable;
    public $articlesTable;
    public $folderPath = '/Users/riteshkumar/Sites/techquer/public';
    public $errors = array();

    private function getForm($selectedTags) {
        if (!$this->form) {
            $this->form = new ArticleForm($this->getServiceLocator()->get('Portal\Model\CategoriesTable'), $this->getServiceLocator()->get('Portal\Model\TagsTable'), $selectedTags);
        }

        return $this->form;
    }

    private function getAdminsTable() {
        if (!$this->adminsTable) {
            $this->adminsTable = $this->getServiceLocator()->get('Portal\Model\AdminsTable');
        }

        return $this->adminsTable;
    }

    private function getArticlesTable() {
        if (!$this->articlesTable) {
            $this->articlesTable = $this->getServiceLocator()->get('Portal\Model\ArticlesTable');
        }

        return $this->articlesTable;
    }

    public function indexAction() {
        $search = $this->request->getQuery('search');
        $paginator = $this->getArticlesTable()->fetchAll(true, array('search' => $search));
        //echo '<pre>'; print_r($paginator->getTotalItemCount()); exit;
        $paginator->setCurrentPageNumber((int) $this->Params()->fromQuery('page', 1));
        $paginator->setItemCountPerPage(10);

        return new ViewModel(array('articles' => $paginator,
            'successMsgs' => $this->flashMessenger()->getCurrentSuccessMessages(),
            'errors' => $this->flashMessenger()->getCurrentErrorMessages()
        ));
    }

    public function addAction() {


        $form = $this->getForm(array());
        $request = $this->getRequest();

        if ($request->isPost()) {
            $filePath = '';
            $articles = new Articles;
            // Adding already exist validation on runtime
            $articles->getInputFilter()->get('title')->getValidatorChain()->attach(new \Zend\Validator\Db\NoRecordExists(array('table' => 'articles', 'field' => 'title', 'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'))));

            $form->setInputFilter($articles->getInputFilter());
            $tags = $request->getPost('tags');
            $form->setData($request->getPost());

            if ($form->isValid()) {
                
                /* image upload code starts here */
                $file = $request->getFiles();
                $fileAdapter = new \Zend\File\Transfer\Adapter\Http();
                $imageValidator = new IsImage();
                if ($imageValidator->isValid($file['file_url']['tmp_name'])) {
                    $fileParts = explode('.', $file['file_url']['name']);
                    $filter = new \Zend\Filter\File\Rename(array(
                        "target" => $this->folderPath."/images/articles/article." . $fileParts[1],
                        "randomize" => true,
                    ));

                    try {
                        $filePath = str_replace($this->folderPath, '',$filter->filter($file['file_url'])['tmp_name']);
                    } catch (\Exception $e) {
                        return new ViewModel(array('form' => $form, 'file_errors' => array($e->getMessage())));
                    }
                } else {
                    return new ViewModel(array('form' => $form, 'file_errors' => $imageValidator->getMessages()));
                }
                /* image upload code ends here */
                
                $articles->exchangeArray($form->getData());
                $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
                $this->getArticlesTable()->saveArticles($articles, $tags, $filePath, $loggedInUser->adminId, $loggedInUser->adminId);
                $this->flashMessenger()->addSuccessMessage('Article added successfully..!!');

                // Redirect to list of pages
                return $this->redirect()->toRoute('portal/articles');
            }
        }

        return new ViewModel(array('form' => $form));
    }

    public function editAction() {

        $id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            return $this->redirect()->toRoute('portal/articles');
        }

        if (!$articles = $this->getArticlesTable()->getArticle($id)) {
            $this->flashMessenger()->addErrorMessage('No article found..!!');
            return $this->redirect()->toRoute('portal/articles');
        }

        $form = $this->getForm(array_keys($this->getArticlesTable()->getArticleTags($id)));
        $form->bind($articles);
        $request = $this->getRequest();

        if ($request->isPost()) {
            $filePath = $request->getPost('oldFile');
            // Adding already exist validation on runtime excluding the current record
            $articles->getInputFilter()->get('title')->getValidatorChain()->attach(new \Zend\Validator\Db\NoRecordExists(array('table' => 'articles', 'field' => 'title', 'adapter' => $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'), 'exclude' => array('field' => 'articleId', 'value' => $id))));

            $form->setInputFilter($articles->getInputFilter());
            $tags = $request->getPost('tags');
            $form->setData($request->getPost());

            if ($form->isValid()) {
                
                /* image upload code starts here */
                $file = $request->getFiles();
                $fileAdapter = new \Zend\File\Transfer\Adapter\Http();
                $imageValidator = new IsImage();
                if ($imageValidator->isValid($file['file_url']['tmp_name'])) {
                    $fileParts = explode('.', $file['file_url']['name']);
                    $filter = new \Zend\Filter\File\Rename(array(
                        "target" => $this->folderPath."/images/articles/article." . $fileParts[1],
                        "randomize" => true,
                    ));

                    try {
                        @unlink($this->folderPath.$filePath);
                        $filePath = str_replace($this->folderPath, '',$filter->filter($file['file_url'])['tmp_name']);
                    } catch (\Exception $e) {
                        return new ViewModel(array('form' => $form, 'file_errors' => array($e->getMessage())));
                    }
                } else {
                    return new ViewModel(array('form' => $form, 'file_errors' => $imageValidator->getMessages()));
                }
                /* image upload code ends here */
                
                $loggedInUser = $this->getAdminsTable()->getAdmin($this->getServiceLocator()->get('AuthService')->getIdentity());
                $this->getArticlesTable()->saveArticles($form->getData(), $tags, $filePath, '', $loggedInUser->adminId);
                $this->flashMessenger()->addSuccessMessage('Article updated successfully..!!');

                // Redirect to listing pages
                return $this->redirect()->toRoute('portal/articles');
            }
        }

        return new ViewModel(array('form' => $form, 'article' => $articles));
    }

    public function deleteAction() {
        $ids = array_filter(explode(',', $this->request->getQuery('ids', 0)));
        $id = (count($ids) == 0) ? (int) $this->params()->fromRoute('id', 0) : $ids;

        if (!$id) {
            $this->flashMessenger()->addErrorMessage('Invalid Ids..!!');
            return $this->redirect()->toRoute('portal/articles');
        }

        $this->getArticlesTable()->changeStatus($id, 4);
        $this->flashMessenger()->addSuccessMessage('Article deleted successfully..!!');

        // Redirect to listing page
        return $this->redirect()->toRoute('portal/articles');
    }

    public function statusAction() {
        $ids = array_filter(explode(',', $this->request->getQuery('ids', 0)));

        if (count($ids) == 0) {
            $this->flashMessenger()->addErrorMessage('Invalid Ids..!!');
            return $this->redirect()->toRoute('portal/articles');
        }

        $this->getArticlesTable()->changeStatus($ids, $this->request->getQuery('status', 1));
        $this->flashMessenger()->addSuccessMessage('Status updated successfully..!!');

        // Redirect to listing page
        return $this->redirect()->toRoute('portal/articles');
    }

}
