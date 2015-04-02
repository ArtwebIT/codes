<?php

/**
 * Table class for user_organization.
 */

namespace Certificat\Model;

use SNJ\Model\User;
use Certificat\Model\UserOrganization;

/**
 * Table class
 */
class UserOrganizationTable extends BaseModelTable
{

    /**
     * Get editors of organizarion
     * 
     * @param int $organizationId
     * @return  array|\ArrayObject|null
     */
    public function getOrganizationUsers($organizationId)
    {
        $select = $this->tableGateway->getSql()
                ->select()
                ->columns(array('user_id', 'role'))
                ->join('user', 'user.id = user_organization.user_id', array('first_name', 'last_name', 'email'))
                ->where(array('user_organization.organization_id' => (int) $organizationId))
                ->order('user.first_name ASC');

        return $this->tableGateway->selectWith($select);
    }

    /**
     * Save user to organization.
     * 
     * @param \Certificat\Model\UserOrganization $userOrganization
     * @throws \Exception
     */
    public function save(UserOrganization $userOrganization)
    {
        $data = array(
            'user_id' => $userOrganization->user_id,
            'organization_id' => $userOrganization->organization_id,
            'role' => $userOrganization->role,
        );

        try {
            return $this->tableGateway->insert($data);
        } catch (\Exception $ex) {
            throw new \Exception($this->getTranslator()->translate('Fehler beim Speichern'));
        }
    }

    /**
     * Get admin of organization
     * 
     * @param int $organizationId
     * @return  array|\ArrayObject|null
     * @throws \Exception
     */
    public function getOrganizationAdmin($organizationId)
    {
        $rowset = $this->tableGateway->select(array(
            'organization_id' => (int) $organizationId,
            'role' => UserOrganization::ROLE_ADMIN
        ));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find admin for organization #$organizationId");
        }

        return $this->getUserTable()->getUser($row->user_id);
    }

    /**
     * Get user role in organization
     * 
     * @param int $userId
     * @param int $organizationId
     * @return string
     * @throws \Exception
     */
    public function getUserRoleInOrganization($userId, $organizationId)
    {
        $rowset = $this->tableGateway->select(array(
            'user_id' => (int) $userId,
            'organization_id' => (int) $organizationId,
        ));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find role for organization #$organizationId and user #$userId");
        }

        return $row->role;
    }

    /**
     * Get user organization
     * 
     * @param int $userId
     * @return array|\ArrayObject|null
     */
    public function getUserOrganization($userId)
    {
        $rowset = $this->tableGateway->select(array(
            'user_id' => (int) $userId,
        ));
        $row = $rowset->current();
        if ($row) {
            return $this->getOrganizationTable()->getOrganization($row->organization_id);
        }
    }

    /**
     * Register user as organization editor, send activation mail
     *
     * @param \SNJ\Model\User $user
     * @param int $organization_id
     * @return int
     */
    public function registerEditor(User $user, $organization_id)
    {
        // Generate password
        $user->password = $this->getUserTable()->generatePassword();
        
        // Save password for email
        $password = $user->password;
        
        // Register user. Do not send default activation email
        $user->id = $this->getUserTable()->registerUser($user, false);

        // Get inserted user
        $user = $this->getUserTable()->getUser($user->id);
        
        // Add user to organization
        $userOrganization = new UserOrganization();
        $userOrganization->exchangeArray(array(
            'user_id' => $user->id,
            'organization_id' => (int) $organization_id,
            'role' => UserOrganization::ROLE_EDITOR,
        ));
        $this->save($userOrganization);


        $config = $this->getServiceLocator()->get('config');
        $mailConfig = $config['mail'];

        $this->renderer = $this->getServiceLocator()->get('ViewRenderer');
        $serverURL = $this->renderer->serverUrl();
        $activationLink = $serverURL . $mailConfig['route']['activation'] . '/user/' . $user->id . '/code/' . $user->activation_code;

        $organization = $this->getOrganizationTable()->getOrganization($organization_id);

        $to = $user->email;
        $subject = sprintf($this->getTranslator()->translate('The admin of "%s" has just created an account for you'), $organization->name);
        $content = $this->renderer->render('mail/organization-editor-created', array(
            'serverURL' => $serverURL,
            'activationLink' => $activationLink,
            'user' => $user,
            'password' => $password,
            'organization' => $organization
        ));

        $this->sendEmail($to, $subject, $content);

        return $user->id;
    }

}
