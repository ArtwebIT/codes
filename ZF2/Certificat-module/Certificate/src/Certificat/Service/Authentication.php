<?php

/**
 * Authentication class for login to system
 * is Zend Authentication class with added organization check
 * for new and locked users
 *
 * @copyright   (c) 2013 Rouven Diener
 * @version     1.0
 */

namespace Certificat\Service;

use Zend\Authentication\AuthenticationService;
use SNJ\Util\Translator;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class Authentication extends AuthenticationService implements ServiceLocatorAwareInterface
{

    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     *
     * @var \Certificat\Model\UserOrganizationTable
     */
    protected $userOrganizationTable;

    /**
     * Set service locator.
     *
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * Get service locator to access service from module configuration.
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
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

    /**
     * Authenticates against the supplied adapter
     *
     * @param  Adapter\AdapterInterface $adapter
     * @return Result
     * @throws Exception\RuntimeException
     */
    public function authenticate(\Zend\Authentication\Adapter\AdapterInterface $adapter = null)
    {
        if (!$adapter) {
            if (!$adapter = $this->getAdapter()) {
                throw new Exception\RuntimeException('An adapter must be set or passed prior to calling authenticate()');
            }
        }
        $result = $adapter->authenticate();


        if ($result->isValid()) {
            $user = $this->getAdapter()->getResultRowObject();
            if (in_array($user->user_status, array('a', 'w'))) {

                $organization = $this->getUserOrganizationTable()->getUserOrganization($user->id);
                if ($organization && $organization->approved != 1) {
                    $result = new \Zend\Authentication\Result(-4, $user->email, array(
                        0 => Translator::translate('Your company has not yet been approved by the administrator.')
                    ));
                } else {
                    /**
                     * ZF-7546 - prevent multiple successive calls from storing inconsistent results
                     * Ensure storage has clean state
                     */
                    if ($this->hasIdentity()) {
                        $this->clearIdentity();
                    }

                    if ($result->isValid()) {
                        $this->getStorage()->write($result->getIdentity());
                    }
                }
            } elseif ($user->user_status === 'r') {
                $result = new \Zend\Authentication\Result(-4, $user->email, array(
                    0 => Translator::translate('Benutzer wurde noch nicht aktiviert.')
                ));
            } else {
                $result = new \Zend\Authentication\Result(-4, $user->email, array(
                    0 => Translator::translate('Benutzer wurde gesperrt.')
                ));
            }
        }

        return $result;
    }

}
