<?php

/**
 * Module class for module 'Certificat'
 */

namespace Certificat;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ViewHelperProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\View\Renderer\PhpRenderer;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface, ViewHelperProviderInterface
{

    /**
     * Do stuff on bootstrap: Initialize ACL, check if user is allowed and set module layout
     * 
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function onBootstrap(MvcEvent $e)
    {
        $this->initAcl($e);

        $em = $e->getApplication()->getEventManager();

        $em->getSharedManager()
                ->attach('Zend\Mvc\Application', MvcEvent::EVENT_ROUTE, array($this, 'onRoute'));
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    /**
     * Get own view helpers used in module views
     *
     * @return array
     */
    public function getViewHelperConfig()
    {
        return include __DIR__ . '/config/module.viewhelpers.php';
    }

    /**
     * Get services used in module
     * 
     * @return array
     */
    public function getServiceConfig()
    {
        return include __DIR__ . '/config/module.services.php';
    }

    /**
     * Initialize ACL. Executed on bootstrap.
     * 
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function initAcl(MvcEvent $e)
    {
        $acl = new \Zend\Permissions\Acl\Acl();
        $roles = include __DIR__ . '/config/module.acl.roles.php';

        foreach ($roles as $role => $resources) {
            $role = new \Zend\Permissions\Acl\Role\GenericRole($role);
            $acl->addRole($role);

            foreach ($resources as $resource) {
                $resource = 'ce/' . $resource;
                if (!$acl->hasResource($resource)) {
                    $acl->addResource(new \Zend\Permissions\Acl\Resource\GenericResource($resource));
                }
                $acl->allow($role, $resource);
            }
        }

        //setting to view
        $e->getViewModel()->acl = $acl;
    }

    /**
     * Fill history, set language und check if user is allowed. Executed on route.
     *
     * @param \Zend\Mvc\MvcEvent $e
     */
    public function onRoute(MvcEvent $e)
    {
        $route = $e->getRouteMatch()->getMatchedRouteName();
        $target = $e->getRouteMatch()->getParam('target', 'window');
        $sm = $e->getApplication()->getServiceManager();

        // fill history of visited pages
        if ($route !== 'file' && $route !== 'captcha' && $target !== 'iframe') {
            $settings = $sm->get('Settings');
            if (!isset($settings->history) || empty($settings->history)) {
                $settings->history = array();
            }
            $new_entry = array($route, $e->getRouteMatch()->getParams());
            $settings->history = array_merge(array($new_entry), $settings->history);
        }

        // set role and user name
        if ($sm->get('AuthService')->hasIdentity()) {
            $identity = $sm->get('AuthService')->getIdentity();

            if ($identity['is_admin'] == 1) {
                $userRole = 'application_admin';
            } elseif ($identity['organization_id']) {
                $userRole = 'organization_' . $identity['organization_role'];
            } else {
                $userRole = 'participant';
            }

            $identityData = array(
                'userName' => $identity['fullName'],
                'userFirstName' => $identity['first_name'],
                'userLastName' => $identity['last_name']
            );
        } else {
            $userRole = 'guest';
            $identityData = array(
                'userName' => $sm->get('translator')->translate('Gast')
            );
        }

        $e->getViewModel()->userRole = $userRole;
        $e->getViewModel()->setVariables($identityData);

        if ($e->getViewModel()->acl->hasResource($route) && !$e->getViewModel()->acl->isAllowed($userRole, $route)) {
            $response = $e->getResponse();
            $response->getHeaders()->addHeaderLine('Location', '/');
            $response->setStatusCode(302);
            return $response;
        }
    }

}
