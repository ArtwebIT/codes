<?php

namespace CertificatTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Certificat\Model\Competence;
use Certificat\Model\CompetenceCategory;
use Certificat\Model\TemplateCompetence;
use Certificat\Model\Template;
use Certificat\Model\Certificate;

/**
 * Class BaseControllerTest
 */
abstract class BaseControllerTest extends AbstractHttpControllerTestCase
{

    /**
     * Id of competence category created in DB
     * @var int
     */
    protected $competenceCategoryId = null;

    /**
     * Id of competence created in DB
     * @var int
     */
    protected $competenceId = null;

    /**
     * Id of template created in DB
     * @var int
     */
    protected $templateId = null;

    /**
     * Id of certificate created in DB
     * @var int
     */
    protected $certificateId = null;

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
     * Test competence category
     * 
     * @var array
     */
    protected $competenceCategory = array(
        'name' => 'Test template',
        'type' => 'task',
        'competences' => array('1', '2'),
        'persons_in_charge' => array(
            array('name' => 'John Doe', 'function' => 'Teacher'),
            array('name' => 'Jane Doe', 'function' => 'Organizator')
        ),
        'additional_comment' => 'Additional comment'
    );

    /**
     * Test template
     * 
     * @var array
     */
    protected $template = array(
        'name' => 'Test template',
        'type' => 'task',
        'competences' => array('1', '2'),
        'persons_in_charge' => array(
            array('name' => 'John Doe', 'function' => 'Teacher'),
            array('name' => 'Jane Doe', 'function' => 'Organizator')
        ),
        'additional_comment' => 'Additional comment'
    );

    /**
     * Certificate array for create in DB
     * @var array
     */
    protected $certificate = array(
        'name' => 'test-certificate-phpunit',
        'template_id' => 1, // Need set later for real certificate
        'language' => 'fr',
        'description' => 'Test certificate description',
        'duration' => 4,
        'start_date' => '21/02/2016',
        'end_date' => '28/02/2016',
        'participant' => array(
            array(
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'participant_testing@certificat.localhost',
                'birthday' => '30/05/1989',
                'comment' => 'Comment abount participant'
            )
        ),
    );

    /**
     *
     * @var \Zend\Authentication\AuthenticationService
     */
    protected $authservice;

    /**
     *
     * @var \SNJ\Model\UserTable
     */
    protected $userTable;

    /**
     *
     * @var \Certificat\Model\OrganizationTable
     */
    protected $organizationTable;

    /**
     *
     * @var \Certificat\Model\UserOrganizationTable
     */
    protected $userOrganizationTable;

    /**
     *
     * @var \Certificat\Model\CompetenceTable
     */
    public $competenceTable;

    /**
     *
     * @var \Certificat\Model\CompetenceCategoryTable
     */
    public $competenceCategoryTable;

    /**
     *
     * @var \Certificat\Model\CertificateTable
     */
    public $certificateTable;

    /**
     *
     * @var \Certificat\Model\CertificateParticipantTable
     */
    public $certificateParticipantTable;

    /**
     *
     * @var \Certificat\Model\TemplateTable
     */
    protected $templateTable;
    
    /**
     *
     * @var \Certificat\Model\TemplateCompetenceTable
     */
    public $templateCompetenceTable;    
    
    /**
     * @var \SNJ\Model\FileTable
     */
    protected $fileTable;    
    
    /**
     * Get FileTable via service manager
     *
     * @return \SNJ\Model\FileTable
     */
    public function getFileTable()
    {
        if (!$this->fileTable) {
            $this->fileTable = $this->getApplicationServiceLocator()->get('FileTable');
        }
        return $this->fileTable;
    }    
    
    /**
     * Get template_competence table via service manager
     *
     * @return \Certificat\Model\TemplateCompetenceTable
     */
    public function getTemplateCompetenceTable()
    {
        if (!$this->templateCompetenceTable) {
            $this->templateCompetenceTable = $this->getApplicationServiceLocator()->get('Certificat\Model\TemplateCompetenceTable');
        }
        return $this->templateCompetenceTable;
    }    

    /**
     * Get competence table via service manager
     *
     * @return \Certificat\Model\TemplateTable
     */
    public function getTemplateTable()
    {
        if (!$this->templateTable) {
            $this->templateTable = $this->getApplicationServiceLocator()->get('Certificat\Model\TemplateTable');
        }
        return $this->templateTable;
    }

