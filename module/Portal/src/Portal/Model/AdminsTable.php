<?php

namespace Portal\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

use Portal\Model\Admins;

class AdminsTable {

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $adapter = $this->tableGateway->getAdapter();
    }
    
    public function fetchAll($paginate=true, $filter = array()) {
        if ($paginate == true) {
            $select = new Select('admins');
            $select->columns(array('*',new Expression('creators.displayName AS creator')));
            $select->join(array('creators'=>'admins'), 'admins.createdBy = creators.adminId',array(),'inner');
            $select->join('lookup_status', 'admins.status = lookup_status.statusId',array('label'),'inner');
            
            /* Data filter code start here */
            if (count($filter) > 0) {

                // Filter code goes here
                //echo str_replace('"','',$select->getSqlString()); //exit;
            }
            /* Data filter code end here */

            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Admins());

            $paginatorAdapter = new DbSelect($select, $this->tableGateway->getAdapter(), $resultSetPrototype);
            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }
        
        return $this->tableGateway->select();
    }
    
    public function getAdmin($userNameorId) {
        
        if (is_numeric($userNameorId)) {
            $rowset = $this->tableGateway->select(array('adminId' => $userNameorId));
        } else {
            $rowset = $this->tableGateway->select(array('userName' => $userNameorId));
        }
        
        $row = $rowset->current();
        
        if (!$row) {
            return false;
            //throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getPermissionsByUsername($username) {
        $permissions = array();
        if ($username != '') {
            
            $select = $this->tableGateway->getSql()->select();
            $select->join('roleModules','users.roleId = roleModules.roleId',array('moduleId', 'create', 'read', 'update', 'delete'),'left');
            $select->join('modules','modules.moduleId = roleModules.moduleId',array('name'),'left');
            $select->where("username = '$username'");
            //echo str_replace('"','',$select->getSqlString()); exit;
            
            $raw = $this->tableGateway->selectwith($select);
            
            foreach ($raw as $value) {
                $permissions[$value->moduleId] = array('create' => $value->create, 'read' => $value->read, 'update' => $value->update, 'delete' => $value->delete, 'name' => $value->name);
            }
        }
        return $permissions;
    }
    
    public function saveAdmins(Admins $admin, $createdBy = '', $updatedBy = '' )
    {
        $data = array(
            'userName' => $admin->userName,
            'displayName' => $admin->displayName,
            'email' => $admin->email,
            'salt' => $admin->salt,
            'roleId' => $admin->roleId,
            'status' => $admin->status,
        );
        
        ($createdBy != '')?$data['createdBy'] = $createdBy:'';
        ($updatedBy != '')?$data['updatedBy'] = $updatedBy:'';
        
        $id = (int) $admin->adminId;
        if ($id == 0) {
            $data['password'] = SHA1($admin->password.$admin->salt);
            $this->tableGateway->insert($data);
        } else {
            ($admin->password != '')?$data['password'] = SHA1($admin->password.$admin->salt):'';
            $data['updatedDate'] = date('Y-m-d h:i:s');
            if ($this->getAdmin($id)) {
                $this->tableGateway->update($data, array('adminId' => $id));
            } else {
                throw new \Exception('Admin does not exist');
            }
        }
    }
    
    public function deleteAdmin($id)
    {
        $this->tableGateway->delete(array('adminId' => $id, 'deletePermission' => 1));
    }
    
    public function changeStatus($ids, $status)
    {
        $this->tableGateway->update(array('status' => $status), array('adminId' => $ids));
    }

}