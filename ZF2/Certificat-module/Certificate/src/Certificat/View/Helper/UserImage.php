<?php

/**
 * View Helper for showing user images
 *
 * @var $imageId   int         id of image
 */

namespace Certificat\View\Helper;

use Zend\View\Helper\AbstractHelper;

/**
 * View Helper class
 */
class UserImage extends AbstractHelper
{

    /**
     * __invoke
     * 
     * @param int $imageId
     * @param bool $wrapped
     * @return string
     */
    public function __invoke($imageId = null, $wrapped = TRUE)
    {
        if ($imageId) {
            $imageUrl = $this->view->url('ce/files', array('action' => 'show', 'id' => $imageId));
        } else {
            $imageUrl = $this->view->serverUrl('/images/no_user_photo.jpg');
        }
        
        $image = '<img src="' . $imageUrl . '" class="img-responsive" />';
        
        if ($wrapped) {
            $image = '<div id="user-avatar-img">' . $image . '</div>';
        }

        return $image;        
    }

}
