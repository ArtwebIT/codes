<?php

/**
 * This is the model class for table "{{fillout_request}}".
 *
 * The followings are the available columns in table '{{fillout_request}}':
 * @property integer $id
 * @property integer $user_id
 * @property string $sum
 * @property string $info
 * @property string $create_at
 *
 * The followings are the available model relations:
 * @property Users $user
 */
class FilloutRequest extends CActiveRecord
{
    const STATUS_PROCESS = 0; // Ожидание
    const STATUS_OK = 1; // Одобрено
    const STATUS_CANCEL = 2; // Отклонено

    public $username;
    public $_firstname;
    public $_lastname = null;
    public $_firstname_lastname = null;
    public $_balans = null;


	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{fillout_request}}';
	}


	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, sum', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('sum', 'length', 'max'=>11),
			array('info', 'length', 'max'=>255),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, sum, info, create_at, status', 'safe', 'on'=>'search'),

            // для CActiveDataProvider (GridView)
            array('username, firstname, lastname, firstname_lastname, create_at, balans', 'safe', 'on'=>'search'),
		);
	}


	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'user' => array(self::BELONGS_TO, 'User', 'user_id'),
		);
	}


	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
            'user_id' => 'user_id',
			'sum' => 'Сумма ($)',
			'info' => 'Кошелек',
			'create_at' => 'Дата записи',
            'status' => 'Состояние', // 0-Ожидание, 1-Одобрено
            'username' => 'Логин',
            'firstname_lastname' => 'Имя Фамилия',
            'balans' => 'Баланс',
		);
	}


    /**
     * scopes
     *
     * @return array
     */
    public function scopes()
    {
        return array(
            'status' => array(
                'condition' => 'status = ' . self::STATUS_PROCESS,
            )
        );
    }


    /**
     * Get Last name on Profile
     *
     * @return mixed
     */
    public function getlastname()
    {
        $this->_lastname = $this->user->profile['lastname'];
        return $this->_lastname;
    }


    /**
     * set Last name on Profile
     *
     * @param $value
     */
    public function setlastname($value)
    {
        $this->_lastname = trim($value);
    }


    /**
     * Get First name on Profile
     *
     * @return mixed
     */
    public function getfirstname()
    {
        $this->_firstname = $this->user->profile['firstname'];
        return $this->_firstname;
    }


    /**
     * set first name on Profile
     *
     * @param $value
     */
    public function setfirstname($value)
    {
        $this->_firstname = trim($value);
    }


    /**
     * Get First name and Last name
     *
     * @return string
     */
    public function getfirstname_lastname()
    {
        if (isset($this->user->profile) and !empty($this->user->profile))
            return implode(' ', array($this->user->profile['firstname'], $this->user->profile['lastname']));
    }


    /**
     * set First name and Last name
     *
     * @param $value
     */
    public function setfirstname_lastname($value)
    {
        $value = trim($value);
        if (strpos($value, ' ') !== false)
        {
            $cities = explode(' ', $value);
            $this->_firstname = $cities[0];
            $this->_lastname = $cities[1];
        }
        else
        {
            $this->_firstname = trim($value);
            $this->_lastname = trim($value);
        }
    }


    /*
     * Получаем баланс пользователя
     */
    public function getBalans()
    {
        $balans = Balans::model()->find('user_id = :userID', array(':userID' => $this->user->id));
        if ($balans)
            $this->_balans = $balans['sum'] - $balans['blocked_sum'];
        else
            $this->_balans = '0.00';

        return $this->_balans;
    }


    /**
     * set Balans
     *
     * @param $value
     */
    public function setBalans($value)
    {
        $this->_balans = trim($value);
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

        $criteria->with = array('user');

		$criteria->compare('id',$this->id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('sum',$this->sum,true);
		$criteria->compare('info',$this->info,true);
		$criteria->compare('t.create_at',$this->create_at,true);
		$criteria->compare('t.status',$this->status,true);
        $criteria->compare('username',$this->username,true);
        $criteria->compare('firstname', $this->_firstname, true, 'OR');
        $criteria->compare('lastname', $this->_lastname, true, 'OR');
        $criteria->compare('balans', $this->_balans, true);

		return new CActiveDataProvider($this, array(
			'criteria'    => $criteria,
            'pagination'  => false
		));
	}


    /**
     * get item Alias
     *
     * @param $type
     * @param null $code
     * @return bool
     */
    public static function itemAlias($type, $code = NULL)
    {
        $_items = array(
            'Status' => array(
                self::STATUS_PROCESS   => 'Ожидание', // 0
                self::STATUS_OK        => 'Одобрено', // 1
                self::STATUS_CANCEL    => 'Отклонено', // 2
            ),
        );
        if (isset($code))
            return isset($_items[$type][$code]) ? $_items[$type][$code] : false;
        else
            return isset($_items[$type]) ? $_items[$type] : false;
    }


	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return FilloutRequest the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


    /**
     * afterSave
     */
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
