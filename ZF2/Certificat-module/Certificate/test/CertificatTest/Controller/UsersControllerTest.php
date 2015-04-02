<?php

namespace CertificatTest\Controller;

use SNJ\Model\User;
use Certificat\Model\UserOrganization;

/**
 * Class UsersControllerTest
 * @package Certificat\Test\Controller
 */
class UsersControllerTest extends BaseControllerTest
{

    /**
     * Id of editor created in DB
     * @var int
     */
    public $editorId = null;

    /**
     * Editor array for create in DB
     * @var array
     */
    public $editor = array(
        'first_name' => 'Test',
        'last_name' => 'Editor',
        'email' => 'organization_editor@certificat.localhost'
    );

    /**
     * Setup for each test in controller
     */
    public function setUp()
    {
        parent::setUp();

        $this->authenticateUser($this->organization['email'], $this->organization['password']);
    }

    /**
     * Users index action - list of organization users
     */
    public function testIndexActionCanBeAccessedByOrganizationAdmin()
    {
        $this->dispatch('/users');

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Users');
        $this->assertControllerClass('UsersController');
        $this->assertMatchedRouteName('ce/users');
        $this->assertQuery('table#organization-users-table'); // id of users table
    }

    /**
     * Users save editor action
     * 
     * @depends testIndexActionCanBeAccessedByOrganizationAdmin
     */
    public function testSaveEditorByOrganizationAdmin()
    {
        $this->_disableSaveOrganizationEditor();

        $this->dispatch('/users/save-editor', 'POST', $this->editor);

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Users');
        $this->assertControllerClass('UsersController');
        $this->assertMatchedRouteName('ce/users');
        $this->assertRedirectTo('/users/');
        $this->assertTrue(isset($_SESSION['FlashMessenger']['success']));
    }

    /**
     * Users delete editor action
     * 
     * @depends testIndexActionCanBeAccessedByOrganizationAdmin
     */
    public function testDeleteEditorByOrganizationAdmin()
    {
        $this->dispatch('/users/delete-editor/id/' . $this->_getEditorId());

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Users');
        $this->assertControllerClass('UsersController');
        $this->assertMatchedRouteName('ce/users');
        $this->assertRedirectTo('/users/');
        $this->assertTrue(isset($_SESSION['FlashMessenger']['success']));
    }

    /**
     * Disable \Certificat\Model\UserOrganizationTable@registerEditor
     * 
     */
    private function _disableSaveOrganizationEditor()
    {
        // Set mock for \Certificat\Model\UserOrganizationTable
        $userOrganizationTableMock = $this->getMockBuilder('Certificat\Model\UserOrganizationTable')
                ->disableOriginalConstructor()
                ->getMock();

        $userOrganizationTableMock->expects($this->once())
                ->method('registerEditor')
                ->will($this->returnValue(1));

        // Set mock objects
        $this->getApplicationServiceLocator()
                ->setAllowOverride(true)
                ->setService('Certificat\Model\UserOrganizationTable', $userOrganizationTableMock);
    }

    /**
     * Create the organization editor for test
     * 
     * @return int
     */
    private function _getEditorId()
    {
        if (!$this->editorId) {
            if ($user = $this->getUserTable()->findUserByEmail($this->editor['email'])) {
                $this->editorId = $user->id;
            } else {
                $data = $this->editor;
                // Generate password
                $data['password'] = $this->getUserTable()->generatePassword();

                $user = new User();
                $user->exchangeArray($data);

                $this->editorId = $this->getUserTable()->registerUser($user, false);

                $identity = $this->getAuthService()->getIdentity();

                // Add user to organization
                $userOrganization = new UserOrganization();
                $userOrganization->exchangeArray(array(
                    'user_id' => $this->editorId,
                    'organization_id' => (int) $identity['organization_id'],
                    'role' => UserOrganization::ROLE_EDITOR,
                ));
                $this->getUserOrganizationTable()->save($userOrganization);
            }
        }

        return $this->editorId;
    }

}
