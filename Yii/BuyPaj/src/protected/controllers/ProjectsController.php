<?php

class ProjectsController extends RController
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
    public $layout='//layouts/main';


	/**
	 * @return array action filters
	 */
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
		$model=new Projects;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Projects']))
		{
			$model->attributes = $_POST['Projects'];

            // Upload images
            $imageUploadFile = CUploadedFile::getInstance($model, 'image');
            if ($imageUploadFile !== null){
                $imageFileName = C_Image::uniqName($imageUploadFile->name);
                $model->image = $imageFileName;
            }

			if ($model->save())
            {
                // save image
                if ($imageUploadFile !== null){
                    C_Image::saveImage($imageUploadFile, Yii::app()->params['uploads_folder'] . '/projects', $imageFileName);
                }


                // Если администратор создает проект "Под нашим управлением"
                // то имитируем покупку и заносим проект в его актив
                if ($model->type == Projects::TYPE_1)
                {
                    $model->sum_buy = $_POST['Projects']['sum_buy'];

                    // записываем кол-во паев в его владение
                    $last_project_id = $model->getPrimaryKey();

                    $modelUserActive = new UserActive;
                    $modelUserActive->user_id = Yii::app()->user->id;
                    $modelUserActive->project_id = $last_project_id;
                    $modelUserActive->count_pay = $model->count_pay;
                    $modelUserActive->total_pay = $model->sum_buy;
                    $modelUserActive->save();

                    $modelBalansHistorys = new BalansHistory;
                    $modelBalansHistorys->operation = BalansHistory::OPERATION_BUY;
                    $modelBalansHistorys->user_id = Yii::app()->user->id;
                    $modelBalansHistorys->project_id = $last_project_id;
                    $modelBalansHistorys->sum = $model->sum_buy;
                    $modelBalansHistorys->notes = 'Собственная покупка и владение';
                    $modelBalansHistorys->save();

                    $modelFeedTransaction = new FeedTransaction;
                    $_price = 0;
                    if ($model->count_pay > 0)
                    {
                        $_price = round($model->sum_buy / $model->count_pay, 2);
                    }
                    $modelFeedTransaction->price = $_price;
                    $modelFeedTransaction->count_pay = $model->count_pay;
                    $modelFeedTransaction->sum_trans = (float)$model->sum_buy;
                    $modelFeedTransaction->project_id = $last_project_id;
                    $modelFeedTransaction->user_buyer_id = Yii::app()->user->id;
                    $modelFeedTransaction->user_seller_id = Yii::app()->user->id;
                    $modelFeedTransaction->type = FeedTransaction::FEED_STATUS_BUY;
                    $modelFeedTransaction->save();
                }


                C_Log::addLog(Log::TYPE_INFO, null, 'Проект создан "' . $model->name . '"');

                $this->redirect(array('view', 'id' => $model->id));
            }

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

		if (isset($_POST['Projects']))
		{
			$model->attributes = $_POST['Projects'];

            // Upload images
            $imageUploadFile = CUploadedFile::getInstance($model, 'image');
            if ($imageUploadFile !== null){
                $imageFileName = C_Image::uniqName($imageUploadFile->name);
                $model->image =  $imageFileName;
            }


            if ($model->save())
            {
                // save image
                if ($imageUploadFile !== null){
                    C_Image::saveImage($imageUploadFile, Yii::app()->params['uploads_folder'] . '/projects', $imageFileName);
                }

                C_Log::addLog(Log::TYPE_INFO, null, 'Проект "' . $id . '" обновлен');

                $this->redirect(array('view', 'id' => $model->id));
            }
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
		/*
        $dataProvider=new CActiveDataProvider('Projects');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
        */
        $model = Projects::model()->findAll();

        $this->render('index', array(
            'projects' => $model
        ));

	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Projects('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Projects']))
			$model->attributes=$_GET['Projects'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Projects the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Projects::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Projects $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='projects-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}


    /**
     * Удалить выбранное
     */
    public function actionDeleteAll()
    {
        $autoIdAll = $_POST['autoId'];
        if (count($autoIdAll) > 0)
        {
            foreach($autoIdAll as $autoId)
            {
                $this->loadModel($autoId)->delete();
                // log
                C_Log::addLog(Log::TYPE_INFO, null, 'Проект "' . $autoId . '" удален');
            }
        }
    }


    /**
     * Удалить Все
     */
    public function actionDeleteFullAll()
    {
        Projects::model()->deleteAll();
    }


    /**
     * сортируем массив по дате
     */
    public function sortArray($a, $b)
    {
        if ($a['date'] == $b['date']){
            return 0;
        }
        if ($a['date'] < $b['date']){
            return -1;
        }
        return 1;
    }


    /**
     * Обзор конкретного проекта
     *
     * @param $id - ID project
     */
    public function actionBrowser($id)
    {
        $list_month = null;
        $list_sum = null;
        $paid_dividends = 0;

        $model = $this->loadModel($id);

        //$income_project = IncomeProject::model()->recently()->findAll('project_id = :ID', array(':ID' => $id));
        $sql = '
            SELECT *
            FROM {{income_project}}
            WHERE project_id = '. $id .
            ' ORDER BY date DESC
            LIMIT 12
            ';
        $connection = Yii::app()->db;
        $income_project = $connection->createCommand($sql)->queryAll();

        // сортируем массив по дате
        uasort($income_project, array('ProjectsController', 'sortArray'));

        $fields = ProjectsField::model()->findAll('project_id = :ID', array(':ID' => $id));
        $modelProject = Projects::model()->findByPk($id);

        $months = array(
            1 => 'Январь',
            2 => 'Февраль',
            3 => 'Март',
            4 => 'Апрель',
            5 => 'Май',
            6 => 'Июнь',
            7 => 'Июль',
            8 => 'Август',
            9 => 'Сентябрь',
            10 => 'Октябрь',
            11 => 'Ноябрь',
            12 => 'Декабрь',
        );

        $data = array();
        $array_month = array();
        $array_year = array();
        $array_sum = array();
        $list_year = '';

        $x = 0;
        foreach($income_project as $income)
        {
            $data[$x]['date'] = $income['date'];
            $data[$x]['sum'] = $income['sum'];
            $x++;
        }
        //asort($data);

        if (!empty($data) and count($data) > 0)
        {
            foreach($data as $key => $item)
            {
                //$mon = explode(' ', $item['date']);
                //$month = date('F', strtotime($item['date']));
                $month = Yii::app()->dateFormatter->format('M', $item['date']);
                $month = $months[$month];

                $year = date('Y', strtotime($item['date']));
                $data[$key]['month'] = $month;

                if ($modelProject)
                    $count_pay = $modelProject->count_pay;
                if (isset($count_pay) and !empty($count_pay))
                    $amount_one_pay = round($item['sum'] / $count_pay, 4);


                array_push($array_year, $year);
                $array_year = array_unique($array_year);
                array_push($array_month, $month);
                array_push($array_sum, $amount_one_pay);

                $list_month = implode(',', $array_month);
                $list_sum = implode(',', $array_sum);
            }
            $list_year = implode('-', $array_year);
        }

        list($controller) = Yii::app()->createController('bid');
        $table_bid = $controller->TableBid();

        // Если проект имеет тип `Возможно приобретение` то поле цена заблокировано для ввода
        $disable_field_price = false;
        $cost_one_pay = null;
        if ($model->type == 1) {
            $disable_field_price = true;
            $cost_one_pay = $model->cost_one_pay;
        }

        // Таблица с отправленными пользователем заявками
        list($controller) = Yii::app()->createController('bid');
        $table_user_bid = null;
        $table_user_bid = $controller->TableUserBid();

        // таблица "Инвесторы"
        $table_investors = null;
        $table_investors = $this->TableInvestors($id);

        // таблица "лента сделок"
        list($controller) = Yii::app()->createController('feedtransaction');
        $table_feed_transaction = null;
        $table_feed_transaction = $controller->TableFeedTransaction($id);

        // таблица "Мои сделки"
        list($controller) = Yii::app()->createController('feedtransaction');
        $table_user_feed_transaction = null;
        $table_user_feed_transaction = $controller->TableUserFeedTransaction($id);

        // данные для графика "Цена пая"
        $modelFeedTransaction = FeedTransaction::model()->asc()->findAll('project_id = :projectID', array(':projectID' => $id));

        // формируем правильные данные для графика "Цена пая"
        $data_chart_pay = array();
        foreach ($modelFeedTransaction as $value)
        {
            $create_at = $value['create_at'];
            $price = $value['price'];

            if (isset($price) and !empty($price))
            {
                if (isset($create_at) and !empty($create_at))
                {
                    $day = date("d", strtotime($create_at));
                    $month = date('m', strtotime($create_at));
                    $year = date('Y', strtotime($create_at));
                    $hour = date('H', strtotime($create_at));
                    $minute = date('i', strtotime($create_at));
                    $seconds = date('s', strtotime($create_at));

                    // format: year,month,date,hours,minutes,seconds
                    $new = array($year, $month, $day, $hour, $minute, $seconds);
                    $series = array($new, $price);

                    array_push($data_chart_pay, $series);
                }
            }
        }
        $data_chart_pay = CJSON::encode($data_chart_pay);


        // Вкладка "Инвесторы"
        $sql = '
            SELECT ua.*, u.*, SUM(r.`count_pay`) AS total_count_pay
            FROM {{user_active}} AS ua
            INNER JOIN
            (
            SELECT ua2.*
            FROM {{user_active}} AS ua2
            ) AS r
            ON r.`user_id` = ua.`user_id`
            INNER JOIN {{users}} AS u
            ON u.id = ua.`user_id`
            WHERE ua.`project_id` = '. (int)$id .'
            GROUP BY ua.`user_id`, ua.`project_id`
            ORDER BY ua.`count_pay` ASC
        ';
        $connection = Yii::app()->db;
        $investors = $connection->createCommand($sql)->queryAll();

        $count_investors = count($investors);

        // Выплачено дивидендов
        $criteria = new CDbCriteria;
        $criteria->select = 'SUM(`sum`) as summary';
        $criteria->condition = 'operation = :operation AND project_id = :projectID';
        $criteria->params = array(':operation' => BalansHistory::OPERATION_DIVIDENDS, ':projectID' => (int)$id);
        $modelBalansHistory = BalansHistory::model()->find($criteria);
        if ($modelBalansHistory) {
            if (isset($modelBalansHistory->summary) and !empty($modelBalansHistory->summary))
                $paid_dividends = $modelBalansHistory->summary;
        }

        // Получаем кол-во доступных паев для дальнейшей покупки
        $free_count_pay = C_Bid::getFreeCountPay($id);

        $this->render('browser', array(
            'project'               => $model,
            'data'                  => $data,
            'list_month'            => $list_month,
            'list_sum'              => $list_sum,
            'list_year'             => $list_year,
            'fields'                => $fields,
            'table_bid'             => $table_bid,
            'table_user_bid'        => $table_user_bid,
            'disable_field_price'   => $disable_field_price,
            'cost_one_pay'          => $cost_one_pay,
            'table_feed_transaction' => $table_feed_transaction,
            'table_user_feed_transaction' => $table_user_feed_transaction,
            'data_chart_pay'        => $data_chart_pay,
            'table_investors'       => $table_investors,
            'count_investors'       => $count_investors,
            'paid_dividends'        => $paid_dividends, // Выплачено дивидендов
            'free_count_pay'        => $free_count_pay, // свободно паев для покупки
        ));
    }


    /**
     * Удалить миниатюру
     *
     * @param $id - id model
     */
    public function actionDeleteThumbnail($id)
    {
        $model = $this->loadModel($id);
        unlink(Yii::app()->params['uploads_folder'] . '/projects/' . $model->image);
        $model->image = '';
        $model->save();
        echo CJSON::encode(200);
        Yii::app()->end();
        //$this->redirect(array('news/admin'));
    }


    /**
     * Проверяет тип проекта и его наличие
     */
    public function actionAjaxCheckType()
    {
        if (Yii::app()->request->isAjaxRequest)
        {
            if (isset($_POST['id_project']) and !empty($_POST['id_project']))
            {
                //$exists = Projects::model()->exists('id = :ID', array(':ID' => $_POST['id_project']));
                $exists = Projects::model()->findByPk($_POST['id_project']);
                if ($exists)
                {
                    echo CJSON::encode(array(
                        'status' => 200,
                        'type' => $exists->type
                    ));
                    Yii::app()->end();
                }
                else
                {
                    echo CJSON::encode(array(
                        'status' => 300
                    ));
                    Yii::app()->end();
                }
            }
        }
    }


    /**
     * Таблица "Инвесторы"
     *
     * @return string
     */
    public function TableInvestors($project_id)
    {
        // Вкладка "Инвесторы"
        $sql = '
            SELECT ua.*, u.*, SUM(r.`count_pay`) AS total_count_pay
            FROM {{user_active}} AS ua
            INNER JOIN
            (
            SELECT ua2.*
            FROM {{user_active}} AS ua2
            ) AS r
            ON r.`user_id` = ua.`user_id`
            INNER JOIN {{users}} AS u
            ON u.id = ua.`user_id`
            WHERE ua.`project_id` = '. (int)$project_id .'
            GROUP BY ua.`user_id`, ua.`project_id`
            ORDER BY ua.`count_pay` ASC
        ';
        $connection = Yii::app()->db;
        $investors = $connection->createCommand($sql)->queryAll();

        return $this->renderPartial(
            'table_investors',
            array(
                'investors' => $investors,
                'project'   => $this->loadModel($project_id),
            ),
            true
        );
    }

}