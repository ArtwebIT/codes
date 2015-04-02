<?php

/**
 * Model for template and competence
 */

namespace Certificat\Model;

/**
 * Model class
 */
class TemplateCompetence extends BaseModel
{

    /**
     *
     * @var int
     */
    public $template_id;

    /**
     *
     * @var int
     */
    public $competence_id;

    /**
     * Fill properties of given object with array data
     * 
     * @param array $data
     */
    public function exchangeArray($data)
    {
        $this->template_id = (!empty($data['template_id'])) ? $data['template_id'] : (!empty($this->template_id) ? $this->template_id : null);
        $this->competence_id = (!empty($data['competence_id'])) ? $data['competence_id'] : (!empty($this->competence_id) ? $this->competence_id : null);
    }

}
