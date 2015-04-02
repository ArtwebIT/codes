<?php

namespace CertificatTest\Controller;

/**
 * Class CertificatesControllerTest
 * @package Certificat\Test\Controller
 */
class CertificatesControllerTest extends BaseControllerTest
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
     * Certificates index action - list of certificates of organization
     */
    public function testIndexActionCanBeAccessedByOrganizationAdmin()
    {
        $this->dispatch('/certificates');

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Certificates');
        $this->assertControllerClass('CertificatesController');
        $this->assertMatchedRouteName('ce/certificates');
        $this->assertQuery('div#certificate-list-active'); // id of active certificates list
        $this->assertQuery('div#certificate-list-archive'); // id of archive certificates list
    }

    /**
     * Certificates new action - Form for create a certificate
     * 
     * @depends testIndexActionCanBeAccessedByOrganizationAdmin
     */
    public function testNewActionCanBeAccessedByOrganizationAdmin()
    {
        $this->dispatch('/certificates/new');

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Certificates');
        $this->assertControllerClass('CertificatesController');
        $this->assertMatchedRouteName('ce/certificates');
        $this->assertQuery('form#certificate'); // id of certificate form
    }

    /**
     * Certificates save action for new certificate
     * 
     * @depends testNewActionCanBeAccessedByOrganizationAdmin
     */
    public function testCreateCertificateByOrganizationAdmin()
    {
        $this->_disableSaveCertificate();

        $this->dispatch('/certificates/save', 'POST', $this->certificate);

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Certificates');
        $this->assertControllerClass('CertificatesController');
        $this->assertMatchedRouteName('ce/certificates');
        $this->assertRedirectTo('/certificates/');
        $this->assertTrue(isset($_SESSION['FlashMessenger']['success']));
    }

    /**
     * Certificates new action - Form for create a certificate
     * 
     * @depends testIndexActionCanBeAccessedByOrganizationAdmin
     */
    public function testEditActionCanBeAccessedByOrganizationAdmin()
    {
        $certificateId = $this->getCertificateId();
        $this->dispatch("/certificates/overview/id/$certificateId");

        $this->assertResponseStatusCode(200);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Certificates');
        $this->assertControllerClass('CertificatesController');
        $this->assertMatchedRouteName('ce/certificates');
        $this->assertQuery('form#certificate'); // id of certificate form
        $this->assertQueryContentContains('h2', $this->certificate['name']);
    }

    /**
     * Certificates save action for existing certificate
     * 
     * @depends testEditActionCanBeAccessedByOrganizationAdmin
     */
    public function testUpdateCertificateByOrganizationAdmin()
    {
        $certificateId = $this->getCertificateId();
        $certificate = $this->getCertificateTable()->getCertificate($certificateId);

        $data = array_merge($this->certificate, array(
            'name' => 'Edited certificate',
            'template_id' => $certificate->template_id
        ));

        $this->dispatch("/certificates/save/id/$certificateId", 'POST', $data);

        $this->assertResponseStatusCode(302);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Certificates');
        $this->assertControllerClass('CertificatesController');
        $this->assertMatchedRouteName('ce/certificates');
        $this->assertRedirectTo('/certificates/');
        $this->assertTrue(isset($_SESSION['FlashMessenger']['success']));
    }

    /**
     * Certificates save action for existing certificate with `comleted` status
     * 
     * @depends testUpdateCertificateByOrganizationAdmin
     */
    public function testUpdateCompletedCertificateByOrganizationAdmin()
    {
        $certificateId = $this->getCertificateId();
        $certificate = $this->getCertificateTable()->getCertificate($certificateId);
        $this->getCertificateTable()->completeCertificate($certificate);

        $data = array_merge($this->certificate, array(
            'name' => 'Edited completed certificate',
            'template_id' => $certificate->template_id
        ));

        $this->dispatch("/certificates/save/id/$certificateId", 'POST', $data);

        $this->assertResponseStatusCode(500);
        $this->assertModuleName('Certificat');
        $this->assertControllerName('Certificat\Controller\Certificates');
        $this->assertControllerClass('CertificatesController');
        $this->assertMatchedRouteName('ce/certificates');
    }

    /**
     * Disable \Certificat\Model\CertificateTable@saveCertificate
     * and \Certificat\Model\CertificateParticipantTable@syncParticipantsByCertificateId methods
     */
    private function _disableSaveCertificate()
    {
        // Set mock for \Certificat\Model\CertificateTable
        $certificateTableMock = $this->getMockBuilder('Certificat\Model\CertificateTable')
                ->disableOriginalConstructor()
                ->getMock();

        $certificateTableMock->expects($this->once())
                ->method('saveCertificate')
                ->will($this->returnValue(1));

        // Set mock for \Certificat\Model\CertificateParticipantTable
        $certificateParticipantTableMock = $this->getMockBuilder('Certificat\Model\CertificateParticipantTable')
                ->disableOriginalConstructor()
                ->getMock();

        $certificateParticipantTableMock->expects($this->any())
                ->method('syncParticipantsByCertificateId')
                ->will($this->returnValue(true));

        // Set mock objects
        $this->getApplicationServiceLocator()
                ->setAllowOverride(true)
                ->setService('Certificat\Model\CertificateTable', $certificateTableMock)
                ->setService('Certificat\Model\CertificateParticipantTable', $certificateParticipantTableMock);
    }

}
