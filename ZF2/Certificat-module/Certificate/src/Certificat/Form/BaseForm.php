<?php

/**
 * Base Form
 */

namespace Certificat\Form;

use Zend\Form\Form;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Form class
 */
abstract class BaseForm extends Form implements ServiceLocatorAwareInterface
{

    /**
     *
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;

    /**
     *
     * @var \Zend\Mvc\I18n\Translator
     */
    protected $translator;

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
     * Reset the form.
     *
     * @return    \Zend\Form\Form
     */
    public function reset()
    {
        $elements = $this->getElements();

        foreach ($elements as $element) {
            if ($element instanceof \Zend\Form\Element\Text) {
                $element->setValue('');
            }

            // Other element types here
        }

        return $this;
    }

}
