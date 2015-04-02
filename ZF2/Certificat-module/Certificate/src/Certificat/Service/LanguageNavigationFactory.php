<?php
/**
 * Navigation factory class for language navigation
 *
 * @copyright   (c) 2013 Rouven Diener
 * @version     1.0
 */

namespace Certificat\Service;

use Zend\Navigation\Service\DefaultNavigationFactory;

/**
 * Factory class
 */
class LanguageNavigationFactory extends DefaultNavigationFactory
{

    /**
     * Get name for navigation
     * 
     * @return string
     */
    protected function getName()
    {
        return 'language';
    }

}
