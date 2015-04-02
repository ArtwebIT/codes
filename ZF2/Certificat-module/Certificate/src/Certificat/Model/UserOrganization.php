<?php

/**
 * Model for organization
 */

namespace Certificat\Model;

/**
 * Model class
 */
class UserOrganization extends BaseModel
{

    const ROLE_ADMIN = 'admin';
    const ROLE_EDITOR = 'editor';

    /**
     *
     * @var int
     */
    public $user_id;

    /**
     *
     * @var int
     */
    public $organization_id;

    /**
     *
     * @var string
     */
    public $role;

    /**
     * Fill properties of given object with array data
     * 
     * @param array $data
     */
    public function exchangeArray($data)
    {
        $this->user_id = (!empty($data['user_id'])) ? $data['user_id'] : (!empty($this->user_id) ? $this->user_id : null);
        $this->organization_id = (!empty($data['organization_id'])) ? $data['organization_id'] : (!empty($this->organization_id) ? $this->organization_id : null);
        $this->role = (!empty($data['role'])) ? $data['role'] : (!empty($this->role) ? $this->role : null);
    }

}
