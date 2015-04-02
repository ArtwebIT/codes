<?php

namespace CertificatTest\Controller;

/**
 * Class AuthControllerTest
 * @package Certificat\Test\Controller
 */
class AuthControllerTest extends BaseControllerTest
{

    /**
     * Register participant
     */
    public function testRegisterParticipant()
    {
        // Clear the user data for testing
        if ($user = $this->getUserTable()->findUserByEmail($this->participant['email'])) {
            if ($user->photo_id) {
                $this->getFileTable()->deleteFile($user->photo_id, $user->id);
            }
            $this->getUserTable()->deleteUser($user->id);
        }

        $this->dispatch('/register', 'POST', array_merge($this->participant, $this->getCaptcha()));

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Auth');
        $this->assertControllerClass('AuthController');
        $this->assertMatchedRouteName('ce/register');
        $this->assertRedirectTo('/');
        $this->assertTrue(isset($_SESSION['FlashMessenger']['success']));
    }

    /**
     * Register organization
     */
    public function testRegisterOrganization()
    {
        // Clear the user & organization data for testing
        if ($user = $this->getUserTable()->findUserByEmail($this->organization['email'])) {
            if ($organization = $this->getUserOrganizationTable()->getUserOrganization($user->id)) {
                if ($organization->logo_id) {
                    $this->getFileTable()->deleteFile($organization->logo_id);
                }
                $this->getOrganizationTable()->deleteOrganization($organization->id);
            }
            $this->getUserTable()->deleteUser($user->id);
        }
        
        $this->dispatch('/register-organization', 'POST', array_merge($this->organization, $this->getCaptcha()));

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Auth');
        $this->assertControllerClass('AuthController');
        $this->assertMatchedRouteName('ce/register-organization');
        $this->assertRedirectTo('/?tab=organization');
        $this->assertTrue(isset($_SESSION['FlashMessenger']['success']));
    }

    /**
     * Re-register participant
     * 
     * @depends testRegisterParticipant
     */
    public function testReRegisterParticipant()
    {
        $this->dispatch('/register', 'POST', array_merge($this->participant, $this->getCaptcha()));

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Auth');
        $this->assertControllerClass('AuthController');
        $this->assertMatchedRouteName('ce/register');
        $this->assertRedirectTo('/');
        $this->assertTrue(isset($_SESSION['FlashMessenger']['error']));
    }

    /**
     * Authenticate not activated participant
     * 
     * @depends testRegisterParticipant
     */
    public function testAuthenticateNotActivatedParticiapant()
    {
        $this->dispatch('/login/authenticate', 'POST', array(
            'email' => $this->participant['email'],
            'password' => $this->participant['password'] // Correct password
        ));

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Auth');
        $this->assertControllerClass('AuthController');
        $this->assertMatchedRouteName('ce/login');
        $this->assertRedirectTo('/');
    }

    /**
     * Activate participant
     * 
     * @depends testRegisterParticipant
     */
    public function testActivateParticipant()
    {
        $user = $this->getUserTable()->findUserByEmail($this->participant['email']);

        $this->dispatch("/activate/user/{$user->id}/code/{$user->activation_code}");

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Auth');
        $this->assertControllerClass('AuthController');
        $this->assertMatchedRouteName('ce/activate');
        $this->assertRedirectTo('/');

        $activatedUser = $this->getUserTable()->findUserByEmail($this->participant['email']);
        $this->assertTrue($activatedUser->user_status == 'a');
    }

    /**
     * Authenticate participant with correct credentials
     * 
     * @depends testActivateParticipant
     */
    public function testAuthenticateWithCorrectCredentials()
    {
        // Unlock user
        if ($user = $this->getUserTable()->findUserByEmail($this->participant['email'])) {
            $this->getUserTable()->unlock($user->id);
        }

        $this->dispatch('/login/authenticate', 'POST', array(
            'email' => $this->participant['email'],
            'password' => $this->participant['password'] // Correct password
        ));

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Auth');
        $this->assertControllerClass('AuthController');
        $this->assertMatchedRouteName('ce/login');
        $this->assertRedirectTo('/my-profile/');
        $this->assertTrue($this->getAuthService()->hasIdentity());
    }

    /**
     * Authenticate participant with incorrect credentials
     * 
     * @depends testActivateParticipant
     */
    public function testAuthenticateWithIncorrectCredentials()
    {
        $this->dispatch('/login/authenticate', 'POST', array(
            'email' => $this->participant['email'],
            'password' => 'incorrectPassword2' // Incorrect password
        ));

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Auth');
        $this->assertControllerClass('AuthController');
        $this->assertMatchedRouteName('ce/login');
        $this->assertRedirectTo('/');
    }

    /**
     * Lost password action for participant
     * 
     * @depends testAuthenticateWithCorrectCredentials
     */
    public function testLostPassword()
    {
        $this->dispatch('/lost-password', 'POST', array(
            'email' => $this->participant['email'],
        ));
        
        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Auth');
        $this->assertControllerClass('AuthController');
        $this->assertMatchedRouteName('ce/lost-password');
        $this->assertRedirectTo('/lost-password');
        $this->assertTrue(isset($_SESSION['FlashMessenger']['success']));
        
        // Return the original password after test
        if ($user = $this->getUserTable()->findUserByEmail($this->participant['email'])) {
            $this->getUserTable()->setPassword($user, $this->participant['password']);
        }        
        
    }

}
