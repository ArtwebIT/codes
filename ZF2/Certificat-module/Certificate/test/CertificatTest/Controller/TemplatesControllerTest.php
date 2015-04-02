<?php

namespace CertificatTest\Controller;

/**
 * Class TemplatesControllerTest
 * @package Certificat\Test\Controller
 */
class TemplatesControllerTest extends BaseControllerTest
{

    /**
     * Setup for each test in controller
     */
    public function setUp()
    {
        parent::setUp();

        $this->authenticateUser($this->organization['email'], $this->organization['password']);
    }

    /**
     * Templates index action - list of templates by organization
     */
    public function testIndexActionCanBeAccessedByOrganizationAdmin()
    {
        $this->dispatch('/templates');

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Templates');
        $this->assertControllerClass('TemplatesController');
        $this->assertMatchedRouteName('ce/templates');
        $this->assertQuery('table#table-templates'); // id of templates table
    }

    /**
     * Templates new action - Form for create a template
     * 
     * @depends testIndexActionCanBeAccessedByOrganizationAdmin
     */
    public function testNewActionCanBeAccessedByOrganizationAdmin()
    {
        $this->dispatch('/templates/new');

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Templates');
        $this->assertControllerClass('TemplatesController');
        $this->assertMatchedRouteName('ce/templates');
        $this->assertQuery('form#template'); // id of template form
    }

    /**
     * Templates index action - list of templates by organization
     * 
     * @depends testNewActionCanBeAccessedByOrganizationAdmin
     */
    public function testCreateTemplateByOrganizationAdmin()
    {
        $this->_disableSaveTemplate();

        $this->dispatch('/templates/save', 'POST', $this->template);

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Templates');
        $this->assertControllerClass('TemplatesController');
        $this->assertMatchedRouteName('ce/templates');
        $this->assertRedirectTo('/templates/');
    }

    /**
     * Disable \Certificat\Model\TemplateTable@saveTemplate
     * and \Certificat\Model\TemplateCompetenceTable@save methods
     */
    private function _disableSaveTemplate()
    {
        // Set mock for \Certificat\Model\TemplateTable
        $templateTableMock = $this->getMockBuilder('Certificat\Model\TemplateTable')
                ->disableOriginalConstructor()
                ->getMock();

        $templateTableMock->expects($this->once())
                ->method('saveTemplate')
                ->will($this->returnValue(1));

        // Set mock for \Certificat\Model\TemplateCompetenceTable
        $templateCompetenceTableMock = $this->getMockBuilder('Certificat\Model\TemplateCompetenceTable')
                ->disableOriginalConstructor()
                ->getMock();

        $templateCompetenceTableMock->expects($this->any())
                ->method('save')
                ->will($this->returnValue(true));

        // Set mock objects
        $this->getApplicationServiceLocator()
                ->setAllowOverride(true)
                ->setService('Certificat\Model\TemplateTable', $templateTableMock)
                ->setService('Certificat\Model\TemplateCompetenceTable', $templateCompetenceTableMock);
    }

}
