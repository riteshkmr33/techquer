<?php

namespace Portal\Form;

use Zend\Captcha;
use Zend\Form\Form;

class RoleForm extends Form {

    public function __construct() {
        parent::__construct();
        $this->setAttributes(array('method' => 'post', 'id' => 'roleForm', 'class' => 'form-horizontal', 'action' => ''));
        
        $this->add(array(
            'name' => 'roleId',
            'type' => 'Hidden',
        ));
        
        $this->add(array(
            'type' => 'text',
            'name' => 'role',
            'options' => array(
                'label' => 'Role Name',
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'role',
            ),
        ));

        $this->add(array(
            'name' => 'status',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Status',
                'value_options' => array( '1' => 'Active', '2' => 'Inactive', '4' => 'Deleted'),
                'empty_option' => '--- Select Status ---',
                'default' => 1
            ),
            'attributes' => array(
                'class' => 'form-control',
                'value' => 1
            )
        ));

        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Login'
            ),
        ));
    }

}
