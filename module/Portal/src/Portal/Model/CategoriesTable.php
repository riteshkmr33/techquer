<?php

namespace Portal\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

use Portal\Model\Categories;

class CategoriesTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $adapter = $this->tableGateway->getAdapter();
    }
    
    public function fetchAll($paginate=true, $filter = array()) {
        if ($paginate == true) {
            $select = new Select('categories');
            $select->columns(array('*',new Expression('admins.displayName AS creator, parentCat.category AS parent')));
            $select->join(array('parentCat'=>'categories'), 'categories.parentId = parentCat.catId',array(),'left');
            $select->join('admins', 'categories.createdBy = admins.adminId',array(),'inner');
            $select->join('lookup_status', 'categories.status = lookup_status.statusId',array('label'),'inner');
            
            /* Data filter code start here */
            if (count($filter) > 0) {
                (isset($filter['search']) && !empty($filter['search']))?$select->where("categories.category like '%".$filter['search']."%' OR parentCat.category like '%".$filter['search']."%' "):'';
            }
            //echo str_replace('"','',$select->getSqlString()); //exit;
            /* Data filter code end here */

            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Categories());

            $paginatorAdapter = new DbSelect($select, $this->tableGateway->getAdapter(), $resultSetPrototype);
            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }
        
        return $this->tableGateway->select();
    }
    
    public function getCategory($id) {
        
        $rowset = $this->tableGateway->select(array('catId' => $id));
        
        $row = $rowset->current();
        
        if (!$row) {
            return false;
            //throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveCategories(Categories $cat, $createdBy = '', $updatedBy = '' )
    {
        $data = array(
            'category' => $cat->category,
            'parentId' => $cat->parentId,
            'status' => $cat->status,
        );
        
        ($createdBy != '')?$data['createdBy'] = $createdBy:'';
        ($updatedBy != '')?$data['updatedBy'] = $updatedBy:'';
        
        $id = (int) $cat->catId;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            $data['updatedDate'] = date('Y-m-d h:i:s');
            if ($this->getCategory($id)) {
                $this->tableGateway->update($data, array('catId' => $id));
            } else {
                throw new \Exception('Category does not exist');
            }
        }
    }
    
    public function deleteCategory($id)
    {
        $this->tableGateway->delete(array('catId' => $id));
    }
    
    public function changeStatus($ids, $status)
    {
        $this->tableGateway->update(array('status' => $status), array('catId' => $ids));
    }

}
