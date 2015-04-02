<?php

/**
 * View Helper for showing organization logo
 *
 * @var $image_id   int         id of image
 */

namespace Certificat\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * View Helper class
 */
class OrganizationLogo extends AbstractHelper
{

    /**
     * __invoke
     * 
     * @param int $image_id
     * @param bool $wrapped
     * @return string
     */
    public function __invoke($image_id = null, $wrapped = TRUE)
    {
        if ($image_id) {
            $imageUrl = $this->view->url('ce/files', array('action' => 'show', 'id' => $image_id));
        } else {
            $imageUrl = $this->view->serverUrl('/images/no_organization_logo.jpg');
        }
        
        $image = '<img src="' . $imageUrl . '" class="img-responsive" />';
        
        if ($wrapped) {
            $image = '<div id="organization-logo-img">' . $image . '</div>';
        }

        return $image;
    }

}
