<?php

/**
 * Base Model for Certificat
 */

namespace Certificat\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Model class
 */
abstract class BaseModel implements InputFilterAwareInterface, ServiceLocatorAwareInterface
{
    /**
     * @var ServiceLocatorInterface
     */
    protected $serviceLocator;    
    
    /**
     * @var \Zend\Mvc\I18n\Translator
     */
    protected $translator;
    
    /**
     *
     * @var InputFilter
     */
    protected $inputFilter;

    /**
     *
     * @var InputFactory
     */
    protected $inputFactory;
    
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
     * Get translator from service manager.
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
     * Constructor
     * 
     */
    public function __construct()
    {
        $this->inputFilter = new InputFilter();
        $this->inputFactory = new InputFactory();
    }

    /**
     * Get properties of given object as an associative array
     * 
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     * Set input filter for form validation
     * 
     * @param \Zend\InputFilter\InputFilterInterface $inputFilter
     * @throws \Exception
     */
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    /**
     * Get input filter for form validation
     * 
     * @return InputFilter
     */
    public function getInputFilter()
    {
        return $this->inputFilter;
    }

}
