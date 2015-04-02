<?php

/**
 * Controller class for user authentication and registering new users
 */

namespace Certificat\Controller;

use Zend\View\Model\JsonModel;
use SNJ\Model\User;
use Certificat\Model\Organization;
use Certificat\Model\UserOrganization;

/**
 * Controller class
 */
class AuthController extends BaseController
{

    /**
     * Action to authenticate user after submitting the login form.
     *
     * @return \Zend\Http\Response
     */
    public function authenticateAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('ce/my-profile');
        }

        $form = $this->getFormElementManager()->get('LoginForm');
        $request = $this->getRequest();

        if ($request->isPost()) {

            $data = $request->getPost();
            $form->setData($data);

            // If the form data is valid...
            if ($form->isValid()) {

                // Authenticate...
                $this->getAuthService()->getAdapter()
                        ->setIdentity($request->getPost('email'))
                        ->setCredential($request->getPost('password'));

                $result = $this->getAuthService()->authenticate();
                $success = $result->isValid();

                // Save messages temporary into flashmessenger
                foreach ($result->getMessages() as $message) {
                    $this->flashMessenger()->setNamespace($success ? 'success' : 'error');
                    $this->flashMessenger()->addMessage($this->getTranslator()->translate($message));
                }

                // If the user was found in database and it's an active user...
                if ($success) {

                    // Read user data from database.
                    $columns = array('id', 'email', 'first_name', 'last_name', 'is_admin', 'language');
                    $resultObj = $this->getAuthService()
                            ->getAdapter()
                            ->getResultRowObject($columns);

                    // If user have organization
                    $organization = $this->getUserOrganizationTable()->getUserOrganization($resultObj->id);

                    // Write user data to session.
                    $this->getAuthService()->getStorage()->write(array(
                        'id' => $resultObj->id,
                        'email' => $resultObj->email,
                        'first_name' => $resultObj->first_name,
                        'last_name' => $resultObj->last_name,
                        'fullName' => $resultObj->first_name . ' ' . $resultObj->last_name, // Heritage from EPortfolio module (shared session)
                        'is_admin' => $resultObj->is_admin,
                        'language' => $resultObj->language,
                        'organization_id' => ($organization) ? $organization->id : null,
                        'organization_role' => ($organization) ? $this->getUserOrganizationTable()->getUserRoleInOrganization($resultObj->id, $organization->id) : null,
                    ));

                    // Log successful user login in database and reset failed attempts.
                    $this->getUserTable()->loginSuccess($resultObj->id, false, false, true);

                    return $this->redirect()->toRoute('ce/my-profile');
                }

                // Log failed user login in database.
                // Lock user after 3 failed attempts.
                else if ($result->getCode() === -3 || $result->getCode() === -4) {
                    $this->getUserTable()->loginFail($request->getPost('email'));
                }
            }

            // Store form data into session so that the form
            // can be re-populated after the redirect (See IndexController.php).
            $_SESSION['login_form_data'] = $data;
        }

        return $this->redirect()->toRoute('ce/index');
    }

    /**
     * Register action. Show and evaluate registration form.
     *
     * @return \Zend\Http\Response
     */
    public function registerAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('ce/my-profile');
        }

        $form = $this->getFormElementManager()->get('RegisterForm');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            $user = new User();
            $form->setInputFilter($user->getCertificatRegistrationInputFilter());

            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                try {
                    $user->id = $this->getUserTable()->registerUser($user);
                    $message = $this->getTranslator()->translate('Du hast dich erfolgreich registriert. Bitte überprüfe deinen Posteingang und folge den Anweisungen in der Mail, die du von uns bekommen hast, um dich anzumelden.');
                    $this->flashMessenger()->addSuccessMessage($message);
                } catch (\Exception $ex) {
                    $this->flashMessenger()->addErrorMessage($ex->getMessage());
                }
            } else {
                // Save messages temporary into flashmessenger
                foreach ($form->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage(implode('<br>', $message));
                }
                $_SESSION['register_form_data'] = $request->getPost();
            }
        }

        return $this->redirect()->toRoute('ce/index');
    }

    /**
     * Register action. Show and evaluate registration form.
     *
     * @return \Zend\Http\Response
     */
    public function registerOrganizationAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('ce/my-profile');
        }

        $form = $this->getFormElementManager()->get('RegisterOrganizationForm');

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            $organization = new Organization();
            $user = new User();

            # First check the organization
            $form->setInputFilter($organization->getInputFilter());
            $oValid = $form->isValid();

            # Secont check the user
            $form->setInputFilter($user->getCertificatRegistrationInputFilter());
            $uValid = $form->isValid();

            if ($oValid && $uValid) {
                $organization->exchangeArray($form->getData());
                $user->exchangeArray($form->getData());
                try {
                    $organization->id = $this->getOrganizationTable()->saveOrganization($organization);
                    $user->id = $this->getUserTable()->registerUser($user);

                    # Add user to organization
                    $userOrganization = new UserOrganization();
                    $userOrganization->exchangeArray(array(
                        'user_id' => $user->id,
                        'organization_id' => $organization->id,
                        'role' => UserOrganization::ROLE_ADMIN,
                    ));
                    $this->getUserOrganizationTable()->save($userOrganization);

                    $message = $this->getTranslator()->translate('Du hast dich erfolgreich registriert. Bitte überprüfe deinen Posteingang und folge den Anweisungen in der Mail, die du von uns bekommen hast, um dich anzumelden.');
                    $this->flashMessenger()->addSuccessMessage($message);
                } catch (\Exception $ex) {
                    $this->flashMessenger()->addErrorMessage($ex->getMessage());
                }
            } else {
                // Save messages temporary into flashmessenger
                foreach ($form->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage(implode('<br>', $message));
                }
                $_SESSION['organization_register_form_data'] = $request->getPost();
            }
        }

        return $this->redirect()->toRoute('ce/index', array(), array('query' => array('tab' => 'organization')));
    }

    /**
     * Activate action. Activate newly registered users.
     *
     * @return \Zend\Http\Response
     */
    public function activateAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('ce/my-profile');
        }

        $id = $this->params()->fromRoute('user', 0);
        $activation_code = (string) $this->params()->fromRoute('code', '');

        $user = $this->getUserTable()->getUser((int) $id);

        if (!empty($user) && $user->activation_code === $activation_code) {
            if ($user->user_status === 'r') {
                $this->getUserTable()->unlock($id);
                $message = $this->getTranslator()->translate('Dein Benutzerkonto wurde erfolgreich aktiviert. Du kannst dich jetzt mit deiner E-Mail-Adresse und deinem Passwort anmelden.');

                # If user - organization admin
                $organization = $this->getUserOrganizationTable()->getUserOrganization($user->id);
                if ($organization && $this->getUserOrganizationTable()->getUserRoleInOrganization($user->id, $organization->id) == UserOrganization::ROLE_ADMIN) {
                    # Send the notification to application admin
                    $this->getOrganizationTable()->sendNeedApproveEmailToAdmin($organization->id);

                    # Send the notification to organization admin
                    $this->getOrganizationTable()->sendNeedApproveEmailToOrganization($organization->id);

                    $message = $this->getTranslator()->translate('Your account has been successfully activated. You can log in with your email address and password after the administrator has confirmed your company.');
                }
            } elseif (in_array($user->user_status, array('a', 'w'))) {
                $message = $this->getTranslator()->translate('Dein Benutzerkonto wurde bereits aktiviert.');
            } else {
                $message = $this->getTranslator()->translate('Dein Benutzerkonto wurde gesperrt. Bitte wende dich an einen Administrator.');
            }
        } else {
            $message = $this->getTranslator()->translate('Dein Aktivierungscode ist falsch. Bitte klicke auf den Link, der dir nach der Registrierung zugesendet wurde oder wende dich an einen Administrator.');
        }
        $this->flashMessenger()->addMessage($message);
        return $this->redirect()->toRoute('ce/index');
    }

    /**
     * Action to log out user.
     */
    public function logoutAction()
    {
        // Logout user
        $this->getSessionStorage()->forgetMe();
        $this->getAuthService()->clearIdentity();

        // Add logout message to be displayed on startpage
        $message = $this->getTranslator()->translate("Du hast dich vom System abgemeldet.");
        $this->flashmessenger()->addMessage($message);

        // Redirect to startpage
        return $this->redirect()->toRoute('ce/index');
    }

    /**
     * Action for lost password retrieval.
     *
     * @return \Zend\Http\Response
     */
    public function lostPasswordAction()
    {
        if ($this->getAuthService()->hasIdentity()) {
            return $this->redirect()->toRoute('ce/my-profile');
        }

        $form = $this->getFormElementManager()->get('LostpasswordForm');
        $message = array();

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $email = $form->get('email')->getValue();
                $user = $this->getUserTable()->findUserByEmail($email);

                if ($user !== null) {
                    $this->getUserTable()->setNewPassword($user);
                    $message = $this->getTranslator()->translate('To reset your password, follow the instructions in the email we just sent to you.');
                    $this->flashMessenger()->addSuccessMessage($message);
                } else {
                    $message = $this->getTranslator()->translate('There is no user registered with this email address.');
                    $this->flashMessenger()->addErrorMessage($message);
                }
            }
            return $this->redirect()->toRoute('ce/lost-password');
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Ajax action to reload the capthca.
     * 
     * @return \Zend\View\Model\JsonModel
     */
    public function captchaReloadAction()
    {
        $registerForm = $this->getFormElementManager()->get('RegisterForm');
        $captcha = $registerForm->get('captcha')->getCaptcha();

        $data = array();

        $data['id'] = $captcha->generate();
        $data['src'] = $captcha->getImgUrl() .
                $captcha->getId() .
                $captcha->getSuffix();

        return new JsonModel($data);
    }

    /**
     * Ajax action to check if e-mail address is already taken
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function checkEmailAction()
    {
        $email = $this->params()->fromQuery('email');

        $result = $this->getUserTable()->findUserByEmail($email);

        if ($result === null) {
            exit('true');
        } else {
            $response = $this->getTranslator()->translate('A user with this email address is already registered.');
            return new JsonModel(array($response));
        }
    }

}
