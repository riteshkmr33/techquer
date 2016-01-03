<?php

namespace Portal\Form;

use Zend\Captcha;
use Zend\Form\Form;
use Portal\Model\CategoriesTable;

class CategoryForm extends Form {
    
    private $cats;
    
    public function __construct(CategoriesTable $cats) {
        parent::__construct();
        $this->cats = $cats;
        $this->setAttributes(array('method' => 'post', 'id' => 'categoryForm', 'class' => 'form-horizontal', 'action' => ''));
        
        $this->add(array(
            'name' => 'catId',
            'type' => 'Hidden',
        ));
        
        $this->add(array(
            'type' => 'text',
            'name' => 'category',
            'options' => array(
                'label' => 'Category',
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'category',
            ),
        ));
        
        $this->add(array(
            'name' => 'parentId',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Parent Category',
                'value_options' => $this->getCats(),
                'empty_option' => '--- Select Category ---'
            ),
            'attributes' => array(
                'class' => 'form-control',
            )
        ));

        $this->add(array(
            'name' => 'status',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Status',
                'value_options' => array( '1' => 'Active', '2' => 'Inactive', '4' => 'Deleted'),
                'empty_option' => '--- Select Status ---'
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

    function getCats()
    {
        $selectData = array(0 => 'Parent');
        $results = $this->cats->fetchAll(false);

        foreach ($results as $result) {
            $selectData[$result->catId] = $result->category;
        }

        return $selectData;
    }

}
