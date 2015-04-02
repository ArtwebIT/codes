<?php

class SignUpForm extends CFormModel
{
	public $email;
	public $password;
	public $confirm_password;
	public $first_name;
	public $last_name;
    public $phone_id;
    public $merchant_name;
    public $merchant_code;
    public $address;
    public $city;
    public $state;
    public $postal_code;
    public $country;
    public $phone;
    public $short_link;
    public $website;
    public $business_category_id;
	
	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            array('email', 'required', 'message' => $error_messages[ErrorProcessor::SIGNUP_EMAIL_EMPTY]),
            array('password', 'required', 'message' => $error_messages[ErrorProcessor::SIGNUP_PASSWORD_EMPTY]),
            array('confirm_password', 'required', 'message' => $error_messages[ErrorProcessor::SIGNUP_CPASSWORD_EMPTY]),
			array('email', 'email', 'message' => $error_messages[ErrorProcessor::SIGNUP_EMAIL_WRONG]),
            array('email', 'length', 'max' => 50, 'message' => $error_messages[ErrorProcessor::SIGNUP_EMAIL_LENGTH]),
			array('password', 'password_unique_length', 'message' => $error_messages[ErrorProcessor::SIGNUP_PASSWORD_LENGTH]),
			array('confirm_password', 'compare', 'compareAttribute'=>'password', 'on'=>array('register', 'api'), 'message' => $error_messages[ErrorProcessor::SIGNUP_CPASSWORD_PASSWORD_NOT_MATCH]),
			array('last_name', 'valid_last_name', 'message' => $error_messages[ErrorProcessor::SIGNUP_LNAME_LENGTH]),
            array('first_name', 'valid_first_name', 'message' => $error_messages[ErrorProcessor::SIGNUP_LNAME_LENGTH]),
			array('email', 'email_unique', 'message' => $error_messages[ErrorProcessor::SIGNUP_EMAIL_NOT_UNIQUE]),
            array('phone_id', 'required', 'on' => 'api', 'message' => $error_messages[ErrorProcessor::SIGNUP_PHONE_ID_EMPTY]),
            array('merchant_name', 'length', 'max' => 255),
            array('address', 'valid_address', 'message' => $error_messages[ErrorProcessor::SIGNUP_ADDRESS_LENGTH]),
            array('city', 'valid_city', 'message' => $error_messages[ErrorProcessor::SIGNUP_CITY_LENGTH]),
            array('state', 'valid_state', 'message' => $error_messages[ErrorProcessor::SIGNUP_STATE_LENGTH]),
            array('postal_code', 'valid_postal_code', 'message' => $error_messages[ErrorProcessor::SIGNUP_POSTAL_CODE_LENGTH]),
            array('country', 'valid_country', 'message' => $error_messages[ErrorProcessor::SIGNUP_COUNTRY_LENGTH]),
            array('phone', 'valid_phone', 'message' => $error_messages[ErrorProcessor::SIGNUP_PHONE_LENGTH]),
            array('website', 'valid_website', 'message' => $error_messages[ErrorProcessor::SIGNUP_WEBSITE_LENGTH]),
            array('merchant_code', 'unsafe'),
            array('business_category_id', 'length', 'max' => 2),

			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			// array('username, email, password, confirm_password, first_name, last_name', 'safe', 'on'=>'search'),
		);
	}


    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'email'             => 'Email',
            'password'          => 'Password',
            'confirm_password'  => 'Confirm password',
            'first_name'        => 'First Name',
            'last_name'         => 'Last Name',
            'phone_id'          => 'phone_id',
            'merchant_name'     => 'Merchant Name',
            'merchant_code'     => 'Merchant Code',
            'address'           => 'Address',
            'city'              => 'City',
            'state'             => 'State',
            'postal_code'       => 'Postal Code',
            'country'           => 'Country',
            'phone'             => 'Phone',
            'website'           => 'Website URL',
            'business_category_id' => 'Business Category',
        );
    }

    /**
	 * Check email uniqueness
	 * This is the 'unique' validator as declared in rules().
	 */
	public function email_unique($attribute,$params)
	{
        $exists = User::model()->exists('email=:email', array(':email'=>$this->email));

        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        if($exists)
        {
			$this->addError('email',$error_messages[ErrorProcessor::SIGNUP_EMAIL_NOT_UNIQUE]);
        }
	}


    public function password_unique_length($attribute, $params)
    {
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        $length = strlen($this->password);
        if (($length < 6) or ($length > 50)) {
            $this->addError('password', $error_messages[ErrorProcessor::SIGNUP_PASSWORD_LENGTH]);
        }
    }

    public function valid_last_name($attribute, $params)
    {
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        if (strlen($this->last_name) > 50) {
            $this->addError('last_name', $error_messages[ErrorProcessor::SIGNUP_LNAME_LENGTH]);
        }
    }

    public function valid_first_name($attribute, $params)
    {
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        if (strlen($this->first_name) > 50) {
            $this->addError('first_name', $error_messages[ErrorProcessor::SIGNUP_LNAME_LENGTH]);
        }
    }


    /*
    public function valid_merchant_name($attribute, $params)
    {
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        if (!empty($this->merchant_name)) {
            $find_user = User::model()->find('merchant_name=:name', array(':name' => $this->merchant_name));
            if (isset($find_user)) {
                $this->addError('merchant_name', $error_messages[ErrorProcessor::SIGNUP_MERCHANT_NAME_ISSET]);
            }
        }
    }
    */

    public function valid_address($attribute, $params)
    {
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        if (strlen($this->address) > 255) {
            $this->addError('address', $error_messages[ErrorProcessor::SIGNUP_ADDRESS_LENGTH]);
        }
    }

    public function valid_city($attribute, $params)
    {
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        if (strlen($this->city) > 50) {
            $this->addError('city', $error_messages[ErrorProcessor::SIGNUP_CITY_LENGTH]);
        }
    }

    public function valid_state($attribute, $params)
    {
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        if (strlen($this->state) > 50) {
            $this->addError('state', $error_messages[ErrorProcessor::SIGNUP_STATE_LENGTH]);
        }
    }

    public function valid_postal_code($attribute, $params)
    {
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        if (strlen($this->postal_code) > 50) {
            $this->addError('code', $error_messages[ErrorProcessor::SIGNUP_POSTAL_CODE_LENGTH]);
        }
    }

    public function valid_country($attribute, $params)
    {
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        if (strlen($this->country) > 50) {
            $this->addError('country', $error_messages[ErrorProcessor::SIGNUP_COUNTRY_LENGTH]);
        }
    }

    public function valid_phone($attribute, $params)
    {
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        if (strlen($this->phone) > 50) {
            $this->addError('phone', $error_messages[ErrorProcessor::SIGNUP_PHONE_LENGTH]);
        }
    }

    public function valid_website($attribute, $params)
    {
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        if (strlen($this->website) > 255) {
            $this->addError('website', $error_messages[ErrorProcessor::SIGNUP_WEBSITE_LENGTH]);
        }
    }



}