<?php

/**
 * This is the model class for table "{{bid}}".
 *
 * The followings are the available columns in table '{{bid}}':
 * @property integer $id
 * @property integer $project_id
 * @property integer $user_id
 * @property integer $status
 * @property string $count
 * @property string $price
 *
 * The followings are the available model relations:
 * @property Projects $project
 * @property Users $user
 */
class Bid extends CActiveRecord
{
    const BID_STATUS_SALE = 0;
    const BID_STATUS_BUY = 1;
    const BID_MIN_LIMIT = 10;


    public $minprice; // пользователь не может отправить заявку на покупку, по цене равно или выше цене уже имеющихся заявок на продажу
    public $maxprice; // пользователь не может разместить заявку на продажу, по цене, ниже или равно уже имеющихся заявок на покупку

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{bid}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('project_id, user_id, status, count', 'required'),
			array('project_id, user_id, status', 'numerical', 'integerOnly'=>true),
			array('count, price', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, project_id, user_id, status, count, price', 'safe', 'on'=>'search'),
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
			'project' => array(self::BELONGS_TO, 'Projects', 'project_id'),
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
			'project_id' => 'Project',
			'user_id' => 'User',
			'status' => '0-продают, 1-покупают',
			'count' => 'кол-во паев',
			'price' => 'цена покупки/продажи',
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
		$criteria->compare('project_id',$this->project_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('status',$this->status);
		$criteria->compare('count',$this->count,true);
		$criteria->compare('price',$this->price,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Bid the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


    public function scopes()
    {
        return array(
            'sale' => array(
                'condition' => 't.status = ' . self::BID_STATUS_SALE, // продают
            ),
            'buy' => array(
                'condition' => 't.status = ' . self::BID_STATUS_BUY, // покупают
            ),
            'recently' => array(
                'order' => 'price DESC'
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
    public static function bidVariant($type, $code = NULL)
    {
        $_items = array(
            'sale_buy' => array(
                '0' => 'продать',
                '1' => 'купить',
            ),
        );
        if (isset($code))
            return isset($_items[$type][$code]) ? $_items[$type][$code] : false;
        else
            return isset($_items[$type]) ? $_items[$type] : false;
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
