<?php

/**
 * Competence Category Form
 */

namespace Certificat\Form;

/**
 * Form class
 */
class CompetenceCategoryForm extends BaseForm
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
    public function __construct($name = 'competence-category')
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
        }

        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
        ));
    }

}
