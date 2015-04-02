<?php

/**
 * Table class for ce_template_competence.
 */

namespace Certificat\Model;

/**
 * Table class
 */
class TemplateCompetenceTable extends BaseModelTable
{

    /**
     * Save competence to template.
     * 
     * @param \Certificat\Model\TemplateCompetence $templateCompetence
     * @throws \Exception
     */
    public function save(TemplateCompetence $templateCompetence)
    {
        $data = array(
            'template_id' => $templateCompetence->template_id,
            'competence_id' => $templateCompetence->competence_id,
        );

        try {
            return $this->tableGateway->insert($data);
        } catch (\Exception $ex) {
            throw new \Exception($this->getTranslator()->translate('Failed to save'));
        }
    }

    public function getCompetencesIdsByTemplateId($template_id)
    {
        $result = array();
        $items = $this->tableGateway->select(array('template_id' => (int) $template_id))
                ->toArray();
        if (count($items) > 0) {
            foreach ($items as $item) {
                $result[] = $item['competence_id'];
            }
        }

        return $result;
    }

    /**
     * Delete records by template_id
     *
     * @param int $template_id
     */
    public function deleteByTemplateId($template_id)
    {
        $this->tableGateway->delete(array('template_id' => (int) $template_id));
    }

}
