<?php

/**
 * Login Form
 */

namespace Certificat\Form;

/**
 * Form class
 */
class LoginForm extends BaseForm
{

    /**
     * Constructor method for form class
     * 
     * @param null|string   $name   Name for form
     */
    public function __construct($name = 'login')
    {
        // we want to ignore the name passed
        parent::__construct($name);
    }

    /**
     * initializer method for form class
     * form creation has to be defined here because there is no service locator on construct
     * 
     */
    public function init()
    {
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'jq-validate');
        $this->add(array(
            'name' => 'email',
            'type' => 'Text',
            'attributes' => array(
                'size' => 30,
                'data-rule-maxlength' => 50,
                'maxsize' => 50,
                'data-rule-required' => true,
                'data-rule-email' => true,
            ),
        ));
        $this->add(array(
            'name' => 'password',
            'type' => 'Password',
            'attributes' => array(
                'size' => 30,
                'data-rule-minlength' => 4,
                'data-rule-maxlength' => 20,
                'maxsize' => 20,
                'data-rule-required' => true,
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
        ));
    }

}
