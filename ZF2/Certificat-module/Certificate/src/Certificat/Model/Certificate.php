<?php

/**
 * Model for certificate
 */

namespace Certificat\Model;

use SNJ\Util\Translator;

/**
 * Model class
 */
class Certificate extends BaseModel
{

    const STATUS_DRAFT = 'draft';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ARCHIVED = 'archived';

    /**
     * Enum values for field `status`
     *
     * @var array
     */
    protected static $statuses = array('draft', 'completed', 'archived');

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
     * @var int
     */
    public $template_id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $status;

    /**
     *
     * @var string
     */
    public $language;

    /**
     *
     * @var string
     */
    public $description;

    /**
     *
     * @var int
     */
    public $duration;

    /**
     *
     * @var string
     */
    public $start_date;

    /**
     *
     * @var string
     */
    public $end_date;

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
        $this->template_id = (!empty($data['template_id'])) ? $data['template_id'] : (!empty($this->template_id) ? $this->template_id : null);
        $this->name = (!empty($data['name'])) ? $data['name'] : (!empty($this->name) ? $this->name : null);
        $this->status = (!empty($data['status'])) ? $data['status'] : (!empty($this->status) ? $this->status : null);
        $this->language = (!empty($data['language'])) ? $data['language'] : (!empty($this->language) ? $this->language : null);
        $this->description = (!empty($data['description'])) ? $data['description'] : (!empty($this->description) ? $this->description : null);
        $this->duration = (!empty($data['duration'])) ? $data['duration'] : (!empty($this->duration) ? $this->duration : null);
        $this->start_date = (!empty($data['start_date'])) ? $data['start_date'] : (!empty($this->start_date) ? $this->start_date : null);
        $this->end_date = (!empty($data['end_date'])) ? $data['end_date'] : (!empty($this->end_date) ? $this->end_date : null);
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
                    'name' => 'status',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'InArray',
                            'options' => array(
                                'haystack' => self::$statuses
                            ),
                        )
                    ),
        )));

        $this->inputFilter->add($this->inputFactory->createInput(array(
                    'name' => 'description',
                    'required' => false,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
        )));

        $this->inputFilter->add($this->inputFactory->createInput(array(
                    'name' => 'duration',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'Between',
                            'options' => array(
                                'min' => 1,
                                'max' => 65535, // Smallint
                            ),
                        ),
                    ),
        )));

        $this->inputFilter->add($this->inputFactory->createInput(array(
                    'name' => 'start_date',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'Date',
                            'options' => array(
                                'format' => 'd/m/Y',
                            ),
                        ),
                    ),
        )));

        $this->inputFilter->add($this->inputFactory->createInput(array(
                    'name' => 'end_date',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'Date',
                            'options' => array(
                                'format' => 'd/m/Y',
                            ),
                        ),
                    ),
        )));

        return parent::getInputFilter();
    }

    /**
     * Return enum values for select options
     *
     * @return array
     */
    public static function getStatusesOptions()
    {
        $result = array();

        foreach (self::$statuses as $status) {
            $result[$status] = mb_convert_case(Translator::translate($status), MB_CASE_TITLE);
        }

        return $result;
    }

}
