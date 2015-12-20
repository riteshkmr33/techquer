<?php

namespace Portal\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Portal\Model\Tags;

class TagsTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $adapter = $this->tableGateway->getAdapter();
    }

    public function fetchAll($paginate = true, $filter = array()) {
        if ($paginate == true) {
            $select = new Select('tags');
            $select->columns(array('*', new Expression('admins.displayName AS creator')));
            $select->join('admins', 'tags.createdBy = admins.adminId', array(), 'inner');
            $select->join('lookup_status', 'tags.status = lookup_status.statusId', array('label'), 'inner');

            /* Data filter code start here */
            if (count($filter) > 0) {
                (isset($filter['search']) && !empty($filter['search'])) ? $select->where("tags.tag like '%" . $filter['search'] . "%'") : '';
            }
            //echo str_replace('"','',$select->getSqlString()); //exit;
            /* Data filter code end here */

            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Tags());

            $paginatorAdapter = new DbSelect($select, $this->tableGateway->getAdapter(), $resultSetPrototype);
            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }

        return $this->tableGateway->select();
    }

    public function getTag($userNameorId) {

        $rowset = $this->tableGateway->select(array('tagId' => $userNameorId));

        $row = $rowset->current();

        if (!$row) {
            return false;
            //throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveTags(Tags $tag, $createdBy = '', $updatedBy = '') {
        $data = array(
            'tag' => $tag->tag,
            'status' => $tag->status,
        );

        ($createdBy != '') ? $data['createdBy'] = $createdBy : '';
        ($updatedBy != '') ? $data['updatedBy'] = $updatedBy : '';

        $id = (int) $tag->tagId;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            $data['updatedDate'] = date('Y-m-d h:i:s');
            if ($this->getTag($id)) {
                $this->tableGateway->update($data, array('tagId' => $id));
            } else {
                throw new \Exception('Tag does not exist');
            }
        }
    }

    public function deleteTag($id) {
        $this->tableGateway->delete(array('tagId' => $id));
    }

    public function changeStatus($ids, $status, $updatedBy) {
        $this->tableGateway->update(array('status' => $status, 'updatedBy' => $updatedBy, 'updatedDate' => date('Y-m-d h:i:s')), array('tagId' => $ids));
    }

}
