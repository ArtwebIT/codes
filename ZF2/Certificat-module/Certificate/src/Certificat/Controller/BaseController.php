<?php

/**
 * Base Controller for Certificat Module
 */

namespace Certificat\Controller;

use Zend\Mvc\Controller\AbstractActionController;

/**
 * Controller class
 */
abstract class BaseController extends AbstractActionController
{

    /**
     *
     * @var \Zend\Form\FormElementManager
     */
    protected $formElementManager;

    /**
     *
     * @var \Zend\Mvc\I18n\Translator
     */
    protected $translator;

    /**
     *
     * @var \Zend\Authentication\AuthenticationService
     */
    protected $authservice;
    
    /**
     *
     * @var \SNJ\Service\AuthStorage
     */
    protected $storage;    

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
     * @var \Certificat\Model\TemplateTable
     */
    public $templateTable;

    /**
     *
     * @var \Certificat\Model\TemplateCompetenceTable
     */
    public $templateCompetenceTable;
    
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
     * @var \SNJ\Service\PdfCreator
     */
    protected $pdfCreator;
    
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
            $this->fileTable = $this->getServiceLocator()->get('FileTable');
        }
        return $this->fileTable;
    }    

    /**
     * Get pdf creator service via service manager
     *
     * @return \SNJ\Service\PdfCreator
     */
    protected function getPdfCreator()
    {
        if (!$this->pdfCreator) {
            $this->pdfCreator = $this->getServiceLocator()->get('PdfCreator');
        }
        return $this->pdfCreator;
    }

    /**
     * Get certificate participant table via service manager
     *
     * @return \Certificat\Model\CertificateTable
     */
    public function getCertificateParticipantTable()
    {
        if (!$this->certificateParticipantTable) {
            $this->certificateParticipantTable = $this->getServiceLocator()->get('Certificat\Model\CertificateParticipantTable');
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
            $this->certificateTable = $this->getServiceLocator()->get('Certificat\Model\CertificateTable');
        }
        return $this->certificateTable;
    }    

    /**
     * Get template_competence table via service manager
     *
     * @return \Certificat\Model\TemplateCompetenceTable
     */
    public function getTemplateCompetenceTable()
    {
        if (!$this->templateCompetenceTable) {
            $this->templateCompetenceTable = $this->getServiceLocator()->get('Certificat\Model\TemplateCompetenceTable');
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
            $this->templateTable = $this->getServiceLocator()->get('Certificat\Model\TemplateTable');
        }
        return $this->templateTable;
    }    

    /**
     * Get competence table via service manager
     *
     * @return \Certificat\Model\CompetencesTable
     */
    public function getCompetenceCategoryTable()
    {
        if (!$this->competenceCategoryTable) {
            $this->competenceCategoryTable = $this->getServiceLocator()->get('Certificat\Model\CompetenceCategoryTable');
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
            $this->competenceTable = $this->getServiceLocator()->get('Certificat\Model\CompetenceTable');
        }
        return $this->competenceTable;
    }    
    
    /**
     * Get form element manager from service manager
     *
     * @return \Zend\Form\FormElementManager
     */
    public function getFormElementManager()
    {
        if (!$this->formElementManager) {
            $this->formElementManager = $this->getServiceLocator()->get('FormElementManager');
        }

        return $this->formElementManager;
    }

    /**
     * Get translator from service manager
     *
     * @return    \Zend\Mvc\I18n\Translator
     */
    public function getTranslator()
    {
        if (!$this->translator) {
            $this->translator = $this->getServiceLocator()->get('translator');
        }
        return $this->translator;
    }

    /**
     * Get authentication service via service manager
     *
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuthService()
    {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()->get('AuthService');
        }

        return $this->authservice;
    }
    
    /**
     * Get session storage via service manager
     *
     * @return \SNJ\Service\AuthStorage
     */
    public function getSessionStorage()
    {
        if (!$this->storage) {
            $this->storage = $this->getServiceLocator()->get('AuthStorage');
        }

        return $this->storage;
    }    

    /**
     * Get user table via service manager
     *
     * @return \SNJ\Model\UserTable
     */
    public function getUserTable()
    {
        if (!$this->userTable) {
            $this->userTable = $this->getServiceLocator()->get('UserTable');
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
            $this->organizationTable = $this->getServiceLocator()->get('Certificat\Model\OrganizationTable');
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
            $this->userOrganizationTable = $this->getServiceLocator()->get('Certificat\Model\UserOrganizationTable');
        }
        return $this->userOrganizationTable;
    }        

}
