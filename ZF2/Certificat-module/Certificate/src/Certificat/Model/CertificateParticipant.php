<?php

/**
 * Model for certificate participant
 */

namespace Certificat\Model;

/**
 * Model class
 */
class CertificateParticipant extends BaseModel
{

    /**
     *
     * @var int
     */
    public $id;

    /**
     *
     * @var int
     */
    public $certificate_id;

    /**
     *
     * @var string
     */
    public $email;

    /**
     *
     * @var string
     */
    public $first_name;

    /**
     *
     * @var string
     */
    public $last_name;

    /**
     *
     * @var string
     */
    public $birthday;

    /**
     *
     * @var string
     */
    public $comment;
    
    /**
     *
     * @var string
     */
    public $attachment;    

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
        $this->certificate_id = (!empty($data['certificate_id'])) ? $data['certificate_id'] : (!empty($this->certificate_id) ? $this->certificate_id : null);
        $this->email = (!empty($data['email'])) ? $data['email'] : (!empty($this->email) ? $this->email : null);
        $this->first_name = (!empty($data['first_name'])) ? $data['first_name'] : (!empty($this->first_name) ? $this->first_name : null);
        $this->last_name = (!empty($data['last_name'])) ? $data['last_name'] : (!empty($this->last_name) ? $this->last_name : null);
        $this->birthday = (!empty($data['birthday'])) ? $data['birthday'] : (!empty($this->birthday) ? $this->birthday : null);
        $this->comment = (!empty($data['comment'])) ? $data['comment'] : (!empty($this->comment) ? $this->comment : null);
        $this->attachment = (!empty($data['attachment'])) ? $data['attachment'] : (!empty($this->attachment) ? $this->attachment : null);
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
                    'name' => 'email',
                    'required' => true,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
                    'validators' => array(
                        array(
                            'name' => 'EmailAddress'
                        ),
                        array(
                            'name' => 'StringLength',
                            'options' => array(
                                'encoding' => 'UTF-8',
                                'min' => 1,
                                'max' => 255,
                            ),
                        ),
                    ),
        )));

        $this->inputFilter->add($this->inputFactory->createInput(array(
                    'name' => 'first_name',
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
                    'name' => 'last_name',
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
                    'name' => 'birthday',
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
                    'name' => 'comment',
                    'required' => false,
                    'filters' => array(
                        array('name' => 'StripTags'),
                        array('name' => 'StringTrim'),
                    ),
        )));

        return parent::getInputFilter();
    }

}
