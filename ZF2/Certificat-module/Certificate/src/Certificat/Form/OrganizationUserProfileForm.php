<?php

/**
 * User Profile Form
 */

namespace Certificat\Form;

/**
 * Form class
 */
class OrganizationUserProfileForm extends BaseForm
{

    /**
     * Constructor method for form class
     *
     * @param null|string   $name   Name for form
     */
    public function __construct($name = 'organization-user-profile')
    {
        parent::__construct($name);
    }

    /**
     * initializer method for form class
     * form creation has to be defined here because there is no service locator on construct
     *
     */
    public function init()
    {
        $this->setAttributes(array(
            'method' => 'post',
            'class' => 'jq-validate'
        ));
        
        $this->add(array(
            'name' => 'id',
            'type' => 'Hidden',
        ));

        $this->add(array(
            'name' => 'first_name',
            'type' => 'Text',
            'attributes' => array(
                'data-rule-maxlength' => 50,
                'maxsize' => 50,
                'data-rule-required' => true,
                'data-rule-letterswithbasicpunc' => true,
            ),
        ));

        $this->add(array(
            'name' => 'last_name',
            'type' => 'Text',
            'attributes' => array(
                'data-rule-maxlength' => 50,
                'maxsize' => 50,
                'data-rule-required' => true,
                'data-rule-letterswithbasicpunc' => true,
            ),
        ));

        $this->add(array(
            'name' => 'sex',
            'type' => 'Select',
            'options' => array(
                'value_options' => array(
                    'm' => $this->getTranslator()->translate('Male'),
                    'w' => $this->getTranslator()->translate('Female'),
                ),
                'required' => true,
            ),
            'attributes' => array(
                'data-rule-required' => true,
            ),
        ));

        $this->add(array(
            'name' => 'password',
            'type' => 'Password',
            'attributes' => array(
                'id' => 'password',
                'size' => 30,
                'data-rule-minlength' => 8,
                'data-rule-maxlength' => 20,
                'maxsize' => 20,
                'data-rule-password' => true,
            ),
        ));

        $this->add(array(
            'name' => 'password_check',
            'type' => 'Password',
            'attributes' => array(
                'size' => 30,
                'data-rule-equalTo' => '#password',
                'data-rule-minlength' => 8,
                'data-rule-maxlength' => 20,
                'maxsize' => 20,
                'data-rule-password' => true,
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
        ));
    }

}
