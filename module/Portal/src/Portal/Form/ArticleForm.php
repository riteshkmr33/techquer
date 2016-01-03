<?php

namespace Portal\Form;

use Zend\Captcha;
use Zend\Form\Form;
use Portal\Model\CategoriesTable;
use Portal\Model\TagsTable;

class ArticleForm extends Form {

    private $cats;
    private $tags;
    private $selectedTags;

    public function __construct(CategoriesTable $cats, TagsTable $tags, $selectedTags) {
        parent::__construct();
        $this->cats = $cats;
        $this->tags = $tags;
        $this->selectedTags = $selectedTags;
        $this->setAttributes(array('method' => 'post', 'id' => 'articleForm', 'class' => 'form-horizontal', 'action' => '', 'enctype' => 'multipart/form-data'));

        $this->add(array(
            'name' => 'articleId',
            'type' => 'Hidden',
        ));

        $this->add(array(
            'type' => 'text',
            'name' => 'title',
            'options' => array(
                'label' => 'Title',
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'title',
            ),
        ));

        $this->add(array(
            'type' => 'text',
            'name' => 'metaTitle',
            'options' => array(
                'label' => 'Meta Title',
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'metaTitle',
            ),
        ));

        $this->add(array(
            'name' => 'summary',
            'type' => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Content',
            ),
            'attributes' => array(
                'class' => 'form-control editor',
                'id' => 'summary',
            ),
        ));

        $this->add(array(
            'name' => 'metaDescription',
            'type' => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Meta Description',
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'metaDescription',
            ),
        ));

        $this->add(array(
            'name' => 'metaKeywords',
            'type' => 'Zend\Form\Element\Textarea',
            'options' => array(
                'label' => 'Meta Keywords',
            ),
            'attributes' => array(
                'class' => 'form-control',
                'id' => 'metaKeywords',
            ),
        ));

        $this->add(array(
            'name' => 'catId',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Category',
                'value_options' => $this->getCats(),
                'empty_option' => '--- Select Category ---'
            ),
            'attributes' => array(
                'class' => 'form-control',
            )
        ));

        $this->add(array(
            'name' => 'tags',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Tags',
                'value_options' => $this->getTags(),
            ),
            'attributes' => array(
                'class' => ' select2',
                'multiple' => 'multiple',
                'value' => $this->selectedTags
            )
        ));

        $this->add(array(
            'name' => 'status',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => 'Status',
                'value_options' => array('1' => 'Active', '2' => 'Inactive', '4' => 'Deleted'),
                'empty_option' => '--- Select Status ---'
            ),
            'attributes' => array(
                'class' => 'form-control',
                'value' => 1
            )
        ));

        $this->add(array(
            'name' => 'file_url',
            'attributes' => array(
                'type' => 'file',
                'class' => 'filestyle'
            ),
            'options' => array(
                'label' => 'Image',
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

    function getCats() {
        $selectData = array();
        $results = $this->cats->fetchAll(false);

        foreach ($results as $result) {
            $selectData[$result->catId] = $result->category;
        }

        return $selectData;
    }

    function getTags() {
        $selectData = array();
        $results = $this->tags->fetchAll(false);

        foreach ($results as $result) {
            $selectData[$result->tagId] = $result->tag;
        }

        return $selectData;
    }

}
