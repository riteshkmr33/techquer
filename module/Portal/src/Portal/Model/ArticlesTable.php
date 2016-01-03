<?php

namespace Portal\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

use Portal\Model\Articles;

class ArticlesTable {
    
    private $articleTags;
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $adapter = $this->tableGateway->getAdapter();
        
        $this->articleTags = new TableGateway('articles_tags', $adapter);
    }
    
    public function fetchAll($paginate=true, $filter = array()) {
        if ($paginate == true) {
            $select = new Select('articles');
            $select->columns(array('*',new Expression('admins.displayName AS creator')));
            $select->join('categories', 'articles.catId = categories.catId',array('category'),'inner');
            $select->join('admins', 'articles.createdBy = admins.adminId',array(),'inner');
            $select->join('lookup_status', 'articles.status = lookup_status.statusId',array('label'),'inner');
            
            /* Data filter code start here */
            if (count($filter) > 0) {
                (isset($filter['search']) && !empty($filter['search']))?$select->where("articles.title like '%".$filter['search']."%' OR articles.summary like '%".$filter['search']."%' OR categories.category  like '%".$filter['search']."%'"):'';
            }
            //echo str_replace('"','',$select->getSqlString()); //exit;
            /* Data filter code end here */

            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Articles());

            $paginatorAdapter = new DbSelect($select, $this->tableGateway->getAdapter(), $resultSetPrototype);
            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }
        
        return $this->tableGateway->select();
    }
    
    public function getArticle($id) {
        
        $rowset = $this->tableGateway->select(array('articleId' => $id));
        
        $row = $rowset->current();
        
        if (!$row) {
            return false;
            //throw new \Exception("Could not find row $id");
        }
        return $row;
    }
    
    public function getArticleTags($id) {
        $tags = array();
        $select = $this->articleTags->getSql()->select();
        $select->join('tags','articles_tags.tagId = tags.tagId',array('tag'),'inner');
        $select->where(array('articleId' => $id));
        $resultSet = $this->articleTags->selectwith($select);
        
        foreach ($resultSet as $data) {
            $tags[$data->tagId] = $data->tag;
        }
        
        return $tags;
    }
    
    public function addTags($articleId, $tags) {
        $this->articleTags->delete(array('articleId' => $articleId));
        
        foreach ($tags as $tag) {
            $this->articleTags->insert(array('articleId' => $articleId, 'tagId' => $tag));
        }
    }
    public function addImages($articleId, $images) {
        $this->articleTags->delete(array('articleId' => $articleId));
        
        foreach ($images as $image) {
            $this->articleTags->insert(array('articleId' => $articleId, 'tagId' => $tag));
        }
    }

    public function saveArticles(Articles $article, $tags, $filePath, $createdBy = '', $updatedBy = '' )
    {
        $data = array(
            'catId' => $article->catId,
            'title' => $article->title,
            'summary' => $article->title,
            'metaTitle' => $article->metaTitle,
            'metaDescription' => $article->metaDescription,
            'metaKeywords' => $article->metaKeywords,
            'filePath' => $filePath,
            'status' => $article->status,
        );
        
        ($createdBy != '')?$data['createdBy'] = $createdBy:'';
        ($updatedBy != '')?$data['updatedBy'] = $updatedBy:'';
        
        $id = (int) $article->articleId;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $id = $this->tableGateway->lastInsertValue;
        } else {
            $data['updatedDate'] = date('Y-m-d h:i:s');
            if ($this->getArticle($id)) {
                $this->tableGateway->update($data, array('articleId' => $id));
            } else {
                throw new \Exception('Article does not exist');
            }
        }
        
        $this->addTags($id, $tags);  // updating article tags
    }
    
    public function deleteCategory($id)
    {
        $this->tableGateway->delete(array('articleId' => $id));
    }
    
    public function changeStatus($ids, $status)
    {
        $this->tableGateway->update(array('status' => $status), array('articleId' => $ids));
    }

}
