<?php

/**
 * This is the model class for table "user".
 *
 * The followings are the available columns in table 'user':
 * @property integer $id
 * @property string $email
 * @property string $password
 * @property string $confirm_password
 * @property string $first_name
 * @property string $last_name
 * @property string $auth_token
 * @property string $confirmation_token
 * @property integer $updated_at
 * @property integer $created_at
 * @property string $username
 * @property integer $activated
 * @property string $phone_id
 * @property string $merchant_name
 * @property string $merchant_code
 * @property string $address
 * @property string $city
 * @property string $state
 * @property string $postal_code
 * @property string $country
 * @property string $phone
 * @property string $short_link
 * @property string $website
 * @property string $facebook
 * @property string $twitter
 * @property string $google
 * @property string $linkedin
 * @property string $youtube_username
 * @property string $youtube_password
 * @property integer $business_category_id
 * @property integer $account_type
 * @property integer $is_youtube_default
 * @property integer $limit_record
 * @property integer $id_itunes
 * @property integer $description
 * @property integer $date_upgrade
 * @property integer $date_final
 *
 */
class User extends CActiveRecord
{
    public $confirm_password;
    public $first_name;
    public $merchant_code;
    //private $error_messages;
    public $account_type;
    public $is_youtube_default;
    public $linkedin;
    public $youtube_username = '';
    public $youtube_password = '';
    public $limit_record;
    public $id_itunes;
    public $description;
    public $date_upgrade;
    public $date_final;
    /**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return User the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'User';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{

        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('email, auth_token', 'required'),
            array('password', 'password_unique_length'),
            array('confirm_password', 'valid_confirm_password', 'on' => 'update'),
			// array('updated_at, created_at, activated', 'numerical', 'integerOnly'=>true),
			array('email', 'length', 'max' => 50),
			array('auth_token, confirmation_token', 'length', 'max' => 32),
			array('first_name, last_name', 'length', 'max' => 50),
            array('merchant_name', 'length', 'max' => 255),
            array('address', 'length', 'max' => 255),
            array('city', 'length', 'max' => 255),
            array('state', 'length', 'max' => 255),
            array('postal_code', 'length', 'max' => 255),
            array('country', 'length', 'max' => 255),
            array('phone', 'length', 'max' => 255),
            array('website', 'length', 'max' => 255),
            array('facebook', 'valid_url', 'max' => 255, 'on' => 'update'),
            array('twitter', 'valid_url', 'max' => 255, 'on' => 'update'),
            array('google', 'valid_url', 'max' => 255, 'on' => 'update'),
            array('linkedin', 'valid_url', 'max' => 255, 'on' => 'update'),
            array('youtube_username', 'length', 'max' => 50),
            array('youtube_password', 'valid_youtube_password'),
            array('business_category_id', 'length', 'max' => 11),
            array('limit_record', 'length', 'max' => 10),
            array('account_type', 'length', 'max' => 2),
            array('id_itunes', 'length', 'max' => 255),
            array('description', 'length', 'max' => 255),
            array('date_upgrade', 'length', 'max' => 255),
            array('date_final', 'length', 'max' => 255),



          	// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, email, id_itunes, description, first_name, last_name, auth_token, confirmation_token, updated_at, created_at, activated, phone_id, merchant_name, address, city, state, postal_code, country, phone, website, facebook, twitter, google, linkedin, youtube_username, youtube_password, business_category_id, limit_record', 'safe', 'on'=>'search'),

            /*
             * Filter for XSS atack
             */
            array('
                email,
                first_name,
                password,
                confirm_password,
                merchant_name,
                address,
                state,
                city,
                state,
                postal_code,
                country,
                phone,
                website,
                facebook,
                twitter,
                google,
                linkedin,
                youtube_username,
                youtube_password,
                limit_record,
                description,
                id_itunes
                ',

                'filter', 'filter' => array($obj = new CHtmlPurifier(),'purify')
            ),
		);
	}

    public function beforeSave()
    {
        if ($this->isNewRecord)
        {
            $this->created_at = new CDbExpression('NOW()');
            $this->updated_at = new CDbExpression('NOW()');
            $this->date_upgrade = new CDbExpression('NOW()');
            $this->limit_record = '10';

            if (empty($this->youtube_username))
            {
                $this->is_youtube_default = '1';
            }
            else
            {
                $this->is_youtube_default = '0';
            }
        }
        else
        {
            $this->updated_at = new CDbExpression('NOW()');

            if (empty($this->youtube_username))
            {
                $this->is_youtube_default = '1';
            }
            else
            {
                $this->is_youtube_default = '0';
            }
        }

        /*
        * generate merchant and short_link
        */
        $this->merchant_code = GlobalHelper::HashMe($this->id, PASSWORD_SALT);
        $this->short_link = $this->setShortUrl($this->id);

        return parent::beforeSave();
    }

    public function setShortUrl($id)
    {
        $current_url = Yii::app()->createAbsoluteUrl('merchant/view', array('code' => GlobalHelper::HashMe($id, PASSWORD_SALT)));
        $current_url = str_replace('/api/', '/' , $current_url);
        $current_url = str_replace('https', 'http', $current_url);
        $short_link = file_get_contents('http://tinyurl.com/api-create.php?url=' . urlencode($current_url));

        return $short_link;
    }


	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
            'contents'=>array(self::HAS_MANY, 'Content', 'user_id'),
            'business_category' => array(self::HAS_MANY, 'BusinessCategory', 'business_category_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'                    => 'ID',
			'email'                 => 'Email',
			'password'              => 'Password',
            'confirm_password'      => 'Confirm Password',
			'first_name'            => 'First Name',
			'last_name'             => 'Last Name',
			'auth_token'            => 'Auth Token',
			'confirmation_token'    => 'Confirmation Token',
			'updated_at'            => 'Updated At',
			'created_at'            => 'Created At',
			'activated'             => 'Activated',
            'phone_id'              => 'phone_id',
            'merchant_name'         => 'Merchant Name',
            'merchant_code'         => 'Merchant Code',
            'address'               => 'Address',
            'city'                  => 'City',
            'state'                 => 'State',
            'postal_code'           => 'Postal Code',
            'country'               => 'Country',
            'phone'                 => 'Phone',
            'website'               => 'Website URL',
            'facebook'              => 'Facebook URL',
            'twitter'               => 'Twitter URL',
            'google'                => 'Google+ URL',
            'linkedin'              => 'Linkedin URL',
            'youtube_username'      => 'Username',
            'youtube_password'      => 'Password',
            'business_category_id'  => 'Business Category',
            'limit_record'          => 'Limit record',
            'description'           => 'Descr',
            'id_itunes'             => 'id_itunes',
            'date_upgrade'          => 'Date Upgrade',
            'date_final'            => 'Date Final',
		);
	}


    /**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('first_name',$this->first_name,true);
		$criteria->compare('last_name',$this->last_name,true);
		$criteria->compare('auth_token',$this->auth_token,true);
		$criteria->compare('confirmation_token',$this->confirmation_token,true);
		$criteria->compare('updated_at',$this->updated_at);
		$criteria->compare('created_at',$this->created_at);
		$criteria->compare('activated',$this->activated);
        $criteria->compare('phone_id',$this->phone_id);
        $criteria->compare('merchant_name', $this->merchant_name);
        $criteria->compare('merchant_code', $this->merchant_code);
        $criteria->compare('address', $this->address);
        $criteria->compare('city', $this->city);
        $criteria->compare('state', $this->state);
        $criteria->compare('postal_code', $this->postal_code);
        $criteria->compare('country', $this->country);
        $criteria->compare('phone', $this->phone);
        $criteria->compare('website', $this->website);
        $criteria->compare('facebook', $this->facebook);
        $criteria->compare('twitter', $this->twitter);
        $criteria->compare('google', $this->google);
        $criteria->compare('linkedin', $this->linkedin);
        $criteria->compare('youtube_username', $this->youtube_username);
        $criteria->compare('youtube_password', $this->youtube_password);
        $criteria->compare('business_category_id', $this->business_category_id);
        $criteria->compare('limit_record', $this->limit_record, true);
        $criteria->compare('id_itunes', $this->id_itunes, true);
        $criteria->compare('description', $this->description, true);
        $criteria->compare('date_upgrade', $this->date_upgrade, true);
        $criteria->compare('date_final', $this->date_final, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}


	/* VocalRef methods */
	public function register()
	{
		$this->proccess_attributes();

		// validate new User model, and if everithing ok save it and return User
		if ($this->validate())
        {
            $this->save();

            if (ENABLE_CONFIRMATION)
            {
                MailWrapper::Confirmation($this);
            }

            return true;

		} else {
            return false;
        }
	}
	
	private function proccess_attributes()
	{
		$this->password = GlobalHelper::HashMe($this->password, PASSWORD_SALT);
        $this->auth_token = GlobalHelper::HashMe($this->email, AUTH_SALT);
		$this->activated = ENABLE_CONFIRMATION ? 0 : 1;
		$this->confirmation_token = GlobalHelper::HashMe($this->email . time(), CONFIRMATION_SALT);
		// $this->created_at = time();
	}

    public function valid_youtube_password()
    {
        if (!empty($this->youtube_username))
        {
            if (empty($this->youtube_password))
            {
                $error_messages_web = ErrorProcessor::getErrors('WEB', get_class($this));
                $this->addError('youtube_password', $error_messages_web[ErrorProcessor::VALID_YOUTUBE_PASSWORD]);
            }
        }
    }

    public function valid_confirm_password($attribute, $params)
    {
        $error_messages_web = ErrorProcessor::getErrors('WEB', get_class($this));

        if (!empty($this->confirm_password)) {
            if ($this->password != $this->confirm_password) {
                $this->addError('confirm_password', $error_messages_web[ErrorProcessor::VALID_CONFIRM_PASSWORD]);
            }
        }
    }

    public function valid_url($attribute, $params)
    {
        if (!empty($this->$attribute))
        {
            $pattern = '|http(s)?://[^\s/$.?#].[^\s]*|';
            preg_match($pattern, $this->$attribute, $matches);
            if (empty($matches))
            {
                $error_messages_web = ErrorProcessor::getErrors('WEB', get_class($this));
                $this->addError('valid_url', $error_messages_web[ErrorProcessor::VALID_URL_SOCIAL]);
            }
        }
    }

    /*
    public function valid_merchant_name($attribute, $params)
    {
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        if (!empty($this->merchant_name)) {
            $find_user = User::model()->find('merchant_name=:name', array(':name' => $this->merchant_name));
            if (isset($find_user)) {
                if ($find_user->id != $this->id)
                    $this->addError('merchant_name', $error_messages[ErrorProcessor::SIGNUP_MERCHANT_NAME_ISSET]);
            }
        }
    }
    */


    public function password_unique_length($attribute, $params)
    {
        $error_messages = ErrorProcessor::getErrors("API", get_class($this));

        if (!empty($this->password)) {
            $length = strlen($this->password);
            if (($length < 6) or ($length > 50)) {
                $this->addError('password', $error_messages[ErrorProcessor::VALID_PASSWORD_LENGTH]);
            }
        }
    }

    /**
     * Checks if the given password is correct.
     * @param string the password to be validated
     * @return boolean whether the password is valid
     */
    public function validatePassword($password)
    {
        return GlobalHelper::HashMe($password, PASSWORD_SALT)===$this->password;
    }

    public function Confirm($token)
    {
        $user = User::model()->find("confirmation_token=:token", array("token" => $token));

        $errors = ErrorProcessor::getErrors("WEB", get_class($this));

        if (!$user)
        {
            $this->addError('confirmation_token', $errors[ErrorProcessor::USER_CONFIRMATION_TOKEN_NOT_VALID]);
            return false;
        }

        if ($user->activated == true)
        {
            $this->addError('confirmation_token', $errors[ErrorProcessor::USER_ALREADY_ACTIVATED]);
            return false;
        }

        $user->activated = true;

        if ($user->update())
        {
            MailWrapper::Confirmed($user);

            return true;
        }

        return false;
    }

    public function reconfirm($email)
    {
        $user = $this->getUserByEmail($email);
        if ($user)  {
            // regenerate confirmation token
            $user->confirmation_token = GlobalHelper::HashMe($this->email . time(), CONFIRMATION_SALT);
            $user->update();

            MailWrapper::Confirmation($user);
        }
    }

    public function generatePassword($length = 8)
    {
        $password = "";
        $possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";

        $maxlength = strlen($possible);

        if ($length > $maxlength) {
            $length = $maxlength;
        }
        $i = 0;
        while ($i < $length) {
            $char = substr($possible, mt_rand(0, $maxlength-1), 1);

            if (!strstr($password, $char)) {
                $password .= $char;
                $i++;
            }
        }

        return $password;
    }

    public function forgotpassword($email)
    {
        $errors = ErrorProcessor::getErrors("WEB", get_class($this));
        $user = $this->getUserByEmail($email);

        if ($user)
        {
            $new_password = $this->generatePassword(8);
            $user->password = GlobalHelper::HashMe($new_password, PASSWORD_SALT);
            $user->update();

            MailWrapper::Forgotpassword($user, $new_password);

            return true;
        }
        else
        {
            $this->addError('confirmation_token', $errors[ErrorProcessor::USER_ALREADY_ACTIVATED]);
            return false;
        }
    }

    private function getUserByEmail($email)
    {
        return User::model()->find('email=:email', array(':email' => $email));
    }


}