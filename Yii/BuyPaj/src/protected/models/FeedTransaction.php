<?php

/**
 * This is the model class for table "{{feed_transaction}}".
 *
 * The followings are the available columns in table '{{feed_transaction}}':
 * @property integer $id
 * @property string $create_at
 * @property string $price
 * @property integer $count_pay
 * @property integer $sum_trans
 * @property integer $project_id
 * @property integer $user_buyer_id
 * @property integer $user_seller_id
 * @property integer $type
 *
 * The followings are the available model relations:
 * @property Users $userBuyer
 * @property Users $userSeller
 * @property Projects $project
 */
class FeedTransaction extends CActiveRecord
{

    const FEED_STATUS_SALE = 0; // продажа
    const FEED_STATUS_BUY = 1; // покупка

    public $userBuyerName = null;
    public $userSellerName = null;
    public $contragent = null;

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{feed_transaction}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_buyer_id, user_seller_id, sum_trans, type', 'required'),
			array('count_pay, project_id, user_buyer_id, user_seller_id, type', 'numerical', 'integerOnly' => true),
			array('price', 'length', 'max' => 10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, create_at, price, count_pay, sum_trans, project_id, user_buyer_id, user_seller_id, type', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'userBuyer' => array(self::BELONGS_TO, 'User', 'user_buyer_id'),
			'userSeller' => array(self::BELONGS_TO, 'User', 'user_seller_id'),
			'project' => array(self::BELONGS_TO, 'Projects', 'project_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'create_at' => 'Дата',
			'price' => 'сумма сделки (покупки/продажи)',
			'count_pay' => 'Кол-во паев',
            'sum_trans' => 'Сумма сделки (price * count_pay)',
			'project_id' => 'Актив',
			'user_buyer_id' => 'Покупатель',
			'user_seller_id' => 'Продавец',
			'type' => 'тип сделки (0-продажа, 1-покупка)',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('count_pay',$this->count_pay);
		$criteria->compare('sum_trans',$this->sum_trans);
		$criteria->compare('project_id',$this->project_id);
		$criteria->compare('user_buyer_id',$this->user_buyer_id);
		$criteria->compare('user_seller_id',$this->user_seller_id);
		$criteria->compare('type',$this->type);
		$criteria->compare('create_at',$this->create_at, true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return FeedTransaction the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


    public function scopes()
    {
        return array(
            'sale' => array(
                'condition' => 't.type = ' . self::FEED_STATUS_SALE, // продажа
            ),
            'buy' => array(
                'condition' => 't.type = ' . self::FEED_STATUS_BUY, // покупка
            ),
            'recently' => array(
                'order' => 'price DESC'
            ),
            'last' => array(
                'order' => 'create_at DESC'
            ),
            'asc' => array(
                'order' => 'create_at ASC'
            ),
        );
    }


    /**
     * Переменные значения в зависимости от действий (покупка, продажа)
     *
     * @param $type - тип выборки
     * @param null $code - вариант
     * @return bool
     */
    public static function transactionType($type, $code = NULL)
    {
        $_items = array(
            'type' => array(
                self::FEED_STATUS_SALE => 'продажа',
                self::FEED_STATUS_BUY => 'покупка',
            ),
        );
        if (isset($code))
            return isset($_items[$type][$code]) ? $_items[$type][$code] : false;
        else
            return isset($_items[$type]) ? $_items[$type] : false;
    }


    /**
     * Получаем имя проекта
     *
     * @param $project_id
     * @return mixed
     */
    public static function getProjectName($project_id)
    {
        $model = Projects::model()->findByPk($project_id);
        return $model->name;
    }


    /**
     * имя пользователя
     *
     * @param $user_id
     * @return mixed
     */
    public static function getUserName($user_id)
    {
        $model = User::model()->findByPk($user_id);
        return $model->username;
    }


    public function afterSave()
    {
        $ext = array(
            'Model' => get_class(),
            'Method' => Yii::app()->controller->id . '/' . Yii::app()->controller->action->id
        );
        $data = array_merge($ext, $this->attributes);
        // Логирование в БД
        C_Log::addLog(Log::TYPE_INFO, null, $data);
    }
}
