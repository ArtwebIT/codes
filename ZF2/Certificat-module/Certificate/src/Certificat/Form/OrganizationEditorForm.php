<?php

/**
 * Organization editor form
 */

namespace Certificat\Form;

/**
 * Form class
 */
class OrganizationEditorForm extends BaseForm
{

    /**
     * Constructor method for form class
     *
     * @param null|string   $name   Name for form
     */
    public function __construct($name = 'organization-editor')
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
        $this->setAttribute('method', 'post');
        $this->setAttribute('class', 'jq-validate');
        
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
            'name' => 'email',
            'type' => 'Text',
            'attributes' => array(
                'data-rule-maxlength' => 50,
                'maxsize' => 50,
                'data-rule-required' => true,
                'data-rule-email' => true,
                'data-rule-remote' => '/check-email',
            ),
        ));
        
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
        ));

    }

}
