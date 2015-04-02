<?php

/**
 * Competence Form
 */

namespace Certificat\Form;

use Certificat\Model\Template;

/**
 * Form class
 */
class TemplateForm extends BaseForm
{

    /**
     * Constructor method for form class
     *
     * @param null|string   $name   Name for form
     */
    public function __construct($name = 'template')
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
            'name' => 'type',
            'type' => 'Select',
            'options' => array(
                'required' => true,
                'value_options' => Template::getTypesOptions(),
            ),
            'attributes' => array(
                'data-rule-required' => true,
            ),
        ));

        // Options will be set later
        $this->add(array(
            'name' => 'competences',
            'type' => 'Select',
            'attributes' => array(
                'multiple' => 'multiple',
                'data-rule-required' => true,
            ),
            'options' => array(
                'required' => true,
                'disable_inarray_validator' => true,
            ),
        ));

        $this->add(array(
            'name' => 'user_id',
            'type' => 'hidden',
        ));

        $this->add(array(
            'name' => 'organization_id',
            'type' => 'hidden',
        ));

        // For all persons. <name1>;<name2>
        $this->add(array(
            'name' => 'persons_in_charge',
            'type' => 'hidden',
        ));

        $this->add(array(
            'name' => 'additional_comment',
            'type' => 'Textarea',
            'attributes' => array(
                'data-rule-nohtml' => true,
            ),
        ));

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
        ));
    }

    /**
     * Set select options for competences
     *
     * @param array $options
     */
    public function setCompetencesOptions($options)
    {
        $this->get('competences')->setValueOptions($options);
        
        return $this;
    }

    /**
     * Set selected values for competences
     *
     * @param array $values
     */
    public function setCompetencesValues($values)
    {
        $this->get('competences')->setValue($values);
        
        return $this;
    }    
}
