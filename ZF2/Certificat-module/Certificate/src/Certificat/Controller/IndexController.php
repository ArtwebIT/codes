<?php

/**
 * Controller class for guest actions
 */

namespace Certificat\Controller;

/**
 * Controller class
 */
class IndexController extends BaseController
{

    /**
     * Default Action. Shows home page.
     */
    public function indexAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('ce/my-profile');
        }
        
        // Get login and register form.
        $loginForm = $this->getFormElementManager()->get('LoginForm');
        $registerForm = $this->getFormElementManager()->get('RegisterForm');
        $registerOrganizationForm = $this->getFormElementManager()->get('RegisterOrganizationForm');

        // Populate login form with submitted data.
        // See AuthController.php
        if (isset($_SESSION['login_form_data'])) {
            $loginForm->setData($_SESSION['login_form_data']);
            unset($_SESSION['login_form_data']);
        }

        // Populate register form with submitted data.
        // See AuthController.php
        if (isset($_SESSION['register_form_data'])) {
            $registerForm->setData($_SESSION['register_form_data']);
            unset($_SESSION['register_form_data']);
        }
        
        // Populate organization register form with submitted data.
        // See AuthController.php
        if (isset($_SESSION['organization_register_form_data'])) {
            $registerOrganizationForm->setData($_SESSION['organization_register_form_data']);
            unset($_SESSION['organization_register_form_data']);
        }        
        
        $openTab = $this->params()->fromQuery('tab');
        
        return array(
            'loginForm' => $loginForm,
            'registerForm' => $registerForm,
            'registerOrganizationForm' => $registerOrganizationForm,
            'openTab' => $openTab
        );
    }

    /**
     * Action to show about page
     */
    public function aboutAction()
    {
        
    }

    /**
     * Action to show contact page.
     */
    public function contactAction()
    {
        
    }

    /**
     * Action to show page for legal information
     */
    public function legalsAction()
    {
        
    }

    /**
     * Action to show page for terms & conditions
     */
    public function termsAction()
    {
        
    }

}
