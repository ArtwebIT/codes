<?php

class HomeController extends FrontEndController
{
    private $error_messages;

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
            //'https +login, signup, profile, index',
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow',  // allow all users to access 'index' and 'view' actions.
                'actions'=>array('index','page', 'contact', 'signup', 'login', 'logout', 'captcha', 'error', 'confirmation', 'reconfirm', 'view', 'forgotpassword', 'avatar', 'analytics'),
                'users'=>array('*'),
            ),
            array('allow', // allow authenticated users to access all actions
                'actions'=>array('profile'),
               // 'roles'=>array('admin'),
                'users'=>array('@'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    /**
	 * Declares class-based actions.
	 */
	public function actions()
	{
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
        if (Yii::app()->user->isGuest)
        {
            $this->redirect(array('login'));
        }
        else
        {
            // get authorized user info
            $user = User::model()->findByPk(Yii::app()->user->id);

            $favorite = Content::model()->findAll('isFavorite=:isFavorite AND user_id=:user_id', array(':isFavorite' => 1, ':user_id' => Yii::app()->user->id));

			if (!empty($_POST['pages']) and ($_POST['pages'] != null))
            {
				$_SESSION['pages'] = $_POST['pages'];
				header("Location: http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
				exit;
            }
            else
            {
				if (empty($_SESSION['pages']))
				{
					$_SESSION['pages'] = 6;
				}
            }

            $criteria = new CDbCriteria(array(
                'condition' => 'user_id=:user_id',
                'params'    => array(
                    ':user_id' => Yii::app()->user->id
                )
            ));

            if ($_SESSION['pages'] == 'all')
            {
                $testimonials = Content::model()->recently()->findAll($criteria);
                $this->render('index', array(
                    'favorite'      => $favorite,
                    'testimonials'  => $testimonials,
                    'user'          => $user
                ));
            }
            else
            {
                $count = Content::model()->count($criteria);
                $pages = new CPagination($count);
                $pages->pageSize = $_SESSION['pages'];
                $pages->applyLimit($criteria);
                $testimonials = Content::model()->recently()->findAll($criteria);

                $this->render('index', array(
                    'favorite'      => $favorite,
                    'testimonials'  => $testimonials,
                    'pages'         => $pages,
                    'user'          => $user
                ));
            }

        }
	}

    public function actionView($id)
    {
        $model = Content::model()->findByPk($id);
        $this->render('view', array(
            'content'   => $model
        ));
    }

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
	    if($error=Yii::app()->errorHandler->error)
	    {
	    	if(Yii::app()->request->isAjaxRequest)
	    		echo $error['message'];
	    	else
	        	$this->render('error', $error);
	    }
	}

	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				MailWrapper::sendMailTemplate("contactus", $model, $model->subject, "tihonau@gmail.com");

				Yii::app()->user->setFlash('contact','Thank you for contacting us. We will respond to you as soon as possible.');
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}


    public function actionProfile()
    {
        $model = $this->loadModel(Yii::app()->user->id);

        if (empty($model->short_link)) {
            $current_url = Yii::app()->createAbsoluteUrl('merchant/view', array('code' => GlobalHelper::HashMe($model->id, PASSWORD_SALT)));
            $current_url = str_replace('https', 'http', $current_url);
            $model->short_link = $this->Encode_ShortUrl($current_url);
            $model->merchant_code = GlobalHelper::HashMe($model->id, PASSWORD_SALT);
            $model->update();
        }

        $user_password = $model->password;

        if( isset($_POST['User']) ) {

            $model->attributes = $_POST['User'];

            if( $model->validate() )
            {
                if (empty($_POST['User']['password']) ) {
                    $model->password = $user_password;
                } else {
                    if ($user_password != $model->password) {
                        $model->password = GlobalHelper::HashMe($_POST['User']['password'], PASSWORD_SALT);
                    }
                }

                if (!empty($_POST['User']['youtube_password']))
                {
                    $model->youtube_password = GlobalHelper::HashMe($_POST['User']['youtube_password'], PASSWORD_SALT);
                }

                if ($model->update()) {
                    $this->redirect(array('profile'));
                }
            }
        }

        $model_user['user'] = $model;

        $category = BusinessCategory::model()->findAll(array('order' => 'name'));
        $model_user['category'] = CHtml::listData($category, 'id', 'name');



        $this->render('profile', array(
            'model' => $model_user
        ));
    }

    public function actionAvatar()
    {
        $model = $this->loadModel(Yii::app()->user->id);

        if (isset($_GET['flag'])) {
            $flag = $_GET['flag'];
            if ($flag == 'ajax') {
                if (isset($_GET['name'])) {
                    $model->avatar = $model->id . '_' . $_GET['name'];
                    $model->save();
                }
            }
        } else {
            $model->avatar = null;
            $model->save();
            $this->redirect(array('profile'));
        }
    }

    public function actionAnalytics()
    {
        $this->render('analytics');
    }

    public function loadModel($id)
    {
        $model = User::model()->findByPk($id);
        if($model === null)
            throw new CHttpException(404, 'The requested page does not exist.');
        return $model;
    }


    public function actionSignup()
    {
        $model = new SignUpForm('register');

		// uncomment the following code to enable ajax-based validation
		if (isset($_POST['ajax']) && $_POST['ajax'] === 'user-signup-form')
		{
            echo CActiveForm::validate($model);
			Yii::app()->end();
		}

		if (isset($_POST['SignUpForm']))
        {
            $model->attributes = $_POST['SignUpForm'];

            if ($model->validate())
            {
                $user = new User;
                $user->setAttributes($model->attributes);

                # $user->account_type = ACCOUNT_TYPE_WEB;
                if ($user->register())
                {
                    // form inputs are valid, do something here
                    $this->render('signup', array('signup' => 'ok'));
                    return;
                }
			}
		}

        $model_user['user'] = $model;

        $category = BusinessCategory::model()->findAll(array('order' => 'name'));
        $model_user['category'] = CHtml::listData($category, 'id', 'name');

		$this->render('signup', array(
            'model'     => $model_user,
            'signup'    => null
        ));
	}



    public function actionConfirmation()
    {
        $token = Yii::app()->request->getQuery("token");

        if (!$token)
        {
            GlobalHelper::return404();
        }

        $model = new User();

        $ok = $model->Confirm($token);

        $this->render('confirmation', array('confirmation'=>$ok));
    }

    public function actionReconfirm()
    {
        $model=new ConfirmForm();
        $sent = "";

        // collect user input data
        if(isset($_POST['ConfirmForm']))
        {
            $model->attributes=$_POST['ConfirmForm'];

            // validate user input and redirect to the previous page if valid
            if($model->validate())
            {
                $user = new User();
                $user->reconfirm($model->email);

                // display the login form
                $sent = "ok";
            }
        }

        // display the login form
        $this->render('reconfirm',array('model'=>$model, 'sent' => $sent));
    }

    public function actionForgotpassword()
    {
        $model = new LoginForm();
        $sent = '';
        if (isset($_POST['LoginForm'])) {
            if ($model->validate()) {
                $user = new User();

                $user->forgotpassword($_POST['LoginForm']['email']);

                $sent = 'ok';
            }
        }

        $this->render('forgotpassword', array(
            'model' => $model,
            'sent'  => $sent
        ));
    }


    /**
     * Displays the login page
     */
    public function actionLogin()
    {
        $model=new LoginForm('web');

        // if it is ajax validation request
        if(isset($_POST['ajax']) && $_POST['ajax']==='login-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        // collect user input data
        if(isset($_POST['LoginForm']))
        {
            $model->attributes=$_POST['LoginForm'];

            // validate user input and redirect to the previous page if valid
            if($model->validate() && $model->login())
            {
               $this->redirect(array('index'));
            }
        }

        // display the login form
        $this->render('login',array('model'=>$model));
    }

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}

    public function Encode_ShortUrl($url)
    {
        $short_url = file_get_contents('http://tinyurl.com/api-create.php?url=' . urlencode($url));
        return $short_url;
    }
}