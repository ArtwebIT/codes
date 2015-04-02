<?php

/**
 * Lost Password Form
 */

namespace Certificat\Form;

/**
 * Form class
 */
class LostpasswordForm extends BaseForm
{

    /**
     * Constructor method for form class
     *
     * @param null|string   $name   Name for form
     */
    public function __construct($name = 'lost_password')
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
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => array(
                'value' => $this->getTranslator()->translate('Send new password'),
                'id' => 'submitbutton',
                'class' => 'button_send',
            ),
        ));
    }

}
