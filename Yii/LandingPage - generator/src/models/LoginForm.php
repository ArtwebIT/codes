<?php

/**
 * LoginForm class.
 * LoginForm is the data structure for keeping
 * user login form data. It is used by the 'login' action of 'SiteController'.
 */
class LoginForm extends CFormModel
{
	public $email;
	public $password;
	public $rememberMe;
    public $auth_token;
    public $username;

    public $error_code;

	private $_identity;

	/**
	 * Declares the validation rules.
	 * The rules state that username and password are required,
	 * and password needs to be authenticated.
	 */
	public function rules()
	{
        $errors_web = ErrorProcessor::getErrors("WEB", "LoginForm");
        $errors_api = ErrorProcessor::getErrors("API", "LoginForm");

		return array(
            array('email', 'required', 'on' => 'web', 'message' => $errors_web[ErrorProcessor::LOGIN_EMAIL_EMPTY]),
            array('username', 'required', 'on'=> 'admin', 'message' => $errors_web[ErrorProcessor::LOGIN_USERNAME_EMPTY]),
            array('password', 'required', 'on'=>array('web','admin'), 'message' => $errors_web[ErrorProcessor::LOGIN_PASSWORD_EMPTY]),
			array('rememberMe', 'boolean', 'on'=>'web'),
			array('password', 'authenticate_web', 'on'=>'web', 'message' => $errors_web[ErrorProcessor::LOGIN_USERNAME_OR_PASSWORD_WRONG]),
            array('password', 'authenticate_admin', 'on'=>'admin', 'message' => $errors_web[ErrorProcessor::LOGIN_USERNAME_OR_PASSWORD_WRONG]),
            array('auth_token', 'required', 'on'=>'api', 'message' =>  $errors_api[ErrorProcessor::AUTH_TOKEN_REQUIRED]),
		);
	}

    public function valid_email($attribute, $params)
    {
        if (empty($this->email))
        {
            $errors_web = ErrorProcessor::getErrors("WEB", get_class($this));
            $this->addError('email', $errors_web[ErrorProcessor::LOGIN_EMAIL_EMPTY]);
        }
    }

	/**
	 * Declares attribute labels.
	 */
	public function attributeLabels()
	{
		return array(
			'rememberMe'=>'Remember me next time',
		);
	}

	/**
	 * Authenticates the password.
	 * This is the 'authenticate' validator as declared in rules().
	 */
	public function authenticate_web($attribute,$params)
	{
        if(!$this->hasErrors())
		{

            $this->_identity=new UserIdentity($this->email, $this->password);

			if(!$this->_identity->authenticate())
            {
                $errors = ErrorProcessor::getErrors("WEB", get_class($this));

                $this->addError('password',$errors[ErrorProcessor::LOGIN_USERNAME_OR_PASSWORD_WRONG]);
            }
		}
	}

    /**
     * Authenticates the password.
     * This is the 'authenticate' validator as declared in rules().
     */
    public function authenticate_admin($attribute,$params)
    {
        if(!$this->hasErrors())
        {

            $this->_identity=new UserIdentity($this->username, $this->password, "admin");

            if(!$this->_identity->authenticate())
            {
                $errors = ErrorProcessor::getErrors("WEB", get_class($this));

                $this->addError('password',$errors[ErrorProcessor::LOGIN_USERNAME_OR_PASSWORD_WRONG]);
            }
        }
    }

	/**
	 * Logs in the user using the given username and password in the model.
	 * @return boolean whether login is successful
	 */
	public function login()
	{
        if($this->_identity===null)
        {
            # API login
            $this->_identity=new UserIdentity($this->email, $this->password);

            $this->_identity->authenticate();
        }

        if($this->_identity->errorCode===UserIdentity::ERROR_NONE)
        {
            $duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
            Yii::app()->user->login($this->_identity,$duration);
            return true;
        }
        else
        {
            return false;
        }
	}

    /*
     *
     * ADmin part
     *
     */
    public function admin_login()
    {
        # Admin login
        $this->_identity=new UserIdentity($this->username, $this->password, "admin");
        $this->_identity->authenticate();

        if($this->_identity->errorCode===UserIdentity::ERROR_NONE)
        {
            $duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
            Yii::app()->user->login($this->_identity,$duration);
            return true;
        }
        else
        {
            return false;
        }
    }


    /*
     *
     * API part
     *
     */
    public function api_login($_email, $_password)
    {
        if($this->_identity===null)
        {
            # API login
            if (!empty($_email) and !empty($_password))
            {
                $this->_identity=new UserIdentity($_email, $_password);
            }
            else
            {
                $this->_identity=new UserIdentity($this->email, $this->password);
            }


            $this->_identity->authenticate();
        }

        if($this->_identity->errorCode===UserIdentity::ERROR_NONE)
        {
            Auth::login($this->_identity);
            return true;
        }
        else
        {
            return false;
        }
    }

    public function auth_by_token()
    {
        if($this->_identity===null)
        {
            # API login
            $this->_identity=new ApiUserIdentity($this->auth_token);

            $this->_identity->authenticate();
        }

        if($this->_identity->errorCode===ApiUserIdentity::ERROR_NONE)
        {
            Auth::login($this->_identity);
            return true;
        }
        else
        {
            $errors = ErrorProcessor::getErrors("API", "LoginForm");
            $this->addError('auth_error',$errors[ErrorProcessor::AUTH_ERROR]);

            return false;
        }
    }
}