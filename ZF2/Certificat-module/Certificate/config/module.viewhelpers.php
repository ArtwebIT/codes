<?php

namespace Certificat;

use Zend\ServiceManager\ServiceLocatorInterface;

return array(
    'invokables' => array(
        'userRoleTitle' => 'Certificat\View\Helper\UserRoleTitle',
        'organizationLogo' => 'Certificat\View\Helper\OrganizationLogo',
        'userImage' => 'Certificat\View\Helper\UserImage',
        'profilePicture' => 'Certificat\View\Helper\ProfilePicture',
    ),
    'factories' => array(
        'Params' => function (ServiceLocatorInterface $helpers) {
            $services = $helpers->getServiceLocator();
            $app = $services->get('Application');
            return new \Certificat\View\Helper\Params($app->getRequest(), $app->getMvcEvent());
        }
    ),
);
