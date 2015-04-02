<?php

/**
 * User Profile Form
 */

namespace Certificat\Form;

/**
 * Form class
 */
class ParticipantUserProfileForm extends OrganizationUserProfileForm
{

    /**
     * Constructor method for form class
     *
     * @param null|string   $name   Name for form
     */
    public function __construct($name = 'participant-user-profile')
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
        parent::init();

        $this->add(array(
            'name' => 'photo_id',
            'type' => 'Hidden',
        ));

        $this->add(array(
            'name' => 'address',
            'type' => 'Text',
            'attributes' => array(
                'data-rule-maxlength' => 60,
                'maxsize' => 60,
                'data-rule-required' => true,
                'data-rule-nohtml' => true,
            ),
        ));

        $this->add(array(
            'name' => 'zip_code',
            'type' => 'Text',
            'attributes' => array(
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
            'options'    => array(
                'empty_option'  => $this->getTranslator()->translate('bitte auswählen'),
                'value_options' => $this->getServiceLocator()->get('Country'),
            ),            
            'attributes' => array(
                'data-rule-required' => true,
            ),
        ));

        $this->add(array(
            'name' => 'date_of_birth',
            'type' => 'Text',
        ));
    }

}