    /**
     * Get certificate participant table via service manager
     *
     * @return \Certificat\Model\CertificateTable
     */
    public function getCertificateParticipantTable()
    {
        if (!$this->certificateParticipantTable) {
            $this->certificateParticipantTable = $this->getApplicationServiceLocator()->get('Certificat\Model\CertificateParticipantTable');
        }
        return $this->certificateParticipantTable;
    }

    /**
     * Get certificate table via service manager
     *
     * @return \Certificat\Model\CertificateTable
     */
    public function getCertificateTable()
    {
        if (!$this->certificateTable) {
            $this->certificateTable = $this->getApplicationServiceLocator()->get('Certificat\Model\CertificateTable');
        }
        return $this->certificateTable;
    }

    /**
     * Get competence table via service manager
     *
     * @return \Certificat\Model\CompetencesTable
     */
    public function getCompetenceCategoryTable()
    {
        if (!$this->competenceCategoryTable) {
            $this->competenceCategoryTable = $this->getApplicationServiceLocator()->get('Certificat\Model\CompetenceCategoryTable');
        }
        return $this->competenceCategoryTable;
    }

    /**
     * Get competence table via service manager
     *
     * @return \Certificat\Model\CompetencesTable
     */
    public function getCompetenceTable()
    {
        if (!$this->competenceTable) {
            $this->competenceTable = $this->getApplicationServiceLocator()->get('Certificat\Model\CompetenceTable');
        }
        return $this->competenceTable;
    }

    /**
     * Get authentication service via service manager
     *
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuthService()
    {
        if (!$this->authservice) {
            $this->authservice = $this->getApplicationServiceLocator()->get('AuthService');
        }

        return $this->authservice;
    }

    /**
     * Get user table via service manager
     *
     * @return \SNJ\Model\UserTable
     */
    public function getUserTable()
    {
        if (!$this->userTable) {
            $this->userTable = $this->getApplicationServiceLocator()->get('UserTable');
        }
        return $this->userTable;
    }

    /**
     * Get organization table via service manager
     *
     * @return \Certificat\Model\OrganizationTable
     */
    public function getOrganizationTable()
    {
        if (!$this->organizationTable) {
            $this->organizationTable = $this->getApplicationServiceLocator()->get('Certificat\Model\OrganizationTable');
        }
        return $this->organizationTable;
    }

    /**
     * Get user organization table via service manager
     *
     * @return \Certificat\Model\UserOrganizationTable
     */
    public function getUserOrganizationTable()
    {
        if (!$this->userOrganizationTable) {
            $this->userOrganizationTable = $this->getApplicationServiceLocator()->get('Certificat\Model\UserOrganizationTable');
        }
        return $this->userOrganizationTable;
    }

    /**
     * Setup the controller
     *
     */
    public function setUp()
    {
        $this->setApplicationConfig(
                include __DIR__ . '/../../../../../config/certificat.config.php'
        );

        parent::setUp();
    }

    protected function getCaptcha()
    {
        $this->dispatch('/');
        foreach ($_SESSION as $key => $value) {
            if (strstr($key, 'Zend_Form_Captcha_')) {
                return array('captcha' =>
                    array(
                        'id' => str_replace('Zend_Form_Captcha_', '', $key),
                        'input' => $value['word'],
                    )
                );
            }
        }
    }

