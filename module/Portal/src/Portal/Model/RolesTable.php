<?php

namespace Portal\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Portal\Model\Roles;

class RolesTable {

    private $modules;
    private $sections;
    private $rolePermissions;
    protected $tableGateway;

    public function __construct(TableGateway $tableGateway) {
        $this->tableGateway = $tableGateway;
        $adapter = $this->tableGateway->getAdapter();

        $this->modules = new TableGateway('modules', $adapter);
        $this->sections = new TableGateway('sections', $adapter);
        $this->rolePermissions = new TableGateway('role_permissions', $adapter);
    }

    public function fetchAll($paginate = true, $filter = array()) {
        if ($paginate == true) {
            $select = new Select('roles');
            $select->columns(array('*', new Expression('admins.displayName AS creator')));
            $select->join('admins', 'roles.createdBy = admins.adminId', array(), 'inner');
            $select->join('lookup_status', 'roles.status = lookup_status.statusId', array('label'), 'inner');

            /* Data filter code start here */
            if (count($filter) > 0) {
                (isset($filter['search']) && !empty($filter['search'])) ? $select->where("roles.role like '%" . $filter['search'] . "%'") : '';
            }
            //echo str_replace('"','',$select->getSqlString()); //exit;
            /* Data filter code end here */

            $resultSetPrototype = new ResultSet();
            $resultSetPrototype->setArrayObjectPrototype(new Roles());

            $paginatorAdapter = new DbSelect($select, $this->tableGateway->getAdapter(), $resultSetPrototype);
            $paginator = new Paginator($paginatorAdapter);

            return $paginator;
        }

        return $this->tableGateway->select();
    }

    public function getRole($userNameorId) {

        $rowset = $this->tableGateway->select(array('roleId' => $userNameorId));

        $row = $rowset->current();

        if (!$row) {
            return false;
            //throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getModules() {
        return $this->modules->select(array('status' => 1))->buffer();
    }

    public function getSections() {
        return $this->sections->select(array('status' => 1))->buffer();
    }
    
    public function getRolePermissions($roleId) {
        $permissions = array();
        $select = $this->tableGateway->getSql()->select();
        $select->join('role_permissions', 'roles.roleId = role_permissions.roleId', array('moduleId', 'sectionId'), 'left');
        $select->where("roles.roleId = $roleId");
        $raw = $this->tableGateway->selectwith($select);

        foreach ($raw as $value) {
            !(isset($permissions[$value->moduleId]))?$permissions[$value->moduleId] = array():'';
            array_push($permissions[$value->moduleId],$value->sectionId);
        }
        
        return $permissions;
    }

    public function assignPermissions($roleId, $permissions, $createdBy = '', $updatedBy = '') {

        if (is_numeric($roleId)) {
            $this->rolePermissions->delete(array('roleId' => $roleId));

            foreach ($permissions as $key => $section) {
                $section = array_filter($section);
                foreach ($section as $sectionId=>$val) {
                    $modulePermissions['moduleId'] = $key;
                    $modulePermissions['roleId'] = $roleId;
                    $modulePermissions['sectionId'] = $sectionId;
                    $this->rolePermissions->insert($modulePermissions);
                }
            }
        }
    }

    public function saveRoles(Roles $role, $permissions, $createdBy = '', $updatedBy = '') {
        $data = array(
            'role' => $role->role,
            'status' => $role->status,
        );

        ($createdBy != '') ? $data['createdBy'] = $createdBy : '';
        ($updatedBy != '') ? $data['updatedBy'] = $updatedBy : '';

        $id = (int) $role->roleId;
        if ($id == 0) {
            $this->tableGateway->insert($data);
            $roleId = $this->tableGateway->lastInsertValue;
            $this->assignPermissions($roleId, $permissions, $createdBy, $updatedBy);
        } else {
            $data['updatedDate'] = date('Y-m-d h:i:s');
            if ($this->getRole($id)) {
                $this->tableGateway->update($data, array('roleId' => $id));
                $this->assignPermissions($id, $permissions, $createdBy, $updatedBy);
            } else {
                throw new \Exception('Role does not exist');
            }
        }
    }

    public function deleteRole($id) {
        $this->tableGateway->delete(array('roleId' => $id));
    }

    public function changeStatus($ids, $status, $updatedBy) {
        $this->tableGateway->update(array('status' => $status, 'updatedBy' => $updatedBy, 'updatedDate' => date('Y-m-d h:i:s')), array('roleId' => $ids));
    }

}
