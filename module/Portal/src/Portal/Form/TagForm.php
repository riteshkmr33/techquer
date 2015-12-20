<?php

namespace Portal\Form;

use Zend\Captcha;
use Zend\Form\Form;

class TagForm extends Form {

    public function __construct() {
        parent::__construct();
        $this->setAttributes(array('method' => 'post', 'id' => 'tagForm', 'class' => 'form-horizontal', 'action' => ''));
        
        $this->add(array(
            'name' => 'tagId',
            'type' => 'Hidden',
        ));
        
        $this->add(array(
            'type' => 'text',
            'name' => 'tag',
            'options' => array(
                'label' => 'Tag Name',
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'tag',
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
