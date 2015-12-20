<?php

namespace Portal\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Tags implements InputFilterAwareInterface {

    public $tagId;
    public $tag;
    public $status;
    public $createdDate;
    public $updatedDate;
    public $createdBy;
    public $updatedBy;
    public $creator;
    
    /* status table */
    public $label;
    
    protected $inputFilter;

    public function exchangeArray($data) {
        $this->tagId = (!empty($data['tagId'])) ? $data['tagId'] : null;
        $this->tag = (!empty($data['tag'])) ? $data['tag'] : null;
        $this->status = (!empty($data['status'])) ? $data['status'] : 0;
        $this->createdDate = (!empty($data['createdDate'])) ? $data['createdDate'] : null;
        $this->updatedDate = (!empty($data['updatedDate'])) ? $data['updatedDate'] : null;
        $this->createdBy = (!empty($data['createdBy'])) ? $data['createdBy'] : null;
        $this->updatedBy = (!empty($data['updatedBy'])) ? $data['updatedBy'] : null;
        $this->creator = (!empty($data['creator'])) ? $data['creator'] : null;
        
        $this->label = (!empty($data['label'])) ? $data['label'] : null;
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
                'name' => 'tag',
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
