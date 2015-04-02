<?php

namespace CertificatTest\Controller;

/**
 * Class ProfileControllerTest
 * @package Certificat\Test\Controller
 */
class ProfileControllerTest extends BaseControllerTest
{

    /**
     * Profile show action for organization user
     */
    public function testShowActionCanBeAccessedByOrganizationUser()
    {
        $this->authenticateUser($this->organization['email'], $this->organization['password']);

        $this->dispatch('/my-profile');

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Profile');
        $this->assertControllerClass('ProfileController');
        $this->assertMatchedRouteName('ce/my-profile');
        $this->assertQuery('form#organization-user-profile'); // id of user form
    }

    /**
     * Profile show action for participant user
     */
    public function testShowActionCanBeAccessedByParticipantUser()
    {
        $this->authenticateUser($this->participant['email'], $this->participant['password']);

        $this->dispatch('/my-profile');

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Profile');
        $this->assertControllerClass('ProfileController');
        $this->assertMatchedRouteName('ce/my-profile');
        $this->assertQuery('form#participant-user-profile'); // id of user form
        $this->assertQuery('input#user-profile-image'); // id of user avatar upload input
    }

    /**
     * Profile save action for organization user
     * 
     * @depends testShowActionCanBeAccessedByOrganizationUser
     */
    public function testSaveActionByOrganizationUser()
    {
        $this->authenticateUser($this->organization['email'], $this->organization['password']);

        $data = array_merge($this->organization, array('first_name' => 'Edited first name'));

        $this->dispatch('/my-profile/save', 'POST', $data);

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Profile');
        $this->assertControllerClass('ProfileController');
        $this->assertMatchedRouteName('ce/my-profile');
        $this->assertRedirectTo('/my-profile/');
        $this->assertTrue(isset($_SESSION['FlashMessenger']['success']));
    }

    /**
     * Profile save action for participant user
     * 
     * @depends testShowActionCanBeAccessedByParticipantUser
     */
    public function testSaveActionByParticipantUser()
    {
        $this->authenticateUser($this->participant['email'], $this->participant['password']);

        $data = array_merge($this->organization, array(
            'address' => '25 street name',
            'zip_code' => '75001',
            'city' => 'Paris',
            'country' => 'FR',
            'date_of_birth' => date('d/m/Y')
        ));

        $this->dispatch('/my-profile/save', 'POST', $data);

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Profile');
        $this->assertControllerClass('ProfileController');
        $this->assertMatchedRouteName('ce/my-profile');
        $this->assertRedirectTo('/my-profile/');
        $this->assertTrue(isset($_SESSION['FlashMessenger']['success']));
    }

}