    /**
     * Authenticate user programmatically
     *
     * @param string $email
     * @param string $password
     */
    protected function authenticateUser($email, $password)
    {
        // Unlock user & approve his organization if exist
        if ($user = $this->getUserTable()->findUserByEmail($email)) {
            if ($organization = $this->getUserOrganizationTable()->getUserOrganization($user->id)) {
                $this->getOrganizationTable()->approveOrganization($organization->id);
            }
            $this->getUserTable()->unlock($user->id);
        }

        // Prepare authenticate
        $this->getAuthService()->getAdapter()
                ->setIdentity($email)
                ->setCredential($password);

        // Authenticate...
        $result = $this->getAuthService()->authenticate();

        if ($result->isValid()) {
            // Read user data from database.
            $columns = array('id', 'email', 'first_name', 'last_name', 'is_admin', 'language');
            $resultObj = $this->getAuthService()
                    ->getAdapter()
                    ->getResultRowObject($columns);

            // If user have organization
            $organization = $this->getUserOrganizationTable()->getUserOrganization($resultObj->id);

            // Write user data to session.
            $this->getAuthService()->getStorage()->write(array(
                'id' => $resultObj->id,
                'email' => $resultObj->email,
                'first_name' => $resultObj->first_name,
                'last_name' => $resultObj->last_name,
                'fullName' => $resultObj->first_name . ' ' . $resultObj->last_name, // Heritage from EPortfolio module (shared session)
                'is_admin' => $resultObj->is_admin,
                'language' => $resultObj->language,
                'organization_id' => ($organization) ? $organization->id : null,
                'organization_role' => ($organization) ? $this->getUserOrganizationTable()->getUserRoleInOrganization($resultObj->id, $organization->id) : null,
            ));
        }
    }

    /**
     * Get or create template for tests
     *
     * @return int
     */
    protected function getTemplateId()
    {
        if (!$this->templateId) {
            $data = $this->template;

            // Reset array keys and encode to json
            $data['persons_in_charge'] = array_values($data['persons_in_charge']);
            $data['persons_in_charge'] = json_encode($data['persons_in_charge']);

            // Get user_id and organization_id for certificate
            if ($user = $this->getUserTable()->findUserByEmail($this->organization['email'])) {
                $data['user_id'] = $user->id;
                if ($organization = $this->getUserOrganizationTable()->getUserOrganization($user->id)) {
                    $data['organization_id'] = $organization->id;
                }
            }

            $template = new Template();
            $template->exchangeArray($data);
            $this->templateId = $this->getTemplateTable()->saveTemplate($template);

            $templateCompetence = new TemplateCompetence();
            $templateCompetence->exchangeArray(array(
                'template_id' => $this->templateId,
                'competence_id' => $this->getCompetenceId()
            ));
            $this->getTemplateCompetenceTable()->save($templateCompetence);

        }

        return $this->templateId;
    }

    /**
     * Create the certificate for test
     * 
     * @return int
     */
    protected function getCertificateId()
    {
        if (!$this->certificateId) {
            $data = $this->certificate;
            $data['template_id'] = $this->getTemplateId();
            $data['status'] = Certificate::STATUS_DRAFT;
            // Get user_id and organization_id for certificate
            if ($user = $this->getUserTable()->findUserByEmail($this->organization['email'])) {
                $data['user_id'] = $user->id;
                if ($organization = $this->getUserOrganizationTable()->getUserOrganization($user->id)) {
                    $data['organization_id'] = $organization->id;
                }
            }

            $certificate = new Certificate();
            $certificate->exchangeArray($data);
            $this->certificateId = $this->getCertificateTable()->saveCertificate($certificate);
        }

        return $this->certificateId;
    }

    /**
     * Create the competence for test
     * 
     * @return int
     */
    protected function getCompetenceId()
    {
        if (!$this->competenceId) {
            $data = array();
            $data['category_id'] = $this->getCompetenceCategoryId();

            // Get organization_id for certificate
            if ($user = $this->getUserTable()->findUserByEmail($this->organization['email'])) {
                if ($organization = $this->getUserOrganizationTable()->getUserOrganization($user->id)) {
                    $data['organization_id'] = $organization->id;
                }
            }

            foreach ($this->getCompetenceCategoryTable()->getLanguages() as $lang) {
                $data["name_{$lang}"] = "Test name {$lang}";
                $data["description_{$lang}"] = "Test description {$lang}";
            }

            $competence = new Competence();
            $competence->exchangeArray($data);
            $this->competenceId = $this->getCompetenceTable()->saveCompetence($competence);
        }

        return $this->competenceId;
    }

    protected function getCompetenceCategoryId()
    {
        if (!$this->competenceId) {
            $categoryData = array();
            foreach ($this->getCompetenceCategoryTable()->getLanguages() as $lang) {
                $categoryData["name_{$lang}"] = "Test name {$lang}";
            }

            $competenceCategory = new CompetenceCategory();
            $competenceCategory->exchangeArray($categoryData);
            $this->competenceCategoryId = $this->getCompetenceCategoryTable()->saveCompetenceCategory($competenceCategory);
        }

        return $this->competenceCategoryId;
    }

}
