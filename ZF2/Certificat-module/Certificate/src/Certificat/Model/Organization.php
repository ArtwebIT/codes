<?php

/**
 * Model for organization
 */

namespace Certificat\Model;

/**
 * Model class
 */
class Organization extends BaseModel
{

    /**
     *
     * @var int
     */
    public $id;

    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $street;

    /**
     *
     * @var string
     */
    public $house;

    /**
     *
     * @var string
     */
    public $zip_code;

    /**
     *
     * @var string
     */
    public $city;

    /**
     *
     * @var string
     */
    public $country;

    /**
     *
     * @var string
     */
    public $phone;

    /**
     *
     * @var string
     */
    public $website;
    
    /**
     *
     * @var int
     */
    public $logo_id;    

    /**
     *
     * @var bool
     */
    public $approved;

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
        $this->name = (!empty($data['name'])) ? $data['name'] : (!empty($this->name) ? $this->name : null);
        $this->street = (!empty($data['street'])) ? $data['street'] : (!empty($this->street) ? $this->street : null);
        $this->house = (!empty($data['house'])) ? $data['house'] : (!empty($this->house) ? $this->house : null);
        $this->zip_code = (!empty($data['zip_code'])) ? $data['zip_code'] : (!empty($this->zip_code) ? $this->zip_code : null);
        $this->city = (!empty($data['city'])) ? $data['city'] : (!empty($this->city) ? $this->city : null);
        $this->country = (!empty($data['country'])) ? $data['country'] : (!empty($this->country) ? $this->country : null);
        $this->phone = (!empty($data['phone'])) ? $data['phone'] : (!empty($this->phone) ? $this->phone : null);
        $this->website = (!empty($data['website'])) ? $data['website'] : (!empty($this->website) ? $this->website : null);
        $this->logo_id = (!empty($data['logo_id'])) ? $data['logo_id'] : (!empty($this->logo_id) ? $this->logo_id : null);
        $this->approved = (!empty($data['approved'])) ? $data['approved'] : (!empty($this->approved) ? $this->approved : 0);
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
                    'name' => 'street',
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
                    'name' => 'house',
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
                                'max' => 10,
                            ),
                        )
                    ),
        )));

        $this->inputFilter->add($this->inputFactory->createInput(array(
                    'name' => 'zip_code',
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
                                'max' => 10,
                            ),
                        ),
                    ),
        )));

        $this->inputFilter->add($this->inputFactory->createInput(array(
                    'name' => 'city',
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
                                'max' => 60,
                            ),
                        )
                    ),
        )));

        $this->inputFilter->add($this->inputFactory->createInput(array(
                    'name' => 'country',
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
                                'min' => 2,
                                'max' => 2,
                            ),
                        ),
                        array(
                            'name' => 'Alpha',
                        ),
                    ),
        )));

        $this->inputFilter->add($this->inputFactory->createInput(array(
                    'name' => 'phone',
                    'required' => false,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'max' => 30,
                            ),
                        ),
                        array(
                            'name' => 'Regex',
                            'options' => array(
                                'pattern' => '/^[0-9()+-\s]+$/',
                                'messages' => array(
                                    \Zend\Validator\Regex::NOT_MATCH => "Phone invalid, only 0-9 ( ) + - characters allowed",
                                ),
                            ),
                        ),
                    ),
        )));

        $this->inputFilter->add($this->inputFactory->createInput(array(
                    'name' => 'website',
                    'required' => false,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'max' => 255,
                            ),
                        ),
                        array(
                            'name' => 'Uri',
                            'options' => array(
                                'allowRelative' => false
                            ),
                        )
                    ),
        )));

        return parent::getInputFilter();
    }

}
