<?php

/**
 * Model for competence category
 */

namespace Certificat\Model;

/**
 * Model class
 */
class CompetenceCategory extends BaseModel
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
     * @var int
     */
    public $id;


    /**
     *
     * @var string
     */
    public $created;

    /**
     *
     * @var string
     */
    public $updated;

    public function __construct()
    {
        parent::__construct();

        foreach ($this->multilanguage_fields as $field) {
            foreach ($this->languages as $lang) {
                $this->{$field . '_' . $lang} = NULL;
            }
        }
    }

    /**
     * Fill properties of given object with array data
     * 
     * @param array $data
     */
    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : (!empty($this->id) ? $this->id : null);
        foreach ($this->multilanguage_fields as $field) {
            foreach ($this->languages as $lang) {
                $fieldLang = $field . '_' . $lang;
                $this->{$fieldLang} = (!empty($data[$fieldLang])) ? $data[$fieldLang] : (!empty($this->{$fieldLang}) ? $this->{$fieldLang} : null);
            }
        }
        $this->created = (!empty($data['created'])) ? $data['created'] : (!empty($this->created) ? $this->created : null);
        $this->updated = (!empty($data['updated'])) ? $data['updated'] : (!empty($this->updated) ? $this->updated : null);
    }

    /**
     * Get input filter for form validation
     * 
     * @return InputFilter
     */
    public function getInputFilter()
    {
        foreach ($this->languages as $lang) {
            $this->inputFilter->add($this->inputFactory->createInput(array(
                        'name' => 'name_' . $lang,
                        'required' => true,
                        'filters' => array(
                            array('name' => 'StripTags'),
                            array('name' => 'StringTrim'),
                        ),
                        'validators' => array(
                            array(
                                'name' => 'StringLength',
                                'options' => array(
                                    'encoding' => 'UTF-8',
                                    'min' => 1,
                                    'max' => 100,
                                ),
                            )
                        ),
            )));
        }

        return parent::getInputFilter();
    }

}
