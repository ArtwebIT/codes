<?php

/**
 * Competence Form
 */

namespace Certificat\Form;

/**
 * Form class
 */
class CompetenceForm extends BaseForm
{

    /**
     * Languages for multilanguage fields
     * 
     * @var array
     */
    protected $languages = array('fr', 'de', 'en');

    /**
     * Constructor method for form class
     *
     * @param null|string   $name   Name for form
     */
    public function __construct($name = 'competence')
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
            'name' => 'organization_id',
            'type' => 'Hidden',
        ));        
        
        $this->add(array(
            'name' => 'category_id',
            'type' => 'Hidden',
        ));                
        
        foreach ($this->languages as $lang) {
            $this->add(array(
                'name' => 'name_' . $lang,
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
                'name' => 'description_' . $lang,
                'type' => 'Text',
                'attributes' => array(
                    'size' => 100,
                    'data-rule-maxlength' => 255,
                    'maxsize' => 255,
                    'data-rule-required' => true,
                    'data-rule-nohtml' => true,
                ),
            ));
        }

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
        ));
    }

}
