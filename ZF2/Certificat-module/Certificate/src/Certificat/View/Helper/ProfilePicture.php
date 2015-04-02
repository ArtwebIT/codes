<?php

/**
 * View Helper for showing user avatar or organization logo
 *
 * @var $imageId   int         id of image
 * @var $editable   boolean     image is editable?
 */

namespace Certificat\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * View Helper class
 */
class ProfilePicture extends AbstractHelper implements ServiceLocatorAwareInterface
{

    /**
     * Attention: This is only the Helper Plugin Manager. To access the "global"
     * service locator, call another getServiceLocator() on this.
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

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
     * @var \Zend\Authentication\AuthenticationService
     */
    protected $authservice;

    /**
     * Set service locator
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator to access service from module configuration
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Get user table via service manager
     *
     * @return \SNJ\Model\UserTable
     */
    public function getUserTable()
    {
        if (!$this->userTable) {
            $this->userTable = $this->getServiceLocator()->getServiceLocator()->get('UserTable');
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
            $this->organizationTable = $this->getServiceLocator()->getServiceLocator()->get('Certificat\Model\OrganizationTable');
        }
        return $this->organizationTable;
    }

    /**
     * Get authentication service via service manager
     *
     * @return \Zend\Authentication\AuthenticationService
     */
    public function getAuthService()
    {
        if (!$this->authservice) {
            $this->authservice = $this->getServiceLocator()->getServiceLocator()->get('AuthService');
        }

        return $this->authservice;
    }

    /**
     * __invoke
     * 
     * @return string
     */
    public function __invoke()
    {
        $identity = $this->getAuthService()->getIdentity();
        $user_id = (int) $identity['id'];
        $organization_id = (int) $identity['organization_id'];

        if ($organization_id) {
            $organization = $this->getOrganizationTable()->getOrganization($organization_id);
            $image = $this->view->organizationLogo($organization->logo_id, FALSE);
        } else {
            $user = $this->getUserTable()->getUser($user_id);
            $image = $this->view->userImage($user->photo_id, FALSE);
        }
        
        return $image;
    }

}
