<?php

class UserActiveController extends RController
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
    public $layout='//layouts/main';


    public function filters()
    {
        return array(
            'rights',
        );
    }


	/**
	 * Displays a particular model.
	 * @param integer $id the ID of the model to be displayed
	 */
	public function actionView($id)
	{
		$this->render('view',array(
			'model'=>$this->loadModel($id),
		));
	}

	/**
	 * Creates a new model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 */
	public function actionCreate()
	{
		$model=new UserActive;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['UserActive']))
		{
			$model->attributes=$_POST['UserActive'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}

	/**
	 * Updates a particular model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id the ID of the model to be updated
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['UserActive']))
		{
			$model->attributes=$_POST['UserActive'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->id));
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}

	/**
	 * Deletes a particular model.
	 * If deletion is successful, the browser will be redirected to the 'admin' page.
	 * @param integer $id the ID of the model to be deleted
	 */
	public function actionDelete($id)
	{
		$this->loadModel($id)->delete();

		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
	}


    /**
	 * Lists all models.
	 */
	public function actionIndex()
	{
        $paid_dividends = 0;
        $common_count_pay = 0;

        $countUserActive = UserActive::model()->count('user_id = :userID AND count_pay > 0', array(':userID' => Yii::app()->user->id));

        // Общее кол-во паев
        $criteria = new CDbCriteria;
        $criteria->select = 'SUM(count_pay) as count_pay';
        $criteria->condition = 'user_id = :userID';
        $criteria->params = array(':userID' => Yii::app()->user->id);
        $modelUserActive = UserActive::model()->find($criteria);

        // Общее количество паев
        if (isset($modelUserActive->count_pay) and !empty($modelUserActive->count_pay))
            $common_count_pay = $modelUserActive->count_pay;

        $sql = '
            SELECT ua.*, r.`create_at`, r.price, p.name, p.count_pay AS projects_count_pay
            FROM {{user_active}} AS ua
            INNER JOIN
            (
            SELECT fd.*
                FROM {{feed_transaction}} AS fd
                ORDER BY fd.`create_at` DESC
            ) AS r
            ON ua.`project_id` = r.`project_id`

            INNER JOIN {{projects}} AS p
            ON p.id = ua.`project_id`

            AND ua.`user_id` = '. Yii::app()->user->id
            .' GROUP BY ua.`project_id`
        ';
        // AND ua.`user_id` = r.`user_buyer_id`
        $connection = Yii::app()->db;
        $data = $connection->createCommand($sql)->queryAll();

        $percent_bag                = 0;
        $common_total_pay_active    = 0;
        if ($data)
        {
            foreach ($data as $key => $value)
            {
                // Текущая стоимость актива = количество паев * цену одного пая.
                // Цена равняется цене последней сделки по этому активу
                // не учитывается тип сделки (покупка или продажа)
                $current_pay_active = $value['count_pay'] * $value['price'];

                // суммируем все текущие стоимости всех активов
                $common_total_pay_active += $current_pay_active;

                // Текущая стоимость актива
                $data[$key]['current_pay_active'] = $current_pay_active;
            }

            foreach ($data as $key => $value)
            {
                // Доля в портфеле = Текущая стоимость актива / сумму текущей стоимости всех активов * 100%
                if ($common_total_pay_active > 0)
                    $percent_bag = ($value['current_pay_active'] / $common_total_pay_active) * 100;
                else
                    $percent_bag = 0;

                // Доля на рынке – доля паев (в %),
                // которой владеет данный инвестор в данном активе (количество паев / общее количество паев * 100%
                if ($value['projects_count_pay'] > 0)
                    $percent_market = ($value['count_pay'] / $value['projects_count_pay']) * 100;
                else
                    $percent_market = 0;

                // Доля в портфеле (%)
                $data[$key]['percent_bag'] = round($percent_bag, 2);
                // Доля на рынке (%)
                $data[$key]['percent_market'] = round($percent_market,2);

                // Выплачено дивидендов
                $criteria = new CDbCriteria;
                $criteria->select = 'SUM(`sum`) as `sum`';
                $criteria->condition = 'user_id = :userID AND project_id = :projectID AND operation = :operation';
                $criteria->params = array(
                    ':userID' => Yii::app()->user->id,
                    ':projectID' => $value['project_id'],
                    ':operation' => BalansHistory::OPERATION_DIVIDENDS
                );
                $modelBalansHistory = BalansHistory::model()->find($criteria);

                if (isset($modelBalansHistory->sum) and !empty($modelBalansHistory->sum))
                    $data[$key]['dividends'] = $modelBalansHistory->sum;
                else
                    $data[$key]['dividends'] = 0;

                // Доходность % покупки
                // Доходность = выплачено дивидендов / сумма покупки * 100%
                if ($data[$key]['dividends'] > 0)
                {
                    if ($data[$key]['current_pay_active'] > 0 )
                    {
                        if ($data[$key]['total_pay'] > 0)
                        {
                            $income = ($data[$key]['dividends'] / $data[$key]['total_pay']) * 100;
                            $data[$key]['income'] = round($income, 2);
                        }
                        else
                            $data[$key]['income'] = 0;
                    }
                    else
                        $data[$key]['income'] = 0;
                }
                else
                    $data[$key]['income'] = 0;
            }
        }

        // Данные для кругового графика
        $common_chart_array = array();
        foreach ($data as $value)
        {
            if ($value['percent_bag'])
            {
                $arr = array($value['name'], $value['percent_bag']);
                array_push($common_chart_array ,$arr);
            }
        }
        $chart = CJSON::encode($common_chart_array);


        // таблица "Мои сделки"
        list($controller) = Yii::app()->createController('feedtransaction');
        $table_feed_transaction = null;
        $table_feed_transaction = $controller->TableUserFeedTransaction_Common();

        // Выплачено дивидендов за все время
        $criteria = new CDbCriteria;
        $criteria->select = 'SUM(`sum`) as summary';
        $criteria->condition = 'user_id = :userID AND operation = :operation';
        $criteria->params = array(':userID' => Yii::app()->user->id, ':operation' => BalansHistory::OPERATION_DIVIDENDS);
        $modelBalansHistory = BalansHistory::model()->find($criteria);
        if (isset($modelBalansHistory->summary) and !empty($modelBalansHistory->summary))
            $paid_dividends = $modelBalansHistory->summary;


        // Данные для вкладки "Дивиденды"
        $sql = '
            SELECT bh.*, p.`name`
            FROM {{balans_history}} AS bh
            LEFT JOIN {{projects}} AS p
            ON bh.project_id = p.id
            WHERE bh.`user_id` = ' . Yii::app()->user->id .
            ' AND bh.`operation` = ' . BalansHistory::OPERATION_DIVIDENDS
        ;
        $connection = Yii::app()->db;
        $dividends = $connection->createCommand($sql)->queryAll();

        $this->render('index', array(
            'have_pay_on_projects'      => $countUserActive,
            'count_pay_user'            => $modelUserActive->count_pay, // Кол-во паев у пользователя
            'data'                      => $data, // главная таблица
            'common_count_pay'          => $common_count_pay,
            'common_total_pay_active'   => $common_total_pay_active,
            'chart'                     => $chart,
            'table_feed_transaction'    => $table_feed_transaction, // "Мои сделки"
            'paid_dividends'            => $paid_dividends,
            'dividends'                 => $dividends
        ));

	}


	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new UserActive('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['UserActive']))
			$model->attributes=$_GET['UserActive'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return UserActive the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=UserActive::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param UserActive $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='user-active-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}


    /**
     * Обновляем кол-во паев, которыми владеет пользователь
     *
     * @param $user_id
     * @param $project_id
     * @param $count_pay - Кол-во паев
     * @param $total_pay - Сумма актива
     * @param $minus - если true, то отнимаем
     */
    public function actionUpdateUserActive($user_id, $project_id, $count_pay, $total_pay, $minus)
    {
        if (
            (isset($user_id) and !empty($user_id))
            and (isset($project_id) and !empty($project_id))
        )
        {
            // если уменьшаем
            if ($minus == true)
            {
                $count_pay = -1 * $count_pay;
                $total_pay = -1 * $total_pay;
            }

            $exists = UserActive::model()->exists(
                'user_id = :userID AND project_id = :projectID',
                array(':userID' => (int)$user_id, ':projectID' => (int)$project_id)
            );

            if ($exists)
            {
               $modelUserActive = UserActive::model()->find(
                   'user_id = :userID AND project_id = :projectID',
                   array(':userID' => (int)$user_id, ':projectID' => (int)$project_id)
               );

                if (isset($modelUserActive->count_pay) and !empty($modelUserActive->count_pay))
                {
                    // если у пользователя покупают последние паи
                    // то удаляем запись из активов
                    $pre = (int)$modelUserActive->count_pay + $count_pay;
                    if ($pre == 0)
                    {
                        $modelUserActive->delete();

                        $project_name = C_Projects::getProjectName($project_id);
                        C_Log::addLog(Log::TYPE_INFO, $user_id, 'У пользователя купили последние паи в проекте ' .$project_name);

                        // отправка сообщения на почту
                        $data = array(
                            'title' => 'Покупка паев',
                            'text' => 'У вас купили последние паи проекта ' . $project_name
                        );
                        C_Mail::sendMailUserByID('simple', Yii::app()->name . ' - покупка', $user_id, $data, null);
                    }
                    else
                    {
                        $int = UserActive::model()->updateCounters(
                            array(
                                'count_pay' => (int)$count_pay,
                                'total_pay' => (float)$total_pay
                            ),
                            'user_id = :userID AND project_id = :projectID',
                            array(':userID' => (int)$user_id, ':projectID' => (int)$project_id)
                        );
                        if ($int)
                        {
                            return true;
                        }
                        else
                        {
                            echo CJSON::encode(array(
                                'status' => 300,
                                'message' => 'Ошибка обновления данные об активах пользователя'
                            ));
                            Yii::app()->end();
                        }
                    }
                }
            }
            else
            {
                $model = new UserActive;
                $model->user_id     = (int)$user_id;
                $model->project_id  = (int)$project_id;
                $model->count_pay   = (int)$count_pay;
                $model->total_pay   = (float)$total_pay;

                if ($model->save())
                {
                    return true;
                }
                else
                {
                    echo CJSON::encode(array(
                        'status' => 300,
                        'message' => 'Ошибка сохранения данные об активах пользователя'
                    ));
                    Yii::app()->end();
                }
            }
        }
    }


    /**
     * есть ли у данного пользователя этот актив для продажи
     */
    public function actionAjaxCheckUserActive()
    {
        if (Yii::app()->request->isAjaxRequest)
        {
            $model = UserActive::model()->find('user_id = :userID AND project_id = :projectID', array(':userID' => Yii::app()->user->id, ':projectID' => (int)$_POST['project_id']));
            if ($model)
            {
                if ($model->count_pay > 0)
                {
                    echo CJSON::encode(array(
                        'status' => 200,
                        'data' => (int)$model->count_pay
                    ));
                    Yii::app()->end();
                }
                else
                {
                    echo CJSON::encode(array(
                        'status' => 201,
                        'message' => 'Вы не можете продать покупателю паи, которых у вас нет.'
                    ));
                    Yii::app()->end();
                }
            }
            else
            {
                echo CJSON::encode(array(
                    'status' => 300,
                    'message' => 'У данного пользователя нет актива для продажи.'
                ));
                Yii::app()->end();
            }
        }
    }
}
