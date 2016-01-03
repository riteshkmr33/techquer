<?php

namespace Portal\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Admins implements InputFilterAwareInterface {

    public $adminId;
    public $userName;
    public $password;
    public $salt;
    public $email;
    public $displayName;
    public $roleId;
    public $deletePermission;
    public $status;
    public $createdDate;
    public $updatedDate;
    public $createdBy;
    public $updatedBy;
    public $creator;
    
    /* status table */
    public $label;
    
    /* roles table */
    public $role;
    
    /* role permission table */
    public $moduleId;
    public $sectionId;
    
    /* modules table */
    public $moduleName;
    
    /* section table */
    public $secton;
    
    protected $inputFilter;

    public function exchangeArray($data) {
        $this->adminId = (!empty($data['adminId'])) ? $data['adminId'] : null;
        $this->userName = (!empty($data['userName'])) ? $data['userName'] : null;
        $this->password = (!empty($data['password'])) ? $data['password'] : null;
        $this->salt = (!empty($data['salt'])) ? $data['salt'] : null;
        $this->email = (!empty($data['email'])) ? $data['email'] : null;
        $this->displayName = (!empty($data['displayName'])) ? $data['displayName'] : null;
        $this->roleId = (!empty($data['roleId'])) ? $data['roleId'] : null;
        $this->status = (!empty($data['status'])) ? $data['status'] : 0;
        $this->createdDate = (!empty($data['createdDate'])) ? $data['createdDate'] : null;
        $this->updatedDate = (!empty($data['updatedDate'])) ? $data['updatedDate'] : null;
        $this->createdBy = (!empty($data['createdBy'])) ? $data['createdBy'] : null;
        $this->updatedBy = (!empty($data['updatedBy'])) ? $data['updatedBy'] : null;
        $this->deletePermission = (!empty($data['deletePermission'])) ? $data['deletePermission'] : null;
        $this->creator = (!empty($data['creator'])) ? $data['creator'] : null;
        
        /* status table */
        $this->label = (!empty($data['label'])) ? $data['label'] : null;
        
        /* roles table */
        $this->role = (!empty($data['role'])) ? $data['role'] : null;
        
        /* role permission table */
        $this->moduleId = (!empty($data['moduleId'])) ? $data['moduleId'] : null;
        $this->sectionId = (!empty($data['sectionId'])) ? $data['sectionId'] : null;
        
        /* modules table */
        $this->moduleName = (!empty($data['moduleName'])) ? $data['moduleName'] : null;
        
        /* section table */
        $this->section = (!empty($data['section'])) ? $data['section'] : null;
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
                'name' => 'userName',
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
                            'max' => 50,
                        ),
                    )
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'password',
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
                            'max' => 150,
                        ),
                    )
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'salt',
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
                            'max' => 64,
                        ),
                    )
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'email',
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
                    ),
                    array(
                        'name' => 'EmailAddress'
                    )
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'displayName',
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
                            'max' => 100,
                        ),
                    )
                ),
            ));
            
            $inputFilter->add(array(
                'name' => 'roleId',
                'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Digits',
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
