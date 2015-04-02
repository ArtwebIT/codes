<?php

/**
 * Register Form
 */

namespace Certificat\Form;

/**
 * Form class
 */
class RegisterForm extends BaseForm
{

    /**
     * Constructor method for form class
     *
     * @param null|string   $name   Name for form
     */
    public function __construct($name = 'register')
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
        $this->setAttribute('class', 'jq-validate form-horizontal');
        $this->add(array(
            'name' => 'first_name',
            'type' => 'Text',
            'attributes' => array(
                'size' => 30,
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
                'size' => 30,
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
            'name' => 'email',
            'type' => 'Text',
            'attributes' => array(
                'size' => 30,
                'data-rule-maxlength' => 50,
                'maxsize' => 50,
                'data-rule-required' => true,
                'data-rule-email' => true,
                'data-rule-remote' => '/check-email',
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
                'data-rule-required' => true,
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
                'data-rule-required' => true,
                'data-rule-password' => true,
            ),
        ));
        $this->add(array(
            'name' => 'captcha',
            'type' => 'Captcha',
            'options' => array(
                'captcha' => array(
                    'class' => 'Image',
                    'font' => 'data/font/Arial.ttf',
                    'width' => 148,
                    'height' => 46,
                    'wordlen' => 5, // default: 8 characters
                    'timeout' => 300, // default: 300 seconds
                    'expiration' => 600, // default: 600 seconds
                    'dotNoiseLevel' => 2, // default: 100 dots
                    'lineNoiseLevel' => 2, // default: 5 lines
                    'gcFreq' => 10, // default: 10
                    'imgDir' => './data/captcha/',
                    'imgUrl' => '/captcha/',
                ),
                'required' => true,
            ),
            'attributes' => array(
                'data-rule-minlength' => 4,
                'data-rule-maxlength' => 20,
                'maxsize' => 20,
                'data-rule-required' => true,
            ),
        ));
        $this->add(array(
            'name' => 'termsaccept',
            'type' => 'Checkbox',
            'attributes' => array(
                'data-rule-required' => true,
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'type' => 'Submit',
        ));
    }

}
