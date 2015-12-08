<?php
namespace Portal\Form;

 use Zend\Captcha;
 use Zend\Form\Form;
  
class LoginForm extends Form
{
    public function __construct() 
    {
        parent::__construct();
        $this->setAttributes(array('method' => 'post', 'id' => 'loginForm', 'class'=>'form-horizontal m-t-20', 'action' => ''));
        
        $this->add(array(
            'type' => 'text',
            'name' => 'username',
            'options' => array(
                'label' => 'Username',
            ),
            'attributes' => array(
                'required' => 'required',
                'class' => 'form-control required',
                'id' => 'username',
                'placeholder' => 'Username'
            ),
        ));
        
        $this->add(array(
            'type'  => 'password',
            'name' => 'password',
            'autocomplete'  => 'off',
            'options' => array(
                'label' => 'Password',
            ),
            'attributes' => array(
                'required' => 'required',
                'class' => 'form-control required',
                'id' => 'password',
            ),
        ));
        
        $this->add(array(
            'type'  => 'text',
            'name' => 'loginKey',
            'autocomplete'  => 'off',
            'options' => array(
                'label' => 'Key',
            ),
            'attributes' => array(
                'required' => 'required',
                'class' => 'form-control required',
                'id' => 'loginKey',
                'placeholder' => 'Key'
            ),
        ));
        
        $this->add(array(
            'type' => 'Zend\Form\Element\Checkbox',
            'name' => 'rememberme',
            'attributes' => array(
                'type'  => 'checkbox',
                'name'  => 'rememberme',
                'id'  => 'remember-me',
            ),
            'options' => array(
                'label' => 'Remember me',
                'checked_value' => '1',
                'unchecked_value' => '0',
            ),
            
        ));
        
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Login'
            ),
        ));
    }
}