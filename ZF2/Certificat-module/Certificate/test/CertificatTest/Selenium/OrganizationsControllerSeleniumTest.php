<?php

namespace CertificatTest\Selenium;

/**
 * Class OrganizationControllerSeleniumTest
 * @package CertificatTest\Selenium
 */
class OrganizationsControllerSeleniumTest extends BaseSeleniumTestCase
{

    /**
     * Test for uploading organization logo
     */
    public function testLogoUploadByOrganizationAdmin()
    {
        $this->login($this->organization['email'], $this->organization['password']);
        $this->assertElementPresent('id=logout-link');

        $this->open('/my-organization');
        $this->waitForPageToLoad('30000');
        $this->assertElementPresent('id=organization-profile-logo');
        
        // Get logo url before action
        $logoBefore = $this->getAttribute('//div[@id="organization-logo-img"]/img/@src');
        
        $logoFile = __DIR__ . '/../../files/logo.jpg';
        $this->type('id=organization-profile-logo', $logoFile);
        $this->click('id=organization-profile-logo-btn');
        sleep(1);
        
        $this->assertElementPresent('id=crop-do');
        $this->click('id=crop-do');
        sleep(1);
        
        // Get logo url after action
        $logoAfter = $this->getAttribute('//div[@id="organization-logo-img"]/img/@src');

        // Compare the avatars
        $this->assertTrue($logoBefore != $logoAfter);
        
        
    }

}
