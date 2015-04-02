<?php

/**
 * Model for template
 */

namespace Certificat\Model;

use SNJ\Util\Translator;

/**
 * Model class
 */
class Template extends BaseModel
{

    /**
     * Enum values for field `type`
     *
     * @var array
     */
    protected static $types = array('formation', 'task', 'function');

    /**
     *
     * @var int
     */
    public $id;

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
    public $name;

    /**
     *
     * @var string
     */
    public $type;

    /**
     *
     * @var string
     */
    public $persons_in_charge;

    /**
     *
     * @var string
     */
    public $additional_comment;

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

    /**
     * Fill properties of given object with array data
     * 
     * @param array $data
     */
    public function exchangeArray($data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : (!empty($this->id) ? $this->id : null);
        $this->user_id = (!empty($data['user_id'])) ? $data['user_id'] : (!empty($this->user_id) ? $this->user_id : null);
        $this->organization_id = (!empty($data['organization_id'])) ? $data['organization_id'] : (!empty($this->organization_id) ? $this->organization_id : null);
        $this->name = (!empty($data['name'])) ? $data['name'] : (!empty($this->name) ? $this->name : null);
        $this->type = (!empty($data['type'])) ? $data['type'] : (!empty($this->type) ? $this->type : null);
        $this->created = (!empty($data['created'])) ? $data['created'] : (!empty($this->created) ? $this->created : null);
        $this->updated = (!empty($data['updated'])) ? $data['updated'] : (!empty($this->updated) ? $this->updated : null);

        //Custom. Can be empty
        $this->persons_in_charge = (array_key_exists('persons_in_charge', $data)) ? $data['persons_in_charge'] : $this->persons_in_charge;
        $this->additional_comment = (array_key_exists('additional_comment', $data)) ? $data['additional_comment'] : $this->additional_comment;
    }

    /**
     * Get input filter for form validation
     * 
     * @return InputFilter
     */
    public function getInputFilter()
    {

        $this->inputFilter->add($this->inputFactory->createInput(array(
                    'name' => 'name',
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

        $this->inputFilter->add($this->inputFactory->createInput(array(
                    'name' => 'type',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'InArray',
                            'options' => array(
                                'haystack' => self::$types
                            ),
                        )
                    ),
        )));

        $this->inputFilter->add($this->inputFactory->createInput(array(
                    'name' => 'additional_comment',
                    'required' => false,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
        )));

        return parent::getInputFilter();
    }

    /**
     * Return enum values for select options
     *
     * @return array
     */
    public static function getTypesOptions()
    {
        $result = array();

        foreach (self::$types as $type) {
            $result[$type] = mb_convert_case(Translator::translate($type), MB_CASE_TITLE);
        }

        return $result;
    }

    /**
     * Return enum values for select options
     *
     * @param string|NULL $persons_in_charge
     */
    public function setPersonsInCharge($persons_in_charge)
    {
        $this->persons_in_charge = $persons_in_charge;
        return $this;
    }

}
