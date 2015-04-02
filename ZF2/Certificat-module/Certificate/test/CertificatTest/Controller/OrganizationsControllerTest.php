<?php

namespace CertificatTest\Controller;

/**
 * Class OrganizationsControllerTest
 * @package Certificat\Test\Controller
 */
class OrganizationsControllerTest extends BaseControllerTest
{

    /**
     * Profile show action for organization user
     */
    public function testEditActionByOrganizationAdmin()
    {
        $this->authenticateUser($this->organization['email'], $this->organization['password']);

        $this->dispatch('/my-organization');

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Organizations');
        $this->assertControllerClass('OrganizationsController');
        $this->assertMatchedRouteName('ce/my-organization');
        $this->assertQuery('form#organization'); // id of organization form
    }

    /**
     * Profile save action for organization user
     * 
     * @depends testEditActionByOrganizationAdmin
     */
    public function testSaveActionByOrganizationUser()
    {
        $this->authenticateUser($this->organization['email'], $this->organization['password']);

        $data = array_merge($this->organization, array('name' => 'Edited organization name'));

        $this->dispatch('/my-organization', 'POST', $data);

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Organizations');
        $this->assertControllerClass('OrganizationsController');
        $this->assertMatchedRouteName('ce/my-organization');
        $this->assertRedirectTo('/my-organization');
        $this->assertTrue(isset($_SESSION['FlashMessenger']['success']));
    }

}