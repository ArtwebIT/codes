<?php

class BalansController extends RController
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
    public $layout='//layouts/main';

    public $balans = null; // для пополнения
    public $buy_system = null; // для пополнения

    public $balans_out = null; // для вывода
    //public $buy_system_out = null; // для вывода

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
		$model=new Balans;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Balans']))
		{
			$model->attributes=$_POST['Balans'];
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

		if(isset($_POST['Balans']))
		{
			$model->attributes=$_POST['Balans'];
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
        $this->pageTitle = 'Баланс';

        // форма пополнения баланса
        $model = new FillInBalansForm('fillinbalans');
        $fillinbalans_form = $this->renderPartial('fillinbalans', array('model' => $model), true);

        // форма вывода средств
        $model_out = new FillOutBalansForm('filloutbalans');
        $filloutbalans_form = $this->renderPartial('filloutbalans', array('model' => $model_out), true);

        // таблица Историй Баланса
        $model_balans_history = new BalansHistory('search');
        $model_balans_history->unsetAttributes();
        $model_balans_history->user_id = Yii::app()->user->id;
        if (isset($_GET['BalansHistory']))
            $model_balans_history->attributes = $_GET['BalansHistory'];

        // таблица заявок на вывод
        $modelFilloutRequest = FilloutRequest::model()->status()->findAll('user_id = :userID', array(':userID' => Yii::app()->user->id));
        $table_fillout_request = $this->renderPartial('table_fillout_request', array('model' => $modelFilloutRequest), true);

        // получаем баланс пользователя
        $UserBalans = C_User::getUserBalans();

		$this->render('index', array(
			'fillinbalans_form'     => $fillinbalans_form, // форма пополнения баланса
			'filloutbalans_form'    => $filloutbalans_form, // форма вывода средств

            'model_balans_history'  => $model_balans_history,
            'table_fillout_request' => $table_fillout_request,

            'user_balans'            => $UserBalans['user_balans'],
            'user_blocked_balans'    => $UserBalans['user_blocked_balans'],
            'user_allow_balans'      => $UserBalans['user_allow_balans'],
		));
	}


	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Balans('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Balans']))
			$model->attributes=$_GET['Balans'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Balans the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Balans::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Balans $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='fill-in-balans-form-fillinbalans-form')
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
     * Проверяем данные на пустоту и на число
     *
     * @return bool
     */
    public function validDataIn()
    {
        if ((isset($_POST['sum_balans']) and !empty($_POST['sum_balans']))
            and (isset($_POST['buy_system']) and !empty($_POST['buy_system']))
        )
        {
            // проверяем баланс на число
            if (is_numeric($_POST['sum_balans']) and ($_POST['sum_balans'] > 0))
            {
                $this->balans       = (float)$_POST['sum_balans'] * 1;
                $this->buy_system   = $_POST['buy_system'];
                return true;
            }
            else
            {
                echo CJSON::encode(array(
                    'status' => 300,
                    'message' => 'Поле `Сумма` должно содержать число'
                ));
                Yii::app()->end();
            }
        }
        else
        {
            echo CJSON::encode(array(
                'status' => 300,
                'message' => 'Поля должны быть заполнены'
            ));
            Yii::app()->end();
        }
    }


    /**
     * Проверяем данные на пустоту и на число
     *
     * @return bool
     */
    public function validDataOut()
    {
        if ((isset($_POST['sum_balans_out']) and !empty($_POST['sum_balans_out']))
            and (isset($_POST['buy_system']) and !empty($_POST['buy_system']))
        )
        {
            // проверяем баланс на число
            if (is_numeric($_POST['sum_balans_out']) and ($_POST['sum_balans_out'] > 0))
            {
                $this->balans_out   = (float)$_POST['sum_balans_out'] * 1;
                $this->buy_system   = $_POST['buy_system'];
                return true;
            }
            else
            {
                echo CJSON::encode(array(
                    'status' => 300,
                    'message' => 'Поле `Сумма` должно содержать число'
                ));
                Yii::app()->end();
            }
        }
        else
        {
            echo CJSON::encode(array(
                'status' => 300,
                'message' => 'Поля должны быть заполнены'
            ));
            Yii::app()->end();
        }
    }


    /**
     * ЗАЯВКА НА ПОПОЛНЕНИЕ БАЛАНСА (Проверка данных платежной системы)
     */
    public function actionFillinbalans()
    {
        if (Yii::app()->request->isAjaxRequest)
        {
            if ($this->validDataIn())
            {
                $user_buysystem_value = null;
                // Проверяем и получаем данные платежной системы у пользователя
                $user_buysystem_value = C_User::getUserBuySystem(Yii::app()->user->id, $this->buy_system);

                if (isset($user_buysystem_value) and !empty($user_buysystem_value))
                {
                    $this->balans = null;
                    $this->buy_system = null;

                    echo CJSON::encode(array(
                        'status' => 200
                    ));
                    Yii::app()->end();
                }
                else
                {
                    echo CJSON::encode(array(
                        'status' => 300,
                        'message' => 'Для этой платежной системы не указаны данные в вашем профиле.'
                    ));
                    Yii::app()->end();
                }
            }
        }
    }


    /**
     * ЗАЯВКА НА ВЫВОД СРЕДСТВ
     */
    public function actionFilloutbalans()
    {
        if (Yii::app()->request->isAjaxRequest)
        {
            if ($this->validDataOut())
            {
                $user_buysystem_value = null;

                // проверяем достаточно ли средств у пользователя для вывода
                $UserBalans = C_User::getUserBalans();

                // 1.) Если пользователь выводит больше чем сам баланс
                if ($this->balans_out > $UserBalans['user_balans'])
                {
                    echo CJSON::encode(array(
                        'status' => 300,
                        'message' => 'Вы не можете вывести '.$this->balans_out.' USD поскольку у вас недостаточно средств.'
                    ));
                    $this->balans_out = null;
                    $this->buy_system = null;
                    Yii::app()->end();
                }

                // 2.) Если пользователь пытается вывести больше чем «Доступно к выводу», но меньше чем есть на балансе
                if ( ($this->balans_out > $UserBalans['user_allow_balans']) and ($this->balans_out < $UserBalans['user_balans']))
                {
                    echo CJSON::encode(array(
                        'status' => 300,
                        'message' => 'Вы не можете вывести '.$this->balans_out.' USD поскольку у вас недостаточно средств. <br /> Вам нужно или указать сумму меньше, или отменить имеющиеся заявки на покупку паев.'
                    ));
                    $this->balans_out = null;
                    $this->buy_system = null;
                    Yii::app()->end();
                }

                // 3.) если пользователь пытается вывести менее 10 долларов
                if ($this->balans_out < Balans::LIMIT_BALANS_OUT)
                {
                    $this->balans_out = null;
                    $this->buy_system = null;

                    echo CJSON::encode(array(
                        'status' => 300,
                        'message' => 'Минимальная сумма к выводу составляет 10 USD'
                    ));
                    Yii::app()->end();
                }

                // Проверяем и получаем данные платежной системы у пользователя
                $user_buysystem_value = C_User::getUserBuySystem(Yii::app()->user->id, $this->buy_system);

                if (isset($user_buysystem_value) and !empty($user_buysystem_value))
                {
                    if (isset($this->balans_out) and !empty($this->balans_out))
                    {
                        // считаем сумму всех заявок пользователя
                        $count_sum_fill_out = FilloutRequest::model()->status()->findAll('user_id = :userID', array('userID' => Yii::app()->user->id));
                        $sum = 0;
                        foreach ($count_sum_fill_out as $value) {
                            $sum += $value->sum;
                        }

                        // если сумма всех + текущей заявки превышает сумму доступную к выводу
                        if ( ($sum + $this->balans_out) > $UserBalans['user_allow_balans'])
                        {
                            echo CJSON::encode(array(
                                'status' => 300,
                                'message' => 'Вы больше не можете оставить заявку на вывод, у вас недостаточно средств.'
                            ));
                            Yii::app()->end();
                        }

                        // добавляем нашу заявку на вывод в систему на обработку для администратора
                        $model_fillout = new FilloutRequest;
                        $model_fillout->user_id = Yii::app()->user->id;
                        $model_fillout->sum = $this->balans_out;
                        $model_fillout->info = FillOutBalansForm::itemAlias('BuySystems', $this->buy_system) . ': ' . $user_buysystem_value;

                        if ($model_fillout->save())
                        {
                            // отправка сообщения на почту
                            $data = array(
                                'title' => 'Заявка на вывод средств',
                                'text' => 'Заявка на вывод средств подана'
                            );
                            $modelUser = User::model()->findByPk(Yii::app()->user->id);
                            C_Mail::sendMailUser('simple', Yii::app()->name . ' - вывод средств', $modelUser->email, $data, null);

                            Yii::app()->user->setFlash('success', 'Заявка на вывод средств подана');

                            // Логирование в БД
                            C_Log::addLog(Log::TYPE_INFO, Yii::app()->user->id, 'Заявка на вывод '. $this->balans_out .'$ подана');

                            $this->balans_out   = null;
                            $this->buy_system   = null;

                            echo CJSON::encode(array(
                                'status' => 200
                            ));
                            Yii::app()->end();
                        }
                        else
                        {
                            // log
                            C_Log::addLog(Log::TYPE_ERROR, null, 'FilloutBalans. Ошибка при сохранении');

                            echo CJSON::encode(array(
                                'status' => 300,
                                'message' => 'Произошла ошибка при сохранении. Мы уже работаем над ней.'
                            ));
                            Yii::app()->end();
                        }
                    }
                }
            }
        }
    }


    /**
     * Отменить вывод
     *
     * @param $id
     */
    public function actionCancel($id)
    {
        $modelFilloutRequest = FilloutRequest::model()->findByPk($id);
        if ($modelFilloutRequest->delete())
        {
            // отправка сообщения на почту
            $data = array(
                'title' => 'Заявка на вывод средств',
                'text' => 'Заявка на вывод средств отменена.'
            );
            $modelUser = User::model()->findByPk(Yii::app()->user->id);
            C_Mail::sendMailUser('simple', Yii::app()->name . ' - вывод средств', $modelUser->email, $data, null);

            // Логирование в БД
            C_Log::addLog(Log::TYPE_INFO, Yii::app()->user->id, 'Заявка '.$id.' отменена.');

            Yii::app()->user->setFlash('success', 'Заявка отменена.');
            $this->redirect(array('/balans'));
        }
    }


    /**
     * Списываем (выводим) баланс пользователя
     *
     * @param $user_id - ID user
     * @param $sum - С баланса пользователя списывается эта сумма средств
     */
    public function actionWithdraw($id, $user_id, $sum)
    {
        if (!is_numeric($sum)) {
            echo CJSON::encode(array(
                'status' => 300,
                'message' => 'Вы должны ввести число для вывода средств'
            ));
            Yii::app()->end();
        }

        $modelBalans = Balans::model()->find('user_id = :userID', array(':userID' => $user_id));
        if  (!$modelBalans)
        {
            // Логирование в БД
            C_Log::addLog(Log::TYPE_ERROR, $user_id, 'Такого пользователя не существует');

            echo CJSON::encode(array(
                'status' => 300,
                'message' => 'Такого пользователя не существует'
            ));
            Yii::app()->end();
        }

        $modelFilloutRequest = FilloutRequest::model()->find('id = :ID AND user_id = :userID AND sum = :sum', array(':ID' => $id, ':userID' => $user_id, ':sum' => $sum));
        if (!$modelFilloutRequest)
        {
            // Логирование в БД
            C_Log::addLog(Log::TYPE_ERROR, $user_id, 'Ошибка целосности данных');

            echo CJSON::encode(array(
                'status' => 300,
                'message' => 'Ошибка целосности данных'
            ));
            Yii::app()->end();
        }

        // если мы пытаемся вывести больше чем у пользователя есть средств, доступные на вывод (БАЛАНС - ЗАБЛОКИРОВАННЫЕ)
        if ($sum > ($modelBalans['sum'] - $modelBalans['blocked_sum']))
        {
            echo CJSON::encode(array(
                'status' => 300,
                'message' => 'Вы пытаетесь вывести больше, чем есть у пользователя средств, доступные на вывод'
            ));
            Yii::app()->end();
        }

        if ($sum <= ($modelBalans['sum'] - $modelBalans['blocked_sum']))
        {
            $modelBalans->sum = $modelBalans['sum'] - $sum;
            $modelFilloutRequest->status = 1;

            if (!$modelFilloutRequest->save())
            {
                // Логирование в БД
                C_Log::addLog(Log::TYPE_ERROR, null, 'Невозможно изменить статус на ОДОБРЕНО');

                echo CJSON::encode(array(
                    'status' => 300,
                    'message' => 'Невозможно изменить статус на ОДОБРЕНО'
                ));
                Yii::app()->end();
            }
            else
            {
                if (!$modelBalans->save())
                {
                    // Логирование в БД
                    C_Log::addLog(Log::TYPE_ERROR, null, 'Невозможно вывести средства');

                    echo CJSON::encode(array(
                        'status' => 300,
                        'message' => 'Невозможно вывести средства'
                    ));
                    Yii::app()->end();
                }
                else
                {
                    // отправка сообщения на почту
                    $data = array(
                        'title' => 'Вывод средств',
                        'text' => 'Ваша заявка на вывод '. $sum .'$ одобрена. На ваш кошелек были выведены средства.'
                    );
                    $modelUser = User::model()->findByPk($user_id);
                    C_Mail::sendMailUser('simple', Yii::app()->name . ' - вывод средств', $modelUser->email, $data, null);


                    // записываем в историю баланса то, что было указано в заявке
                    $model = new BalansHistory;
                    $model->user_id = $user_id;
                    $model->operation = BalansHistory::OPERATION_FILLOUT;
                    $model->sum = (float)$sum;
                    $model->notes = $modelFilloutRequest->info;
                    $model->save();

                    // Логирование в БД
                    C_Log::addLog(Log::TYPE_INFO, $user_id, 'Сохраняем историю баланса. Операция: ' .  BalansHistory::OPERATION_FILLOUT . '. Сумма: ' . $sum);

                    // Все ок. с баланса списали средства
                    echo CJSON::encode(array(
                        'status' => 200
                    ));
                    Yii::app()->end();
                }
            }
        }
    }


    /**
     * Отклонить заявку на вывод
     *
     * @param $id - ID record
     * @param $user_id - ID user
     * @param $sum - сумма заявки
     */
    public function actionCancelWithdraw($sum, $id, $user_id)
    {
        $model = FilloutRequest::model()->find(
            'id = :ID AND user_id = :userID AND status = :status',
            array(
                ':ID' => $id,
                ':userID' => $user_id,
                ':status' => FilloutRequest::STATUS_PROCESS
            )
        );
        if ($model)
        {
            $model->status = 2;
            if ($model->save())
            {
                // отправка сообщения на почту
                $data = array(
                    'title' => 'Заявка на вывод отклонена',
                    'text' => 'Ваша заявка на вывод '. $sum .'$ отклонена администратором.'
                );
                $modelUser = User::model()->findByPk($user_id);
                C_Mail::sendMailUser('simple', Yii::app()->name . ' - вывод средств', $modelUser->email, $data, null);

                // Логирование в БД
                C_Log::addLog(Log::TYPE_INFO, $user_id, 'Заявка на вывод отклонена пользователем');

                echo CJSON::encode(array(
                    'status' => 200
                ));
                Yii::app()->end();
            }
            else
            {
                // Логирование в БД
                C_Log::addLog(Log::TYPE_ERROR, null, 'Невозможно изменить статус на ОТКЛОНЕНО');

                echo CJSON::encode(array(
                    'status' => 300,
                    'message' => 'Невозможно изменить статус на ОТКЛОНЕНО'
                ));
                Yii::app()->end();
            }
        }

    }


    /**
     * Ajax Обновить блок с балансом в левом сайдбаре
     */
    public function actionAjaxUpdateBalansBlock()
    {
        $balans_block = $this->renderPartial(
            'balans_block',
            '',
            true
        );

        echo CJSON::encode(array(
            'status' => 200,
            'data' => $balans_block
        ));
        Yii::app()->end();
    }
}
