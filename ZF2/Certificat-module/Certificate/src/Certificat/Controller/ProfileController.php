<?php

/**
 * Controller class for guest actions
 */

namespace Certificat\Controller;

/**
 * Controller class
 */
class ProfileController extends BaseController
{

    /**
     * Action for showing and editing personal user data.
     * 
     * @return \Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        $identity = $this->getServiceLocator()->get('AuthService')->getIdentity();
        $id = (int) $identity['id'];
        $user = $this->getUserTable()->getUser($id);

        $formName = ($this->layout()->userRole == 'participant') ? 'ParticipantUserProfileForm' : 'OrganizationUserProfileForm';
        $form = $this->getFormElementManager()->get($formName);
        $cropForm = $this->getFormElementManager()->get('CropForm');

        // Populate form with submitted data or with user from DB
        if (isset($_SESSION['user_profile_form_data'])) {
            $form->setData($_SESSION['user_profile_form_data']);
            unset($_SESSION['user_profile_form_data']);
        } else {
            $form->setData($user->getArrayCopy());
        }

        return array(
            'form' => $form,
            'cropForm' => $cropForm,
            'user' => $user
        );
    }

    /**
     * Action for saving personal user data.
     * 
     * @return Response
     */
    public function saveAction()
    {
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $identity = $this->getServiceLocator()->get('AuthService')->getIdentity();
            $id = (int) $identity['id'];
            $user = $this->getUserTable()->getUser($id);
            
            $formName = ($this->layout()->userRole == 'participant') ? 'ParticipantUserProfileForm' : 'OrganizationUserProfileForm';
            $form = $this->getFormElementManager()->get($formName);

            // Set user id
            $data['id'] = $user->id;
            
            // strtotime not working with date in format dd/mm/yyyy
            $data['date_of_birth'] = str_replace('/', '-', $data['date_of_birth']);
            $data['date_of_birth'] = date('Y-m-d', strtotime($data['date_of_birth']));

            // Remember the user's e-mail address.
            $email = $user->email;

            $form->setData($data);
            $form->setInputFilter($user->getEditUserInputFilterForCertificatModule($this->layout()->userRole));

            if ($form->isValid()) {
                $user->exchangeArray($form->getData());

                // Set the mail address again so that it won't be
                // overwritten with an empty string.
                $user->email = $email;

                try {
                    $this->getUserTable()->saveUser($user);

                    // Change password if needed
                    if (!empty($data['password']) && $data['password'] === $data['password_check']) {
                        $this->getUserTable()->setPassword($user, $data['password']);
                    }

                    $message = $this->getTranslator()->translate('Your changes was successfully saved.');
                    $this->flashMessenger()->addSuccessMessage($message);
                } catch (\Exception $ex) {
                    $this->flashMessenger()->addErrorMessage($ex->getMessage());
                }
            } else {
                foreach ($form->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage(implode('<br>', $message));
                }

                $_SESSION['user_profile_form_data'] = $request->getPost();
            }
        }

        return $this->redirect()->toRoute('ce/my-profile');
    }

}
