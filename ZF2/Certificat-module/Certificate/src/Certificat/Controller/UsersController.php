<?php

/**
 * Controller class for guest actions
 */

namespace Certificat\Controller;

use SNJ\Model\User;
use Certificat\Model\UserOrganization;

/**
 * Controller class
 */
class UsersController extends BaseController
{

    /**
     * Default Action. Shows templates.
     *
     */
    public function indexAction()
    {
        $identity = $this->getAuthService()->getIdentity();
        $organization_id = (int) $identity['organization_id'];

        $form = $this->getFormElementManager()->get('OrganizationEditorForm');

        // Populate form with submitted data.
        if (isset($_SESSION['organization_editor_form_data'])) {
            $form->setData($_SESSION['organization_editor_form_data']);
            unset($_SESSION['organization_editor_form_data']);
        }

        return array(
            'form' => $form,
            'users' => $this->getUserOrganizationTable()->getOrganizationUsers($organization_id)->toArray()
        );
    }

    public function saveEditorAction()
    {
        $identity = $this->getAuthService()->getIdentity();
        $organization_id = (int) $identity['organization_id'];
        $fromRole = $identity['organization_role'];
        
        if ($fromRole != UserOrganization::ROLE_ADMIN) {
            throw new \Exception("Only user with role '" . UserOrganization::ROLE_ADMIN . "' has access");
        }        
        
        $form = $this->getFormElementManager()->get('OrganizationEditorForm');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            $user = new User();
            $form->setInputFilter($user->getCertificatOrganizationEditorInputFilter());

            if ($form->isValid()) {
                $user->exchangeArray($form->getData());
                try {
                    $user->id = $this->getUserOrganizationTable()->registerEditor($user, $organization_id);
                    $message = $this->getTranslator()->translate('User was successfully added.');
                    $this->flashMessenger()->addSuccessMessage($message);
                } catch (\Exception $ex) {
                    $this->flashMessenger()->addErrorMessage($ex->getMessage());
                }
            } else {
                // Save messages temporary into flashmessenger
                foreach ($form->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage(implode('<br>', $message));
                }
                $_SESSION['organization_editor_form_data'] = $request->getPost();
            }
        }

        return $this->redirect()->toRoute('ce/users');
    }

    public function deleteEditorAction()
    {
        $user_id = (int) $this->params()->fromRoute('id');
        $organization = $this->getUserOrganizationTable()->getUserOrganization($user_id);
        $role = $this->getUserOrganizationTable()->getUserRoleInOrganization($user_id, $organization->id);

        $identity = $this->getAuthService()->getIdentity();
        $organization_id = (int) $identity['organization_id'];
        $fromRole = $identity['organization_role'];

        if ($organization->id != $organization_id || $fromRole != UserOrganization::ROLE_ADMIN) {
            throw new \Exception("Only user with role '" . UserOrganization::ROLE_ADMIN . "' has access");
        }

        if ($role == UserOrganization::ROLE_ADMIN) {
            throw new \Exception("You can not delete the user with role '" . UserOrganization::ROLE_ADMIN . "'");
        }

        try {
            $this->getUserTable()->deleteUser($user_id);
            $message = $this->getTranslator()->translate('User was successfully deleted.');
            $this->flashMessenger()->addSuccessMessage($message);
        } catch (\Exception $ex) {
            $this->flashMessenger()->addErrorMessage($ex->getMessage());
        }

        return $this->redirect()->toRoute('ce/users');
    }

}
