<?php

class SiteController extends Controller
{
    public $layout = 'main';
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
        // Блок новостей
        $news = News::model()->recently()->findAll();

        // Форма подписки
        $subscribers = new Subscribers();

        if (isset($_POST['ajax']) && $_POST['ajax'] === 'subscribers-form_subscribers-form')
        {
            $errors = CActiveForm::validate($subscribers);
            // ajax error form
            if ($errors != '[]')
            {
                echo $errors;
                Yii::app()->end();
            }
            else
            {
                if (isset($_POST['Subscribers']))
                {
                    $subscribers->attributes = $_POST['Subscribers'];
                    if ($subscribers->validate()) {
                        $subscribers->save();

                        echo CJSON::encode(array(
                            'subscribered' => true
                        ));
                        Yii::app()->end();
                        //$this->refresh();
                    }
                }
            }
        }

        //---------- Таблица проектов -----------------

        //$model = Projects::model()->with('feedTransaction')->findAll();
        // OR DAO
        $sql = '
            SELECT p.*, r.create_at, r.price
            FROM {{projects}} AS p
            LEFT JOIN
            (
            SELECT fd.*
            FROM {{feed_transaction}} AS fd
            ORDER BY fd.`create_at` DESC
            ) AS r
            ON r.project_id = p.`id`
            GROUP BY p.`id`
        ';
        $connection = Yii::app()->db;
        $model = $connection->createCommand($sql)->queryAll();

        // добавляем к проекту кол-во всех свободных паев, доступных на продажу
        foreach ($model as $key => $item)
        {
            if (is_numeric($item['type']))
            {
                switch ($item['type'])
                {
                    case Projects::TYPE_1 : // Под нашим управлением

                        // получаем все заявки на продажу и считаем кол-во паев доступных на продажу
                        $modelBid = Bid::model()->sale()->findAll('project_id = :projectID', array(':projectID' => $item['id']));
                        $project_sales_pay = 0;
                        if ($modelBid)
                        {
                            // суммируем паи
                            foreach ($modelBid as $value) {
                                $project_sales_pay += $value->count;
                            }
                        }

                        $model[$key]['sales_pay'] = $project_sales_pay;
                        break;

                    case Projects::TYPE_2: // Возможно приобретение

                        $modelBid = Bid::model()->buy()->findAll('project_id = :projectID', array(':projectID' => $item['id']));
                        $project_buy_pay = 0;
                        if ($modelBid)
                        {
                            // суммируем паи
                            foreach ($modelBid as $value) {
                                $project_buy_pay += $value->count;
                            }
                        }
                        $model[$key]['sales_pay'] = ((int)$item['count_pay'] - (int)$project_buy_pay);
                        break;
                }
            } else {
                throw new CHttpException(400, 'Ошибка типа проекта ' . $item['id']);
            }
        }

        $common_allow_buy_pay = 0; // Количество паев в продаже
        $common_cost_all_pay = 0; // Стоимость всех паев
        foreach ($model as $value)
        {
            $common_allow_buy_pay += $value['sales_pay'];

            // Если возможно приобретение
            if ($value['type'] == Projects::TYPE_2)
            {
                if ($value['cost_one_pay'])
                    $common_cost_all_pay += $value['cost_one_pay'] * $value['sales_pay'];
            }

            // Если под нашим управлением
            if ($value['type'] == Projects::TYPE_1)
            {
                if ($value['price'])
                    $common_cost_all_pay += $value['price'] * $value['sales_pay'];
            }
        }

        //------ end Projects ------------------------


        $youtube_video = C_Settings::getSettingValue('youtube_video');
        $promo = C_Settings::getSettingValue('promo');
        $advantage = C_Settings::getSettingValue('advantage');

		$this->render('index', array(
            'news'              => $news,
            'subscribers'       => $subscribers,
            'youtube_video'     => $youtube_video,
            'promo'             => $promo,
            'advantage'         => $advantage,
            'projects'          => $model,
        ));
	}


	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if ($error = Yii::app()->errorHandler->error)
		{
            if (Yii::app()->request->isAjaxRequest)
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
		$model = new ContactForm;

		if (isset($_POST['ContactForm']))
		{
			$model->attributes = $_POST['ContactForm'];
			if ($model->validate())
			{
                $data = array(
                    'title' => 'Контактная форма',
                    'name' => $model->name,
                    'email' => $model->email,
                    'message' => $model->body
                );
                $mail = C_Mail::sendMailUser('contact', $model->subject, C_Settings::getSettingValue('contactEmail'), $data, ' - Контакт');
                //send
                if ($mail) {
                    Yii::app()->user->setFlash('contact', 'Спасибо. Ваше сообщение отправлено.');
                } else {
                    Yii::app()->user->setFlash('error', 'Не возможно отправить форму.');
                }

				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}


	/**
	 * Displays the login page
	 */
	public function actionLogin()
	{
		$model = new LoginForm;

		// if it is ajax validation request
		if (isset($_POST['ajax']) && $_POST['ajax'] === 'login-form-popup')
		{
            $errors = CActiveForm::validate($model);
            // ajax error form
            if ($errors != '[]')
            {
                echo $errors;
                Yii::app()->end();
            }
            else
            {
                // collect user input data
                if (isset($_POST['LoginForm']))
                {
                    $model->attributes = $_POST['LoginForm'];
                    // validate user input and redirect to the previous page if valid
                    if ($model->validate() && $model->login())
                    {
                        $this->lastViset();
                        $url = $this->createUrl('/cabinet');
                        echo CJSON::encode(array(
                            'authenticated' => true,
                            'redirectUrl' => $url,
                        ));
                        Yii::app()->end();
                    }

                }
            }
		}
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		//$this->redirect(Yii::app()->homeUrl);
        $this->redirect(Yii::app()->homeUrl);
	}


    private function lastViset()
    {
        $lastVisit = User::model()->notsafe()->findByPk(Yii::app()->user->id);
        $lastVisit->lastvisit_at = date('Y-m-d H:i:s');
        $lastVisit->save();
    }


    public function actionAccess()
    {
        $this->render('access');
    }
}