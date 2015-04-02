<?php

/**
 * Table class for organization.
 */

namespace Certificat\Model;

use Zend\Db\Sql\Where;
use SNJ\Model\File;

/**
 * Table class
 */
class OrganizationTable extends BaseModelTable
{

    /**
     * Fetch all entries in table via table gateway
     * 
     * @return array
     */
    public function fetchAll($search = '')
    {
        $select = $this->tableGateway->getSql()->select();
        if ($search != '') {
            $like = function (Where $where) use ($search) {
                $where->like('name', "%$search%");
            };
            $select->where($like);
        }
        $select->order("created DESC");
        $resultSet = $this->tableGateway->selectWith($select);
        return $resultSet;
    }

    /**
     * Get entry by id in table via table gateway
     * 
     * @param   int     $id
     * @return  array|\ArrayObject|null
     * @throws  \Exception
     */
    public function getOrganization($id)
    {
        $rowset = $this->tableGateway->select(array('id' => (int) $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception($this->getTranslator()->translate("Could not find organization #$id"));
        }
        return $row;
    }

    /**
     * Save organization as new record or update an existing record.
     * 
     * @param \Certificat\Model\Organization $organization
     * @throws \Exception
     */
    public function saveOrganization(Organization $organization)
    {
        $data = array(
            'name' => $organization->name,
            'street' => $organization->street,
            'house' => $organization->house,
            'zip_code' => $organization->zip_code,
            'city' => $organization->city,
            'country' => $organization->country,
            'phone' => $organization->phone,
            'website' => $organization->website,
        );

        $id = (int) $organization->id;

        try {
            if (empty($id)) {
                $data['created'] = date('Y-m-d H:i:s');
                $this->tableGateway->insert($data);
                $id = $this->tableGateway->lastInsertValue;
            } else {
                $this->tableGateway->update($data, array('id' => $id));
            }
        } catch (\Exception $ex) {
            throw new \Exception($this->getTranslator()->translate('Failed to save'));
        }

        return $id;
    }
    
    /**
     * Add logo for organization
     *
     * @param \SNJ\Model\File $file
     * @throws \Exception
     */
    public function addOrganizationLogo(File $file)
    {
        $data = array(
            'logo_id' => $file->id,
        );

        $id = (int) $file->organization_id;
        
        if ($id == 0) {
            throw new \Exception($this->getTranslator()->translate('No organizations selected'));
        } else {
            if ($this->getOrganization($id)) {
                try {
                    $this->tableGateway->update($data, array('id' => $id));
                } catch (\Exception $ex) {
                    throw new \Exception($this->getTranslator()->translate('Failed to save'));
                }
            } else {
                throw new \Exception($this->getTranslator()->translate('The organization does not exist'));
            }
        }
    }    

    /**
     * Delete file relations for organization
     *
     * @param int $organization_id
     */
    public function deleteOrganizationLogo($organization_id)
    {
        $data = array(
            'logo_id' => null,
        );

        try {
            $this->tableGateway->update($data, array('id' => (int) $organization_id));
        } catch (\Exception $ex) {
            throw new \Exception($this->getTranslator()->translate('Failed to save'));
        }
    }    

    /**
     * Set approved/disapproved organization status
     * 
     * @param int $organizationId
     * @param int $approved
     * @throws \Exception
     */
    private function _toggleApproved($organizationId, $approved)
    {
        $id = (int) $organizationId;

        $data = array(
            'approved' => (int) $approved,
        );

        if ($this->getOrganization($id)) {
            try {
                $this->tableGateway->update($data, array('id' => $id));
            } catch (\Exception $e) {
                throw new \Exception($this->getTranslator()->translate('Failed to save'));
            }
        }
    }

    /**
     * Set approved organization status
     * 
     * @param int $organizationId
     */
    public function approveOrganization($organizationId)
    {
        $this->_toggleApproved($organizationId, 1);
        $organizationAdmin = $this->getUserOrganizationTable()->getOrganizationAdmin($organizationId);

        $to = $organizationAdmin->email;
        $subject = $this->getTranslator()->translate('Your organization has been approved');
        $content = $this->getViewRenderer()->render('mail/organization-approved', array(
            'serverURL' => $this->renderer->serverUrl(),
        ));

        $this->sendEmail($to, $subject, $content);
    }

    /**
     * Set disapproved organization status
     * 
     * @param int $organizationId
     */
    public function disapproveOrganization($organizationId)
    {
        $this->_toggleApproved($organizationId, 0);
        $organizationAdmin = $this->getUserOrganizationTable()->getOrganizationAdmin($organizationId);

        $to = $organizationAdmin->email;
        $subject = $this->getTranslator()->translate('Your organization has not been approved');
        $content = $this->getViewRenderer()->render('mail/organization-disapproved', array(
            'serverURL' => $this->renderer->serverUrl(),
        ));

        $this->sendEmail($to, $subject, $content);
    }

    /**
     * Send notification to admin about new organization
     * 
     * @param int $organizationId
     */
    public function sendNeedApproveEmailToAdmin($organizationId)
    {
        $config = $this->getServiceLocator()->get('config');
        $mailConfig = $config['mail'];

        $to = $mailConfig['recipient']['admin'];
        $subject = $this->getTranslator()->translate('New organization');
        $content = $this->getViewRenderer()->render('mail/admin/organization-need-approve', array(
            'serverURL' => $this->renderer->serverUrl(),
            'organization' => $this->getOrganization($organizationId),
        ));

        $this->sendEmail($to, $subject, $content);
    }

    /**
     * Send notification to organization about waiting of confirmation by admin
     * 
     * @param int $organizationId
     */
    public function sendNeedApproveEmailToOrganization($organizationId)
    {
        $organizationAdmin = $this->getUserOrganizationTable()->getOrganizationAdmin($organizationId);

        $to = $organizationAdmin->email;
        $subject = $this->getTranslator()->translate('Thanks for subscribing');
        $content = $this->getViewRenderer()->render('mail/organization-need-approve', array(
            'serverURL' => $this->renderer->serverUrl(),
            'organization' => $this->getOrganization($organizationId),
        ));

        $this->sendEmail($to, $subject, $content);
    }

    /**
     * Delete record by given id
     *
     * @param int $id
     */
    public function deleteOrganization($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }    
}
