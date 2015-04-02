<?php

/**
 * Certificate Form
 */

namespace Certificat\Form;

use Certificat\Model\Certificate;

/**
 * Form class
 */
class CertificateForm extends BaseForm
{

    /**
     * Constructor method for form class
     *
     * @param null|string   $name   Name for form
     */
    public function __construct($name = 'certificate')
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
            'name' => 'user_id',
            'type' => 'hidden',
        ));

        $this->add(array(
            'name' => 'organization_id',
            'type' => 'hidden',
        ));

        // Options will be set later
        $this->add(array(
            'name' => 'template_id',
            'type' => 'Select',
            'attributes' => array(
                'data-rule-required' => true,
            ),
            'options' => array(
                'required' => true,
                'disable_inarray_validator' => true,
            ),
        ));

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
            'name' => 'language',
            'type' => 'Select',
            'options' => array(
                'required' => true,
                'value_options' => array(
                    'de' => $this->getTranslator()->translate('German'),
                    'fr' => $this->getTranslator()->translate('French'),
                    'en' => $this->getTranslator()->translate('English'),
                ),
            ),
            'attributes' => array(
                'data-rule-required' => true,
            ),
        ));

        $this->add(array(
            'name' => 'description',
            'type' => 'Textarea',
            'attributes' => array(
                'data-rule-nohtml' => true,
            ),
        ));

        $this->add(array(
            'name' => 'duration',
            'type' => 'Text',
            'attributes' => array(
                'data-rule-required' => true,
                'data-rule-digits' => true,
                'data-rule-nohtml' => true,
            ),
        ));

        $this->add(array(
            'name' => 'start_date',
            'type' => 'Text',
            'attributes' => array(
                'data-rule-required' => true,
                'data-rule-nohtml' => true,
                'data-date-format' => 'dd/mm/yyyy',
            ),
        ));

        $this->add(array(
            'name' => 'end_date',
            'type' => 'Text',
            'attributes' => array(
                'data-rule-required' => true,
                'data-rule-nohtml' => true,
                'data-date-format' => 'dd/mm/yyyy',
            ),
        ));

        $this->add(array(
            'name' => 'complete',
            'type' => 'Submit',
        ));
        
        $this->add(array(
            'name' => 'draft',
            'type' => 'Submit',
        ));        
    }

    /**
     * Set select options for `template` field
     *
     * @param array $options
     */
    public function setTemplateOptions($options)
    {
        $this->get('template_id')->setValueOptions($options);

        return $this;
    }

}
