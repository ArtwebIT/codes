<?php

namespace CertificatTest\Selenium;

/**
 * Class ProfileControllerSeleniumTest
 * @package CertificatTest\Selenium
 */
class ProfileControllerSeleniumTest extends BaseSeleniumTestCase
{

    /**
     * Test for uploading user avatar
     */
    public function testAvatarUpload()
    {
        $this->login($this->participant['email'], $this->participant['password']);
        $this->assertElementPresent('id=logout-link');

        $this->open('/my-profile');
        $this->waitForPageToLoad('30000');
        $this->assertElementPresent('id=user-profile-image');
        
        // Get avatar url before action
        $avatarBefore = $this->getAttribute('//div[@id="user-avatar-img"]/img/@src');
        
        $avatarFile = __DIR__ . '/../../files/avatar.jpg';
        $this->type('id=user-profile-image', $avatarFile);
        $this->click('id=user-profile-image-btn');
        sleep(1);
        
        $this->assertElementPresent('id=crop-do');
        $this->click('id=crop-do');
        sleep(1);
        
        // Get avatar url after action
        $avatarAfter = $this->getAttribute('//div[@id="user-avatar-img"]/img/@src');

        // Compare the avatars
        $this->assertTrue($avatarBefore != $avatarAfter);
        
        
    }

}
