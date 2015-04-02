<?php

namespace CertificatTest\Selenium;

use PHPUnit_Extensions_SeleniumTestCase;

/**
 * Class BaseSeleniumTestCase
 * @package CertificatTest\Selenium
 */
class BaseSeleniumTestCase extends PHPUnit_Extensions_SeleniumTestCase
{

    const BASE_URL = 'http://certificat.localhost';

    /**
     * Test participant
     * 
     * @var array
     */
    protected $participant = array(
        'email' => 'participant_testing@certificat.localhost',
        'password' => 'password1',
        'password_check' => 'password1',
        'first_name' => 'participant',
        'last_name' => 'testing',
        'sex' => 'm',
        'termsaccept' => 1,
        'captcha' => array(
            'input' => '',
            'id' => '',
        )
    );

    /**
     * Test organization
     * 
     * @var array
     */
    protected $organization = array(
        // Organization 
        'name' => 'Organization name',
        'street' => 'street',
        'house' => '123',
        'zip_code' => '75001',
        'city' => 'Paris',
        'country' => 'FR',
        // Organization admin
        'email' => 'organization_testing@certificat.localhost',
        'password' => 'password1',
        'password_check' => 'password1',
        'first_name' => 'organization',
        'last_name' => 'testing',
        'sex' => 'm',
        'termsaccept' => 1,
        'captcha' => array(
            'input' => '',
            'id' => '',
        )
    );

    /**
     * Set up for each test
     * 
     */
    protected function setUp()
    {
        $this->setBrowser('*chrome');
        $this->setBrowserUrl(self::BASE_URL);
    }

    /**
     * Logout action
     * 
     * @param string $email
     * @param string $password
     */
    protected function login($email, $password)
    {
        $this->open('/');
        $this->type('id=login-form-email', $email);
        $this->type('id=login-form-password', $password);
        $this->click('id=login-form-submit');
        $this->waitForPageToLoad('30000');
    }

    /**
     * Logout action
     * 
     */
    protected function logout()
    {
        $this->click('id=logout-link');
        $this->waitForPageToLoad("30000");
    }

}
