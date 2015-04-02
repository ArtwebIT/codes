<?php

namespace CertificatTest\Selenium;

/**
 * Class AuthControllerSeleniumTest
 * @package CertificatTest\Selenium
 */
class AuthControllerSeleniumTest extends BaseSeleniumTestCase
{

    /**
     * Logout action for participant
     * 
     */
    public function testLogout()
    {
        $this->login($this->participant['email'], $this->participant['password']);
        $this->assertTrue($this->isElementPresent('id=logout-link'));

        $this->logout();
        $this->assertFalse($this->isElementPresent('id=logout-link'));
    }

}
