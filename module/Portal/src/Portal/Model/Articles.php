<?php

namespace Portal\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Articles implements InputFilterAwareInterface {

    public $articleId;
    public $catId;
    public $title;
    public $summary;
    public $metaTitle;
    public $metaDescription;
    public $metaKeywords;
    public $filePath;
    public $status;
    public $createdDate;
    public $updatedDate;
    public $createdBy;
    public $updatedBy;
    public $creator;
    
    /* status table */
    public $label;
    
    /* categories table */
    public $category;
    
    protected $inputFilter;

    public function exchangeArray($data) {
        $this->articleId = (!empty($data['articleId'])) ? $data['articleId'] : null;
        $this->catId = (!empty($data['catId'])) ? $data['catId'] : null;
        $this->title = (!empty($data['title'])) ? $data['title'] : null;
        $this->summary = (!empty($data['summary'])) ? $data['summary'] : null;
        $this->metaTitle = (!empty($data['metaTitle'])) ? $data['metaTitle'] : null;
        $this->metaDescription = (!empty($data['metaDescription'])) ? $data['metaDescription'] : null;
        $this->metaKeywords = (!empty($data['metaKeywords'])) ? $data['metaKeywords'] : null;
        $this->filePath = (!empty($data['filePath'])) ? $data['filePath'] : null;
        $this->status = (!empty($data['status'])) ? $data['status'] : 0;
        $this->createdDate = (!empty($data['createdDate'])) ? $data['createdDate'] : null;
        $this->updatedDate = (!empty($data['updatedDate'])) ? $data['updatedDate'] : null;
        $this->createdBy = (!empty($data['createdBy'])) ? $data['createdBy'] : null;
        $this->updatedBy = (!empty($data['updatedBy'])) ? $data['updatedBy'] : null;
        $this->creator = (!empty($data['creator'])) ? $data['creator'] : null;
        
        $this->label = (!empty($data['label'])) ? $data['label'] : null;
        
        /* categories table */
        $this->category = (!empty($data['category'])) ? $data['category'] : null;
    }
    
    // Add content to this method:
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function getInputFilter() {

        if (!$this->inputFilter) {

            $inputFilter = new InputFilter();
            
            $inputFilter->add(array(
                'name' => 'catId',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Digits',
                    )
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'title',
                'required' => true,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 255,
                        ),
                    )
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'metaTitle',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name' => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min' => 1,
                            'max' => 255,
                        ),
                    )
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'metaDescription',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'metaKeywords',
                'required' => false,
                'filters' => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'status',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Digits',
                    )
                ),
            ));
            
            $this->inputFilter = $inputFilter;
        }
        
        return $this->inputFilter;
    }

}
