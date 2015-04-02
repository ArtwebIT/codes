<?php

/**
 * Table class for template.
 */

namespace Certificat\Model;

/**
 * Table class
 */
class TemplateTable extends BaseModelTable
{

    /**
     * Get all entries in table by organization_id via table gateway
     * 
     * @param int $organization_id 
     * @return array
     */
    public function getByOrganizationId($organization_id)
    {
        $select = $this->tableGateway->getSql()
                ->select()
                ->join('user', 'user.id = ce_template.user_id', array('user_first_name' => 'first_name', 'user_last_name' => 'last_name'))
                ->where(array('organization_id' => (int) $organization_id))
                ->order('name ASC');

        return $this->tableGateway->selectWith($select);
    }

    /**
     * Get entry by id in table via table gateway
     * 
     * @param   int     $id
     * @return  array|\ArrayObject|null
     * @throws  \Exception
     */
    public function getTemplate($id)
    {
        $rowset = $this->tableGateway->select(array('id' => (int) $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception($this->getTranslator()->translate("Could not find template #$id"));
        }
        return $row;
    }

    /**
     * Save competence as new record or update an existing record.
     * 
     * @param \Certificat\Model\Template $template
     * @throws \Exception
     */
    public function saveTemplate(Template $template)
    {
        $data = array(
            'user_id' => $template->user_id,
            'organization_id' => $template->organization_id,
            'name' => $template->name,
            'type' => $template->type,
            'persons_in_charge' => $template->persons_in_charge,
            'additional_comment' => $template->additional_comment
        );

        $id = (int) $template->id;

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
     * Delete record by given id
     *
     * @param int $id
     */
    public function deleteTemplate($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }
    
    /**
     * Get organization`s templates for select
     * 
     * @param int $organization_id
     * @return array
     */
    public function getTemplatesForSelect($organization_id)
    {
        $result = array();
        $templates = $this->getByOrganizationId($organization_id)->toArray();
        if (count($templates) > 0) {
            foreach ($templates as $template) {
                $result[$template['id']] = $template['name'];
            }            
        }
        
        return $result;
    }    

}
