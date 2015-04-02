<?php

/**
 * Table class for сompetence.
 */

namespace Certificat\Model;

use Zend\Db\Sql\Where;
use Zend\Db\Sql\Predicate;

/**
 * Table class
 */
class CompetenceTable extends BaseModelTable
{

    /**
     * Multilanguage fields
     * 
     * @var array
     */
    protected $multilanguage_fields = array('name', 'description');

    /**
     * Languages for multilanguage fields
     * 
     * @var array
     */
    protected $languages = array('fr', 'de', 'en');

    /**
     * Get all entries in table by category_id via table gateway
     * 
     * @param int $category_id 
     * @return array
     */
    public function getByCategoryIdForAdmin($category_id)
    {
        $select = $this->tableGateway->getSql()
                ->select()
                ->join('organization', 'organization.id = ce_competence.organization_id', array('organization_name' => 'name'), 'left')
                ->where(array('category_id' => (int) $category_id));

        $lang = $this->getServiceLocator()->get('Language');
        if (in_array($lang, $this->languages)) {
            $select->order("name_$lang ASC");
        }
        
        return $this->tableGateway->selectWith($select);
    }

    /**
     * Get entries in table for organization (Admin or own) by category_id via table gateway
     * 
     * @param int $category_id 
     * @param int $organization_id 
     * @return array
     */
    public function getByCategoryIdForOrganization($category_id, $organization_id)
    {
        $organizationWhere = function (Where $where) use ($organization_id) {
            $where->nest()
                    ->equalTo('organization_id', (int) $organization_id)
                    ->or
                    ->isNull('organization_id')
                    ->unnest();
        };

        $select = $this->tableGateway->getSql()
                ->select()
                ->join('organization', 'organization.id = ce_competence.organization_id', array('organization_name' => 'name'), 'left')
                ->where($organizationWhere)
                ->where(array('category_id' => (int) $category_id));

        $lang = $this->getServiceLocator()->get('Language');
        if (in_array($lang, $this->languages)) {
            $select->order("name_$lang ASC");
        }
        
        return $this->tableGateway->selectWith($select);
    }

    /**
     * Get entry by id in table via table gateway
     * 
     * @param   int     $id
     * @return  array|\ArrayObject|null
     * @throws  \Exception
     */
    public function getCompetence($id)
    {
        $rowset = $this->tableGateway->select(array('id' => (int) $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception($this->getTranslator()->translate("Could not find сompetence #$id"));
        }
        return $row;
    }

    /**
     * Save competence as new record or update an existing record.
     * 
     * @param \Certificat\Model\Competence $competence
     * @throws \Exception
     */
    public function saveCompetence(Competence $competence)
    {
        $data = array(
            'organization_id' => $competence->organization_id,
            'category_id' => $competence->category_id,
        );

        foreach ($this->multilanguage_fields as $field) {
            foreach ($this->languages as $lang) {
                $data[$field . '_' . $lang] = $competence->{$field . '_' . $lang};
            }
        }

        $id = (int) $competence->id;

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
    public function deleteCompetence($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }

}
