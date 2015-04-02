<?php

/**
 * View Helper for showing title of user role
 *
 * @var $userRole string
 */

namespace Certificat\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * View Helper class
 */
class UserRoleTitle extends AbstractHelper
{

    /**
     * __invoke
     *
     * @param string    $userRole
     * @return string
     */
    public function __invoke($userRole)
    {
        switch ($userRole) {
            case 'application_admin':
                $title = $this->getView()->translate('Administrator');
                break;

            case 'participant':
                $title = $this->getView()->translate('Participant');
                break;

            default:
                $title = $this->getView()->translate('Organization');
                break;
        }

        return $title;
    }

}
