<?php

/**
 * Table class for сompetence category.
 */

namespace Certificat\Model;

/**
 * Table class
 */
class CompetenceCategoryTable extends BaseModelTable
{

    /**
     * Multilanguage fields
     * 
     * @var array
     */
    protected $multilanguage_fields = array('name');

    /**
     * Languages for multilanguage fields
     * 
     * @var array
     */
    protected $languages = array('fr', 'de', 'en');

    /**
     *
     * @var \Certificat\Model\CompetenceTable
     */
    public $competenceTable;

    /**
     * Get competence table via service manager
     *
     * @return \Certificat\Model\CompetencesTable
     */
    public function getCompetenceTable()
    {
        if (!$this->competenceTable) {
            $this->competenceTable = $this->getServiceLocator()->get('Certificat\Model\CompetenceTable');
        }
        return $this->competenceTable;
    }

    /**
     * Get all categories with all competences. Only for Admin
     * 
     * @return array
     */
    public function getCategoriesWithAllCompetences()
    {
        $categories = $this->fetchAll()->toArray();
        if (count($categories) > 0) {
            foreach ($categories as &$category) {
                $category['competences'] = $this->getCompetenceTable()
                        ->getByCategoryIdForAdmin($category['id'])
                        ->toArray();
            }
            unset($category);
        }
        return $categories;
    }

    /**
     * Get all categories with competences for organization
     * 
     * @param int $organization_id
     * @return array
     */
    public function getCategoriesWithOrganizationCompetences($organization_id)
    {
        $categories = $this->fetchAll()->toArray();
        if (count($categories) > 0) {
            foreach ($categories as &$category) {
                $category['competences'] = $this->getCompetenceTable()
                        ->getByCategoryIdForOrganization($category['id'], $organization_id)
                        ->toArray();
            }
            unset($category);
        }
        return $categories;
    }

    /**
     * Get all categories with organization competences for select with optgroup
     * 
     * @param int $organization_id
     * @return array
     */
    public function getCategoriesWithOrganizationCompetencesForSelect($organization_id)
    {
        $result = array();
        $lang = $this->getServiceLocator()->get('Language');
        $categories = $this->getCategoriesWithOrganizationCompetences($organization_id);
        if (count($categories) > 0) {
            foreach ($categories as $category) {
                if (count($category['competences']) > 0) {
                    $options = array();
                    foreach ($category['competences'] as $competence) {
                        $options[$competence['id']] = $competence['name_' . $lang];
                    }
                    $result[] = array(
                        'label' => $category['name_' . $lang],
                        'options' => $options
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Fetch all entries in table via table gateway. Ordered by name
     * 
     * @return null|ResultSetInterface
     */
    public function fetchAll()
    {
        $select = $this->tableGateway->getSql()->select();

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
    public function getCompetenceCategory($id)
    {
        $rowset = $this->tableGateway->select(array('id' => (int) $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception($this->getTranslator()->translate("Could not find сompetence category #$id"));
        }
        return $row;
    }

    /**
     * Save competence category as new record or update an existing record.
     * 
     * @param \Certificat\Model\CompetenceCategory $competenceCategory
     * @throws \Exception
     */
    public function saveCompetenceCategory(CompetenceCategory $competenceCategory)
    {
        foreach ($this->multilanguage_fields as $field) {
            foreach ($this->languages as $lang) {
                $data[$field . '_' . $lang] = $competenceCategory->{$field . '_' . $lang};
            }
        }

        $id = (int) $competenceCategory->id;

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
    public function deleteCompetenceCategory($id)
    {
        $this->tableGateway->delete(array('id' => (int) $id));
    }

    /**
     * Get languages for this entity
     * 
     * @return  array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

}
