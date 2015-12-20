<?php

namespace Portal\Form;

use Zend\Captcha;
use Zend\Form\Form;

class ArticleForm extends Form {

    public function __construct() {
        parent::__construct();
        $this->setAttributes(array('method' => 'post', 'id' => 'loginForm', 'class' => 'form-horizontal', 'action' => ''));
        
        $this->add(array(
            'name' => 'adminId',
            'type' => 'Hidden',
        ));
        
        $this->add(array(
            'type' => 'text',
            'name' => 'displayName',
            'options' => array(
                'label' => 'Name',
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'displayName',
            ),
        ));

        $this->add(array(
            'type' => 'text',
            'name' => 'userName',
            'options' => array(
                'label' => 'Username',
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'userName',
            ),
        ));

        $this->add(array(
            'type' => 'password',
            'name' => 'password',
            'autocomplete' => 'off',
            'options' => array(
                'label' => 'Password',
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'password',
            ),
        ));

        $this->add(array(
            'type' => 'text',
            'name' => 'salt',
            'autocomplete' => 'off',
            'options' => array(
                'label' => 'Salt',
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'salt',
            ),
        ));

        $this->add(array(
            'type' => 'text',
            'name' => 'email',
            'options' => array(
                'label' => 'E-mail',
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'email',
            ),
        ));

        $this->add(array(
            'name' => 'status',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Status',
                'value_options' => array( '1' => 'Active', '3' => 'Suspended', '4' => 'Deleted'),
                'empty_option' => '--- Select Status ---'
            ),
            'attributes' => array(
                'class' => 'form-control',
            )
        ));

        $this->add(array(
            'name' => 'roleId',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Role',
                'value_options' => $this->getRoles(),
                'empty_option' => '--- Select Role ---'
            ),
            'attributes' => array(
                'class' => 'form-control',
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

    function getRoles() {
        $selectData = array('1' => 'Admin','2' => 'Sub-Admin');
        /* $results = $this->roles->fetchAll(false);

          foreach ($results as $result) {
          $selectData[$result->roleId] = ucwords($result->name);
          } */

        return $selectData;
    }

}
