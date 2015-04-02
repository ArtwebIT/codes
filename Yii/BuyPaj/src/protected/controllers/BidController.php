<?php

class BidController extends RController
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
    public $layout='//layouts/main';

    private $bid_count = null;
    private $bid_price = null;
    private $free_count_pay = null;

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
		$model=new Bid;

		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['Bid']))
		{
			$model->attributes=$_POST['Bid'];
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

		if(isset($_POST['Bid']))
		{
			$model->attributes=$_POST['Bid'];
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
		$dataProvider=new CActiveDataProvider('Bid');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	/**
	 * Manages all models.
	 */
	public function actionAdmin()
	{
		$model=new Bid('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Bid']))
			$model->attributes=$_GET['Bid'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Bid the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model = Bid::model()->findByPk($id);
		if ($model === null)
			throw new CHttpException(404, 'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Bid $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if (isset($_POST['ajax']) && $_POST['ajax']==='bid-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}


    /**
     * Таблица заявок (покупка или продажа)
     *
     * @param null $id - ID project
     * @return string - table bid
     */
    public function TableBid($id = null)
    {
        if (isset($id) and !empty($id))
            $_id = $id;
        elseif (isset($_GET['id']) and !empty($_GET['id']))
            $_id = $_GET['id'];

        if (isset($_id) and !empty($_id))
        {
            $bid_sale = Bid::model()->sale()->recently()->with('user')->findAll('project_id = :projectID', array(':projectID' => $_id));
            $bid_buy = Bid::model()->buy()->recently()->with('user')->findAll('project_id = :projectID', array(':projectID' => $_id));
            $project = Projects::model()->findByPk($_id);

            $table = $this->renderPartial('table_bid', array(
                'bid_sale'      => $bid_sale,
                'bid_buy'       => $bid_buy,
                'project'       => $project
            ), true);

            return $table;
        }
    }


    /**
     * Таблица всех заявок текущего пользователя (покупка или продажа)
     *
     * @param null $id - ID project
     * @return string - table user bid
     */
    public function TableUserBid($id = null)
    {
        if (isset($id) and !empty($id))
            $_id = $id;
        elseif (isset($_GET['id']) and !empty($_GET['id']))
            $_id = $_GET['id'];

        if (isset($_id) and !empty($_id))
        {
            $modelBidUser = Bid::model()->findAll('project_id = :projectID AND user_id = :userID', array(':projectID' => $_id, ':userID' => Yii::app()->user->id));

            if ($modelBidUser)
            {
                $table_user_bid = $this->renderPartial('table_user_bid', array(
                    'model' => $modelBidUser
                ), true);

                return $table_user_bid;
            }
        }
    }


    /**
     * Ajax отмена заявки пользователя
     */
    public function actionAjaxCancelBid()
    {
        if (isset($_POST['id']) and !empty($_POST['id']))
        {
            $modelProject = Projects::model()->findByPk($_POST['project_id']);

            $id = (int)$_POST['id']; // ID записи  таблице
            $project_id = (int)($_POST['project_id']);

            $_bid_price = $_POST['bid_price']; // Цена
            $_bid_count = (int)$_POST['bid_count']; // кол-во
            $_bid_type = $_POST['bid_type']; // тип заявки

            // удаляем заявку из таблицы по ID
            $bid = Bid::model()->findByPk($id);
            if ($bid->delete())
            {
                // Логирование в БД
                C_Log::addLog(Log::TYPE_INFO, null, 'удаляем заявку из таблицы по ID = ' . $id);

                // обновляем все наши таблицы на странице
                $table_bid = $this->TableBid($project_id);
                $table_user_bid = $this->TableUserBid($project_id);

                // списываем сумму из заблокированных, если заявка была на покупку
                if ($_bid_type == Bid::BID_STATUS_BUY)
                {
                    $sum = $_bid_price * (int)$_bid_count;
                    $minus_blocked = C_User::updateMoney(Yii::app()->user->id, $sum, true, true);
                    if ($minus_blocked)
                    {
                        // Логирование в БД
                        C_Log::addLog(Log::TYPE_INFO, Yii::app()->user->id, 'Отмена заявки ' . $_bid_count . ' паев в проекте ' . $modelProject->name);

                        // Send MAIL
                        $data = array(
                            'title' => 'Отмена заявки',
                            'text' => 'Отмена заявки ' . $_bid_count . ' паев в проекте ' . $modelProject->name
                        );
                        $modelUser = User::model()->findByPk(Yii::app()->user->id);
                        C_Mail::sendMailUser('simple', Yii::app()->name . ' - Отмена заявки', $modelUser->email, $data, null);

                        echo CJSON::encode(array(
                            'status' => 200,
                            'data' => $table_bid,
                            'table_user_bid' => $table_user_bid,
                            'updatebalansblock' => true, // обновить блок с балансом
                        ));
                        Yii::app()->end();
                    }
                    else
                    {
                        echo CJSON::encode(array(
                            'status' => 300,
                            'message' => 'Ошибка запроса.'
                        ));
                        Yii::app()->end();
                    }
                }
                else
                {
                    // Логирование в БД
                    C_Log::addLog(Log::TYPE_INFO, Yii::app()->user->id, 'Отмена заявки ' . $_bid_count . ' паев в проекте ' . $modelProject->name);

                    // Send MAIL
                    $data = array(
                        'title' => 'Отмена заявки',
                        'text' => 'Отмена заявки ' . $_bid_count . ' паев в проекте ' . $modelProject->name
                    );
                    $modelUser = User::model()->findByPk(Yii::app()->user->id);
                    C_Mail::sendMailUser('simple', Yii::app()->name . ' - Отмена заявки', $modelUser->email, $data, null);

                    echo CJSON::encode(array(
                        'status' => 200,
                        'data' => $table_bid,
                        'table_user_bid' => $table_user_bid
                    ));
                    Yii::app()->end();
                }
            }
            else
            {
                echo CJSON::encode(array(
                    'status' => 300,
                    'message' => 'Ошибка удаления записи.'
                ));
                Yii::app()->end();
            }
        }
        else
        {
            echo CJSON::encode(array(
                'status' => 300,
                'message' => 'Ошибка запроса.'
            ));
            Yii::app()->end();
        }

    }


    /**
     * Проверка полей на пустоту и на то, что ввели числа
     *
     * @return bool
     */
    public function validData()
    {
        if ((isset($_POST['bid_count']) and !empty($_POST['bid_count']))
            and (isset($_POST['bid_price']) and !empty($_POST['bid_price']))
        )
        {
            // проверяем на числа
            if (is_numeric($_POST['bid_count']) and is_numeric($_POST['bid_price']))
            {
                $this->bid_count = $_POST['bid_count'];
                $this->bid_price = $_POST['bid_price'];

                return true;
            }
            else
            {
                echo CJSON::encode(array(
                    'status' => 300,
                    'message' => 'Поля должны содержать только числа'
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
     * Проверка лимита для значения `кол-во паев`
     *
     * @param $status - покупка или продажа (флаг)
     * @return bool
     */
    public function checkLimit($status)
    {
        if ($this->bid_count < Bid::BID_MIN_LIMIT)
        {
            echo CJSON::encode(array(
                'status' => 300,
                'message' => 'Вы не можете ' . Bid::bidVariant('sale_buy', $status) . ' меньше ' . Bid::BID_MIN_LIMIT . ' паев',
                'show_limit_popup' => true
            ));
            Yii::app()->end();
        }
        else
        {
            return true;
        }
    }


    /**
     * Операция: выставить паи на продажу (0)
     *
     * @param $id_project
     * @param $bid_count - кол-во паев
     * @param $bid_price - цена
     */
    public function actionAjaxAddBidToSale()
    {
        if (Yii::app()->request->isAjaxRequest)
        {
            if (isset($_POST['id_project']) and !empty($_POST['id_project']))
            {
                // проверяем данные на пустоту и на то, что ввели именно числа
                if ($this->validData())
                {
                    // проверяем лимит (min 10)
                    if ($this->checkLimit(false))
                    {
                        // Для проектов типа ПОД НАШИМ УПРАВЛЕНИЕМ если пользователь нажимает «На продажу»,
                        // то скрипт должен в разделе МОИ АКТИВЫ посмотреть, есть ли у этого пользователя нужное количество паев этого актива
                        // кроме администраторов, которые могут всегда продавать паи
                        $modelProject = Projects::model()->findByPk($_POST['id_project']);
                        if ($modelProject)
                        {
                            if ($modelProject->type == Projects::TYPE_1)
                            {
                                C_User::checkUserActive(Yii::app()->user->id, $_POST['id_project'], $this->bid_count);
                            }
                        }

                        // Пользователь не может разместить заявку на продажу, по цене, ниже или равно уже имеющихся заявок на покупку
                        if ($this->checkLimitIssueSale($_POST['id_project'], $this->bid_price))
                        {
                            // находим похожую заявку текущего пользователя по такой же цене
                            //$modelBidIssueCost = Bid::model()->sale()->find('project_id = :projectID AND user_id = :userID AND price = :price AND status = :status', array(':projectID' => $_POST['id_project'], ':userID' => Yii::app()->user->id, ':price' => $this->bid_price, 'status' => Bid::BID_STATUS_SALE));
                            //if (isset($modelBidIssueCost) and !empty($modelBidIssueCost))
                            //{
                                // Обновляем данные с добавлением кол-во паев для заявки пользователя с такой же ценой
                                $int = Bid::model()->updateCounters(
                                    array('count' => $this->bid_count),
                                    'project_id = :projectID
                                    AND user_id = :userID
                                    AND price = :price
                                    AND status = :status',
                                    array(
                                        ':projectID'    => $_POST['id_project'],
                                        ':userID'       => Yii::app()->user->id,
                                        ':price'        => $this->bid_price,
                                        ':status'       => Bid::BID_STATUS_SALE
                                    )
                                );

                                // если нашли заявку по такой же цене и обновили ее
                                if ($int)
                                {
                                    $table_bid = $this->TableBid($_POST['id_project']);
                                    $table_user_bid = $this->TableUserBid($_POST['id_project']);

                                    // Логирование в БД
                                    C_Log::addLog(Log::TYPE_INFO, Yii::app()->user->id, 'Подача заявки на продажу ' . $this->bid_count . ' паев в проекте ' . $modelProject->name);

                                    // Send MAIL
                                    $data = array(
                                        'title' => 'Заявка на продажу паев',
                                        'text' => 'Подача заявки на продажу ' . $this->bid_count . ' паев в проекте ' . $modelProject->name
                                    );
                                    $modelUser = User::model()->findByPk(Yii::app()->user->id);
                                    C_Mail::sendMailUser('simple', Yii::app()->name . ' - заявки на продажу паев', $modelUser->email, $data, null);

                                    echo CJSON::encode(array(
                                        'status' => 200,
                                        'data' => $table_bid,
                                        'table_user_bid' => $table_user_bid,
                                        'message' => 'Ваша текущая заявка по такой же цене была обновлена.'
                                    ));
                                    Yii::app()->end();
                                }
                                else // просто добавляем заявку на продажу
                                {
                                    $model = new Bid;
                                    $model->project_id  = $_POST['id_project'];
                                    $model->user_id     = Yii::app()->user->id;
                                    $model->status      = Bid::BID_STATUS_SALE;
                                    $model->count       = $this->bid_count;
                                    $model->price       = $this->bid_price;

                                    if ($model->save())
                                    {
                                        $table_bid = $this->TableBid($_POST['id_project']);
                                        $table_user_bid = $this->TableUserBid($_POST['id_project']);

                                        // Логирование в БД
                                        C_Log::addLog(Log::TYPE_INFO, Yii::app()->user->id, 'Подача заявки на продажу ' . $this->bid_count . ' паев в проекте ' . $modelProject->name);

                                        // Send MAIL
                                        $data = array(
                                            'title' => 'Заявка на продажу паев',
                                            'text' => 'Подача заявки на продажу ' . $this->bid_count . ' паев в проекте ' . $modelProject->name
                                        );
                                        $modelUser = User::model()->findByPk(Yii::app()->user->id);
                                        C_Mail::sendMailUser('simple', Yii::app()->name . ' - заявки на продажу паев', $modelUser->email, $data, null);

                                        echo CJSON::encode(array(
                                            'status' => 200,
                                            'data' => $table_bid,
                                            'table_user_bid' => $table_user_bid
                                        ));
                                        Yii::app()->end();
                                    }
                                }
                            //}
                        }
                    }
                }
            }
            else
            {
                // Логирование в БД
                C_Log::addLog(Log::TYPE_ERROR, null, 'actionAjaxAddBidToSale. Не верный запрос.');

                echo CJSON::encode(array(
                    'status' => 300,
                    'message' => 'Не верный запрос.'
                ));
                Yii::app()->end();
            }
        }
    }


    /**
     * Проверяем может ли пользователь подать заявку на покупку такого кол-ва паев
     *
     * @param $project_id - ID проекта
     * @return bool
     */
    public function checkOnlineCountPay($project_id)
    {
        // Если тип проекта "под нашим управлением"
        // то все равно сколько осталось паев - заявки можно подавать всегда (уже при покупке или продаже идут проверки на наличие)
        $modelProject = Projects::model()->findByPk($project_id);
        if ($modelProject->type == Projects::TYPE_1)
        {
            return true;
        }

        // Получаем кол-во доступных паев для покупки
        $free_count_pay = C_Bid::getFreeCountPay($project_id);

        if ($free_count_pay)
        {
            $this->free_count_pay = $free_count_pay;

            // если пользователь хочет купить больше паев, чем уже осталось
            if ($this->bid_count > $free_count_pay)
            {
                echo CJSON::encode(array(
                    'status' => 201,
                    'message' => 'Вы не можете купить столько паев. Доступно только ' . $free_count_pay . ' паев.'
                ));
                Yii::app()->end();
            }
            else
            {
                return true;
            }
        }
        else
        {
            echo CJSON::encode(array(
                'status' => 201,
                'message' => 'Свободных для продажи паев больше нет.'
            ));
            Yii::app()->end();
        }
    }


    /**
     * Операция: оставить завку на покупку паев (1)
     *
     * @param $id_project
     * @param $bid_count - кол-во паев
     * @param $bid_price - цена
     */
    public function actionAjaxAddBidToBuy()
    {
        if (Yii::app()->request->isAjaxRequest)
        {
            if (isset($_POST['id_project']) and !empty($_POST['id_project']))
            {
                $modelProject = Projects::model()->findByPk($_POST['id_project']);

                // проверяем данные на пустоту и на то, что ввели именно числа
                if ($this->validData())
                {
                    // проверяем лимит (min 10)
                    if ($this->checkLimit(true))
                    {
                        // пользователь не может купить паев, если такого кол-ва в продаже уже нет
                        if ($this->checkOnlineCountPay($_POST['id_project']))
                        {
                            // пользователь не может отправить заявку на покупку, по цене равно или выше цене уже имеющихся заявок на продажу
                            if ($this->checkLimitIssueBuy($_POST['id_project'], $this->bid_price))
                            {
                                // находим похожую заявку текущего пользователя по такой же цене
                                $modelBidIssueCost = Bid::model()->buy()->find('project_id = :projectID AND user_id = :userID AND price = :price AND status = :status', array(':projectID' => $_POST['id_project'], ':userID' => Yii::app()->user->id, ':price' => $this->bid_price, 'status' => Bid::BID_STATUS_BUY));

                                if (isset($modelBidIssueCost) and !empty($modelBidIssueCost))
                                {
                                    $sum = 0;
                                    // Суммируем кол-во паев для заявки пользователя с такой же ценой
                                    //$sum = $this->bid_count + $modelBidIssueCost->count;

                                    // есть ли у пользователя нужное количество денег на балансе c учетом добавленной заявки по такой же цене
                                    if ($this->checkMoneyIssue($this->bid_price, $this->bid_count))
                                    {
                                        // Обновляем данные с добавлением кол-во паев для заявки пользователя с такой же ценой
                                        $int = Bid::model()->updateCounters(
                                            array('count' => $this->bid_count),
                                            'project_id = :projectID
                                            AND user_id = :userID
                                            AND price = :price
                                            AND status = :status',
                                            array(
                                                ':projectID'    => $_POST['id_project'],
                                                ':userID'       => Yii::app()->user->id,
                                                ':price'        => $this->bid_price,
                                                ':status'       => Bid::BID_STATUS_BUY
                                            )
                                        );

                                        // если данные обновились
                                        if ($int)
                                        {
                                            // Логирование в БД
                                            C_Log::addLog(Log::TYPE_INFO, Yii::app()->user->id, 'Обновляем данные для заявки с такой же ценой. Проект ID = ' . $_POST['id_project'] . '. Цена = ' . $this->bid_price . '. Статус = ' . Bid::BID_STATUS_BUY);

                                            $table_bid = $this->TableBid($_POST['id_project']);
                                            $table_user_bid = $this->TableUserBid($_POST['id_project']);

                                            // на балансе пользователя блокируется та сумма, на которую он готов купить паи
                                            $_sum = $this->bid_count * $this->bid_price;
                                            $int2 = C_User::updateMoney(Yii::app()->user->id, $_sum, false, true);

                                            // если обновилась заблокированная сумма
                                            if ($int2)
                                            {
                                                // кол-во свободных паев после принятия заявки
                                                $now_free_count_pay = $this->free_count_pay - $this->bid_count;

                                                // Отправляем сообщение администратору(ам) о том, что кол-во свободных паев не осталось
                                                // и что, проект можно покупать
                                                $__free_count_pay = C_Bid::getFreeCountPay($_POST['id_project']);
                                                if ($__free_count_pay == 0)
                                                {
                                                    $data = array(
                                                        'title' => 'Покупка проекта',
                                                        'text'=> 'Все паи уже раскупили. Проект "'. $modelProject->name .'" можно покупать.'
                                                    );
                                                    C_Mail::sendMailToAdmins(Yii::app()->name . ' - Покупка проекта', $data);
                                                }

                                                // Логирование в БД
                                                C_Log::addLog(Log::TYPE_INFO, Yii::app()->user->id, 'Подача заявки на покупку ' . $this->bid_count . ' паев в проекте ' . $modelProject->name);

                                                // Send MAIL
                                                $data = array(
                                                    'title' => 'Заявка на покупку паев',
                                                    'text' => 'Подача заявки на покупку ' . $this->bid_count . ' паев в проекте ' . $modelProject->name
                                                );
                                                $modelUser = User::model()->findByPk(Yii::app()->user->id);
                                                C_Mail::sendMailUser('simple', Yii::app()->name . ' - заявки на покупку паев', $modelUser->email, $data, null);

                                                echo CJSON::encode(array(
                                                    'status'            => 200,
                                                    'data'              => $table_bid,
                                                    'table_user_bid'    => $table_user_bid,
                                                    'updatebalansblock' => true, // обновить блок с балансом
                                                    'message'           => 'Ваша текущая заявка по такой же цене была обновлена.',
                                                    'free_count_pay'    => $now_free_count_pay
                                                ));
                                                Yii::app()->end();
                                            }
                                        }
                                        else
                                        {
                                            echo CJSON::encode(array(
                                                'status' => 300,
                                                'message' => 'Не возможно добавить значения к вашей текущей заявки по такой же цене.'
                                            ));
                                            Yii::app()->end();
                                        }
                                    }
                                }
                                else // если похожих заявок текущего пользователя с такой же ценой нет, то добавляем запись в базу
                                {
                                    // есть ли у пользователя нужное количество денег на балансе
                                    if ($this->checkMoneyIssue($this->bid_price, $this->bid_count))
                                    {
                                        $model = new Bid;
                                        $model->project_id  = $_POST['id_project'];
                                        $model->user_id     = Yii::app()->user->id;
                                        $model->status      = Bid::BID_STATUS_BUY;
                                        $model->count       = $this->bid_count;
                                        $model->price       = $this->bid_price;

                                        if ($model->save())
                                        {
                                            // Логирование в БД
                                            C_Log::addLog(Log::TYPE_INFO, Yii::app()->user->id, 'Сохранили заявку пользователя. Статус = ' . Bid::BID_STATUS_BUY . '. Кол-во = ' . $this->bid_count . '. Цена = ' . $this->bid_price);

                                            $table_bid = $this->TableBid($_POST['id_project']);
                                            $table_user_bid = $this->TableUserBid($_POST['id_project']);

                                            // на балансе пользователя блокируется та сумма, на которую он готов купить паи
                                            $__sum = $this->bid_count * $this->bid_price;
                                            $int = C_User::updateMoney(Yii::app()->user->id, $__sum, false, true);

                                            if ($int)
                                            {
                                                // кол-во свободных паев после принятия заявки
                                                $now_free_count_pay = $this->free_count_pay - $this->bid_count;

                                                // Логирование в БД
                                                C_Log::addLog(Log::TYPE_INFO, Yii::app()->user->id, 'Подача заявки на покупку ' . $this->bid_count . ' паев в проекте ' . $modelProject->name);

                                                // Send MAIL
                                                $data = array(
                                                    'title' => 'Заявка на покупку паев',
                                                    'text' => 'Подача заявки на покупку ' . $this->bid_count . ' паев в проекте ' . $modelProject->name
                                                );
                                                $modelUser = User::model()->findByPk(Yii::app()->user->id);
                                                C_Mail::sendMailUser('simple', Yii::app()->name . ' - заявки на покупку паев', $modelUser->email, $data, null);

                                                // Отправляем сообщение администратору(ам) о том, что кол-во свободных паев не осталось
                                                // и что, проект можно покупать
                                                $__free_count_pay = C_Bid::getFreeCountPay($_POST['id_project']);
                                                if ($__free_count_pay == 0)
                                                {
                                                    $data = array(
                                                        'title' => 'Покупка проекта',
                                                        'text'=> 'Все паи уже раскупили. Проект "'. $modelProject->name .'" можно покупать.'
                                                    );
                                                    C_Mail::sendMailToAdmins(Yii::app()->name . ' - Покупка проекта', $data);
                                                }

                                                echo CJSON::encode(array(
                                                    'status'            => 200,
                                                    'updatebalansblock' => true, // обновить блок с балансом
                                                    'data'              => $table_bid,
                                                    'table_user_bid'    => $table_user_bid,
                                                    'free_count_pay'    => $now_free_count_pay
                                                ));
                                                Yii::app()->end();
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
            else
            {
                echo CJSON::encode(array(
                    'status' => 300,
                    'message' => 'Не верный запрос.'
                ));
                Yii::app()->end();
            }
        }
    }


    /**
     * ПОКУПКА ПАЕВ
     */
    public function actionAjaxBuyPayFinish()
    {
        if (Yii::app()->request->isAjaxRequest)
        {
            // если пользователь пытается купить у самого же себя
            $preModelBid = Bid::model()->findByPk($_POST['record_id']);
            if ($preModelBid)
            {
                if ($preModelBid->user_id == Yii::app()->user->id)
                {
                    echo CJSON::encode(array(
                        'status' => 200,
                        'message' => 'Вы не можете купить паи у себя.'
                    ));
                    Yii::app()->end();
                }
            }

            // Количество паев, которые хочет купить пользователь
            $int_pay = ($_POST['int_pay']) ? (int)$_POST['int_pay'] : 0;

            // Цена за пай
            $int_price = ($_POST['int_price']) ? (float)$_POST['int_price'] : 0;

            // Кол-во паев, которые Контрагент готов продать
            $int_count = ($_POST['int_count']) ? (int)$_POST['int_count'] : 0;

            $sum = $int_pay * $int_price;


            // если пользователь пытается купить меньше 10 паев
            if ($int_pay < Bid::BID_MIN_LIMIT)
            {
                echo CJSON::encode(array(
                    'status' => 201,
                    'message' => 'Вы не можете купить меньше ' . Bid::BID_MIN_LIMIT . ' паев.',
                    'show_limit_popup' => true
                ));
                Yii::app()->end();
            }

            // Если у пользователя есть деньги для покупки
            if ($this->actionCheckMoneyOnBalans($sum))
            {
                // Если покупатель купил все паи, то заявка продавца исчезает из стакана
                if ($int_pay == $int_count)
                {
                    $pay = Bid::model()->findByPk($_POST['record_id'])->delete();

                    if (!$pay)
                    {
                        echo CJSON::encode(array(
                            'status' => 300,
                            'message' => 'Возможно такой заявки нет. Попробуйте обновить страницу и повторить действие.'
                        ));
                        Yii::app()->end();
                    }
                }
                // Заявка продавца уменьшается на то количество паев, которое приобрел покупатель
                else
                {
                    $y = $int_pay * (-1); // уменьшаем
                    $pay = Bid::model()->updateCounters(
                        array('count' => $y),
                        'id = :ID',
                        array(':ID' => $_POST['record_id'])
                    );

                    // Логирование в БД
                    C_Log::addLog(Log::TYPE_INFO, null, 'Заявка ('.$_POST['record_id'].') продавца уменьшается на то количество паев ('. $y .'), которое приобрел покупатель');

                    if (!$pay)
                    {
                        echo CJSON::encode(array(
                            'status' => 300,
                            'message' => 'Возможно такой заявки нет. Попробуйте обновить страницу и повторить действие.'
                        ));
                        Yii::app()->end();
                    }
                }

                // ПОКУПАЕМ ПАИ
                if ($pay)
                {
                    // уменьшаем баланс покупателя
                    if (C_User::updateMoney(Yii::app()->user->id, $sum, true, false))
                    {
                        // увеличиваем баланс продавца
                        if (C_User::updateMoney($_POST['user_id'], $sum, false, false))
                        {
                            // заносим данные о покупке в ленту сделок
                            list($controller) = Yii::app()->createController('Feedtransaction');
                            $controller->actionAddFeedTransaction($int_price, $int_pay, $_POST['project_id'], Yii::app()->user->id, $_POST['user_id'], FeedTransaction::FEED_STATUS_BUY);

                            $total_pay = $sum;

                            // увеличиваем кол-во паев у текущего покупателя для текущего актива
                            list($controller) = Yii::app()->createController('Useractive');
                            $controller->actionUpdateUserActive(Yii::app()->user->id, $_POST['project_id'], $int_pay, $total_pay, false);

                            // уменьшаем кол-во паев у продавца
                            list($controller) = Yii::app()->createController('Useractive');
                            $controller->actionUpdateUserActive($_POST['user_id'], $_POST['project_id'], $int_pay, $total_pay, true);


                            // Обновляем историю баланса
                            $project_name = C_Projects::getProjectName($_POST['project_id']);
                            // у покупателя - текущий юзер
                            C_User::addBalansHistory(Yii::app()->user->id, $_POST['project_id'], BalansHistory::OPERATION_BUY, $sum, $int_pay . ' паев проекта `' . $project_name . '`', null);
                            // у продавца
                            C_User::addBalansHistory($_POST['user_id'], $_POST['project_id'], BalansHistory::OPERATION_SALE, $sum, $int_pay . ' паев проекта `' . $project_name . '`', null);


                            $table_bid = $this->TableBid($_POST['project_id']);

                            // таблица "Инвесторы"
                            list($controller) = Yii::app()->createController('projects');
                            $table_investors = null;
                            $table_investors = $controller->TableInvestors($_POST['project_id']);

                            // таблица "лента сделок"
                            list($controller) = Yii::app()->createController('feedtransaction');
                            $table_feed_transaction = null;
                            $table_feed_transaction = $controller->TableFeedTransaction($_POST['project_id']);

                            // таблица "Мои сделки"
                            list($controller) = Yii::app()->createController('feedtransaction');
                            $table_user_feed_transaction = null;
                            $table_user_feed_transaction = $controller->TableUserFeedTransaction($_POST['project_id']);

                            // если мы покупали паи у администрации (продавца)
                            // то уменьшаем кол-во паев у проекта
                            /*
                            if (C_Rights::checkRoleByID($_POST['user_id'], array(Yii::app()->params['roles']['subadmin'])))
                            {
                                C_Projects::updateCountPay($_POST['project_id'], $int_pay, true);
                            }
                            */


                            // отправка сообщения на почту
                            $data = array(
                                'title' => 'Покупка паев',
                                'text' => 'Вы купили ' . $int_pay . ' паев на сумму ' .$sum . '$'
                            );
                            $modelUser = User::model()->findByPk(Yii::app()->user->id);
                            C_Mail::sendMailUser('simple', Yii::app()->name . ' - покупка', $modelUser->email, $data, null);

                            // отправка сообщения на почту
                            $data = array(
                                'title' => 'Покупка паев',
                                'text' => 'Вам продали ' . $int_pay . ' пай(ев) на сумму ' .$sum . '$'
                            );
                            $modelUser = User::model()->findByPk($_POST['user_id']);
                            C_Mail::sendMailUser('simple', Yii::app()->name . ' - покупка', $modelUser->email, $data, null);

                            
                            echo CJSON::encode(array(
                                'status'                        => 200,
                                'message'                       => 'Вы купили паи.',
                                'updatebalansblock'             => true, // обновить блок с балансом
                                'table_feed_transaction'        => $table_feed_transaction, // обновить "Лента сделок"
                                'table_user_feed_transaction'   => $table_user_feed_transaction, // обновить "Мои сделки"
                                'table_investors'               => $table_investors, // обновить "Инвесторы"
                                'data'                          => $table_bid
                            ));
                            Yii::app()->end();
                        }
                    }
                }
            }

        }
    }


    /**
     * ПРОДАЖА ПАЕВ
     */
    public function actionAjaxSalePayFinish()
    {
        if (Yii::app()->request->isAjaxRequest)
        {
            // если пользователь пытается продать паи себе
            $preModelBid = Bid::model()->findByPk($_POST['record_id']);
            if ($preModelBid)
            {
                if ($preModelBid->user_id == Yii::app()->user->id)
                {
                    echo CJSON::encode(array(
                        'status' => 200,
                        'message' => 'Вы не можете продать себе паи.'
                    ));
                    Yii::app()->end();
                }
            }

            // Количество паев, которые хочет продать пользователь
            $int_pay = ($_POST['int_pay']) ? (int)$_POST['int_pay'] : 0;

            // Цена за пай
            $int_price = ($_POST['int_price']) ? (float)$_POST['int_price'] : 0;

            // Кол-во паев, которые Контрагент готов купить
            $int_count = ($_POST['int_count']) ? (int)$_POST['int_count'] : 0;

            $sum = $int_pay * $int_price;

            // если пользователь пытается купить меньше 10 паев
            if ($int_pay < Bid::BID_MIN_LIMIT)
            {
                echo CJSON::encode(array(
                    'status' => 201,
                    'message' => 'Вы не можете продать меньше ' . Bid::BID_MIN_LIMIT . ' паев.',
                    'show_limit_popup' => true
                ));
                Yii::app()->end();
            }

            // если продали столько, сколько пользователь хотел купить
            // то удаляем заявку из списка
            $exists = Bid::model()->findByPk($_POST['record_id']);
            if ($exists->count == $int_pay)
            {
                Bid::model()->findByPk($_POST['record_id'])->delete();
            }
            else
            {
                // Заявка покупателя уменьшается на купленное количество паев
                $y = $int_pay * (-1); // уменьшаем
                $pay = Bid::model()->updateCounters(
                    array('count' => $y),
                    'id = :ID',
                    array(':ID' => $_POST['record_id'])
                );

                // Логирование в БД
                C_Log::addLog(Log::TYPE_INFO, null, 'Заявка ('.$_POST['record_id'].') покупателя уменьшается на купленное количество ('.$y.') паев');
            }

            $total_pay = $sum;

            // у продавца списывается это количество паев
            list($controller) = Yii::app()->createController('Useractive');
            $controller->actionUpdateUserActive(Yii::app()->user->id, $_POST['project_id'], $int_pay, $total_pay,  true);

            // Покупателю, соответственно это же количество паев добавляется
            list($controller) = Yii::app()->createController('Useractive');
            $controller->actionUpdateUserActive($_POST['user_id'], $_POST['project_id'], $int_pay, $total_pay, false);

            // С баланса разместившего заявку на покупку (из тех средств, что заблокированы)
            // списывается сумма сделки c покупателя
            C_User::updateMoney($_POST['user_id'], $sum, true, true);

            // и зачисляется на баланс продавца
            C_User::updateMoney(Yii::app()->user->id, $sum, false, false);


            // Информация о сделке фиксируется в ЛЕНТЕ СДЕЛОК
            // заносим данные о продаже в ленту сделок
            list($controller) = Yii::app()->createController('Feedtransaction');
            $controller->actionAddFeedTransaction($int_price, $int_pay, $_POST['project_id'], $_POST['user_id'], Yii::app()->user->id, FeedTransaction::FEED_STATUS_SALE);


            // Обновляем историю баланса
            $project_name = C_Projects::getProjectName($_POST['project_id']);
            // у продавца - текущий юзер
            C_User::addBalansHistory(Yii::app()->user->id, $_POST['project_id'], BalansHistory::OPERATION_SALE, $sum, $int_pay . ' паев проекта `' . $project_name . '`', null);
            // у покупателя
            C_User::addBalansHistory($_POST['user_id'], $_POST['project_id'], BalansHistory::OPERATION_BUY, $sum, $int_pay . ' паев проекта `' . $project_name . '`', null);




            $table_bid = $this->TableBid($_POST['project_id']);


            // таблица "Инвесторы"
            list($controller) = Yii::app()->createController('projects');
            $table_investors = null;
            $table_investors = $controller->TableInvestors($_POST['project_id']);

            // таблица "лента сделок"
            list($controller) = Yii::app()->createController('feedtransaction');
            $table_feed_transaction = null;
            $table_feed_transaction = $controller->TableFeedTransaction($_POST['project_id']);

            // таблица "Мои сделки"
            list($controller) = Yii::app()->createController('feedtransaction');
            $table_user_feed_transaction = null;
            $table_user_feed_transaction = $controller->TableUserFeedTransaction($_POST['project_id']);

            // если паи продает администрация (продавец)
            // то уменьшаем кол-во паев у проекта
            /*
            if (C_Rights::checkRoleByID(Yii::app()->user->id, array(Yii::app()->params['roles']['subadmin'])))
            {
                C_Projects::updateCountPay($_POST['project_id'], $int_pay, true);
            }
            */


            // отправка сообщения на почту продацу
            $data = array(
                'title' => 'Продажа паев',
                'text' => 'Ваши паи проданы'
            );
            $modelUser = User::model()->findByPk(Yii::app()->user->id);
            C_Mail::sendMailUser('simple', Yii::app()->name . ' - продажа', $modelUser->email, $data, null);

            // отправка сообщения на почту покпателю
            $data = array(
                'title' => 'Покупка паев',
                'text' => 'Вам продали паи'
            );
            $modelUser = User::model()->findByPk($_POST['user_id']);
            C_Mail::sendMailUser('simple', Yii::app()->name . ' - покупка', $modelUser->email, $data, null);


            echo CJSON::encode(array(
                'status'                        => 200,
                'message'                       => 'Вы продали паи.',
                'updatebalansblock'             => true, // обновить блок с балансом
                'table_feed_transaction'        => $table_feed_transaction, // обновить "Лента сделок"
                'table_user_feed_transaction'   => $table_user_feed_transaction, // обновить "Мои сделки"
                'table_investors'               => $table_investors, // обновить "Инвесторы"
                'data'                          => $table_bid
            ));
            Yii::app()->end();
        }
    }


    /**
     * Проверка средств у пользователя
     *
     * @param $sum
     * @return bool
     */
    public function actionCheckMoneyOnBalans($sum = 0)
    {
        if (is_numeric($sum))
        {
            if ($sum == 0)
            {
                echo CJSON::encode(array(
                    'status' => 201,
                    'message' => 'Введите нужное количество паев.'
                ));
                Yii::app()->end();
            }
            else
            {
                // если у пользователя есть средства
                if (C_User::checkMoneyOnBalans(Yii::app()->user->id, $sum))
                {
                    return true;
                }
                else
                {
                    echo CJSON::encode(array(
                        'status' => 201,
                        'message' => 'Для этой сделки у вас недостаточно средств. Вам нужно сначала пополнить ' . CHtml::link('баланс', array('/balans'))
                    ));
                    Yii::app()->end();
                }
            }
        }
    }


    /**
     * Проверяем, есть ли у пользователя нужное кол-во доступных денег на балансе
     *
     * @param $bid_price - Цена одного пая
     * @param $bid_count - кол-во паев, которые хочет купить покупатель
     * @return bool
     */
    public function checkMoneyIssue($bid_price, $bid_count)
    {
        $userBalans = C_User::getUserBalans();
        $sum = $bid_price * $bid_count;

        // Если у пользователя есть столько денег или больше
        if (($sum == $userBalans['user_allow_balans']) or ($sum < $userBalans['user_allow_balans']))
        {
            return true;
        }
        else //  Если денег на балансе недостаточно
        {
            echo CJSON::encode(array(
                'status' => 200,
                'message' => 'Чтобы покупать паи, вам нужно сначала пополнить ' . CHtml::link('баланс', array('/balans'))
            ));
            Yii::app()->end();
        }
    }


    /**
     * Пользователь не может отправить заявку на покупку, по цене равно или выше цене уже имеющихся заявок на продажу
     *
     * @param $project_id  - ID project
     * @param $user_price - пользовательская цена
     * @return bool
     */
    public function checkLimitIssueBuy($project_id, $user_price)
    {
        // если заявок на продаже нет вообще, тогда пропускаем
        $model_BID = Bid::model()->sale()->exists('project_id = :projectID', array(':projectID' => $_POST['id_project']));
        if (!$model_BID)
        {
            return true;
        }
        else
        {
            if (isset($project_id) and !empty($project_id))
            {
                if (is_numeric($project_id))
                {
                    $criteria = new CDbCriteria;
                    $criteria->select = 'MIN(price) as minprice';
                    $criteria->condition = 'project_id = :projectID AND status = :status';
                    $criteria->params = array(':projectID' => $project_id, ':status' => Bid::BID_STATUS_SALE);
                    $model = Bid::model()->find($criteria);

                    if (isset($model) and !empty($model))
                    {
                        $min = $model->minprice;
                        if (($user_price == $min) OR ($user_price > $min))
                        {
                            echo CJSON::encode(array(
                                'status' => 300,
                                'message' => 'Вы можете купить паи куда более выгодно, приняв уже размещенные заявки продавцов.'
                            ));
                            Yii::app()->end();
                        }
                        else
                        {
                            return true;
                        }
                    }
                }
            }
        }
    }


    /**
     * Пользователь не может разместить заявку на продажу, по цене, ниже или равно уже имеющихся заявок на покупку
     *
     * @param $project_id  - ID project
     * @param $user_price - пользовательская цена
     */
    public function checkLimitIssueSale($project_id, $user_price)
    {
        // если заявок на покупок нет вообще, тогда пропускаем
        $existsBid = Bid::model()->buy()->exists('project_id = :projectID', array(':projectID' => $_POST['id_project']));
        if (!$existsBid)
        {
            return true;
        }
        else
        {
            if (isset($project_id) and !empty($project_id))
            {
                if (is_numeric($project_id))
                {
                    $criteria = new CDbCriteria;
                    $criteria->select = 'MAX(price) as maxprice';
                    $criteria->condition = 'project_id = :projectID AND status = :status';
                    $criteria->params = array(':projectID' => $project_id, ':status' => Bid::BID_STATUS_BUY);
                    $model = Bid::model()->find($criteria);

                    if (isset($model) and !empty($model))
                    {
                        $max = $model->maxprice;
                        if (($user_price == $max) OR ($user_price < $max))
                        {
                            echo CJSON::encode(array(
                                'status' => 300,
                                'message' => 'Вы можете продать свои паи куда более выгодно, приняв уже размещенные заявки покупателей.'
                            ));
                            Yii::app()->end();
                        }
                        else
                        {
                            return true;
                        }
                    }
                }
            }
        }
    }


    /**
     * Автопокупка паев
     */
    public function actionAjaxAutoBuy()
    {
        if (Yii::app()->request->isAjaxRequest)
        {
            if (isset($_POST['id_project']) and !empty($_POST['id_project']))
            {
                $sql = '
                    SELECT b.id, u.email, b.user_id, SUM(b.`count`) AS total_count_pay, SUM(b.`price`) AS total_price_pay
                    FROM {{bid}} AS b
                    LEFT JOIN bay_users AS u
                    ON u.id = b.user_id
                    WHERE b.`status` = '. Bid::BID_STATUS_BUY .
                    ' AND b.project_id = '. $_POST['id_project'].
                    ' GROUP BY b.user_id';
                $connection = Yii::app()->db;
                $bid = $connection->createCommand($sql)->queryAll();
                if ($bid)
                {
                    foreach ($bid as $value)
                    {
                        $result = false;

                        $sum = $value['total_price_pay'] * $value['total_count_pay'];

                        // начисляем активы
                        $addActive = C_UserActive::addActive($value['user_id'], $_POST['id_project'], $value['total_count_pay'], $sum);
                        if ($addActive)
                        {
                            // списываем деньги с заблокированных средств
                            $int2 = C_User::updateMoney($value['user_id'], $sum, true, true);
                            if ($int2)
                            {
                                // списываем деньги с текущего баланса
                                $int3 = C_User::updateMoney($value['user_id'], $sum, true, false);
                                if ($int3)
                                {
                                    // оформляем сделку как покупку паев
                                    $feed_trans = new FeedTransaction();
                                    $feed_trans->price = $value['total_price_pay'];
                                    $feed_trans->count_pay = $value['total_count_pay'];
                                    $feed_trans->sum_trans = $sum;
                                    $feed_trans->project_id = $_POST['id_project'];
                                    $feed_trans->user_buyer_id = $value['user_id'];
                                    $feed_trans->user_seller_id = Yii::app()->user->id;
                                    $feed_trans->type = FeedTransaction::FEED_STATUS_BUY;

                                    if ($feed_trans->save())
                                    {
                                        // Обновляем историю баланса
                                        $int4 = C_User::addBalansHistory($value['user_id'], $_POST['id_project'], BalansHistory::OPERATION_BUY, $sum, 'Автопокупка', null);
                                        if ($int4)
                                        {
                                            // удалить заявки
                                            $int5 = Bid::model()->findByPk($value['id']);
                                            if ($int5->delete())
                                            {
                                                $modelProject = Projects::model()->findByPk($_POST['id_project']);
                                                // отправляем всем пользователям уведомление о том, что они стали владеть паями
                                                // Send MAIL
                                                $data = array(
                                                    'title' => 'Автопокупка паев',
                                                    'text' => 'Вы владете ' . $value['total_count_pay'] . ' паями в проекте ' . $modelProject->name
                                                );
                                                C_Mail::sendMailUser('simple', Yii::app()->name . ' - автопокупка', $value['email'], $data, null);

                                                $result = true;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if ($result)
                    {
                        // Логирование в БД
                        C_Log::addLog(Log::TYPE_INFO, null, 'Автопокупка произведена успешно');

                        echo CJSON::encode(array(
                            'status' => 200,
                            'message' => 'Автопокупка произведена успешно.'
                        ));
                        Yii::app()->end();
                    }
                }
            }
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
}
