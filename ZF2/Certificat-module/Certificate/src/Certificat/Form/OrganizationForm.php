<?php

/**
 * Organization Form
 */

namespace Certificat\Form;

/**
 * Form class
 */
class OrganizationForm extends BaseForm
{

    /**
     * Constructor method for form class
     *
     * @param null|string   $name   Name for form
     */
    public function __construct($name = 'organization')
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
            'name' => 'name',
            'type' => 'Text',
            'attributes' => array(
                'size' => 100,
                'data-rule-maxlength' => 100,
                'maxsize' => 100,
                'data-rule-required' => true,
                'data-rule-nohtml' => true,
            ),
        ));
        $this->add(array(
            'name' => 'street',
            'type' => 'Text',
            'attributes' => array(
                'size' => 100,
                'data-rule-maxlength' => 100,
                'maxsize' => 100,
                'data-rule-required' => true,
                'data-rule-nohtml' => true,
            ),
        ));
        $this->add(array(
            'name' => 'house',
            'type' => 'Text',
            'attributes' => array(
                'size' => 10,
                'data-rule-maxlength' => 10,
                'maxsize' => 10,
                'data-rule-required' => true,
                'data-rule-nohtml' => true,
            ),
        ));
        $this->add(array(
            'name' => 'zip_code',
            'type' => 'Text',
            'attributes' => array(
                'size' => 10,
                'data-rule-maxlength' => 10,
                'maxsize' => 10,
                'data-rule-required' => true,
                'data-rule-nohtml' => true,
            ),
        ));
        $this->add(array(
            'name' => 'city',
            'type' => 'Text',
            'attributes' => array(
                'size' => 60,
                'data-rule-maxlength' => 60,
                'maxsize' => 60,
                'data-rule-required' => true,
                'data-rule-letterswithbasicpunc' => true,
                'data-rule-nohtml' => true,
            ),
        ));
        $this->add(array(
            'name' => 'country',
            'type' => 'Select',
            'options' => array(
                'required' => true,
                'value_options' => $this->getServiceLocator()->get('Country'),
            ),
            'attributes' => array(
                'data-rule-required' => true,
            ),
        ));
        $this->add(array(
            'name' => 'phone',
            'type' => 'Text',
            'attributes' => array(
                'size' => 30,
                'data-rule-maxlength' => 30,
                'maxsize' => 30,
                'data-rule-nohtml' => true,
            ),
        ));    
        $this->add(array(
            'name' => 'website',
            'type' => 'Text',
            'attributes' => array(
                'size' => 255,
                'data-rule-maxlength' => 255,
                'maxsize' => 255,
                'data-rule-url' => true,
                'data-rule-nohtml' => true,
            ),
        ));         
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
        ));
    }

}
