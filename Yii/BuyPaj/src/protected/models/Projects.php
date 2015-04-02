<?php

/**
 * This is the model class for table "{{projects}}".
 *
 * The followings are the available columns in table '{{projects}}':
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property string $income
 * @property string $yield
 * @property string $total_capitalization
 * @property string $info
 * @property string $description
 * @property string $count_pay
 * @property string $cost_one_pay
 * @property string $months
 * @property string $plans
 * @property string $reports
 */
class Projects extends CActiveRecord
{

    const TYPE_1 = 0; // Под нашим управлением
    const TYPE_2 = 1; // Возможно приобретение

    public $sales_pay = null; // Свободные паи для продажи
    public $image;
    public $auto_cost_one_pay; // Цена пая – формируется автоматически и равняется цене последней сделки по этому проекту (актуально для проектов Под нашим управлением
    public $sum_buy; // Сумма покупки (заполняется при создании проекта с типом "Под нашим управлением")


	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{projects}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, type', 'required'),
			array('type', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>255),
			array('income, total_capitalization, count_pay, cost_one_pay', 'length', 'max'=>10),
			array('yield', 'length', 'max'=>5),
			array('months', 'length', 'max'=>3),
            array('image', 'ImageValid'),

            array('info, description, plans, reports', 'safe'),
            array('image', 'unsafe'),


            array('sum_buy', 'numerical', 'integerOnly'=>true),
            //array('sum_buy', 'sumbuyValid'),
            array('sum_buy', 'unsafe'),


            //array('cost_one_pay', 'checkEmptyCostOnPay'),

            // The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, type, income, yield, total_capitalization, info, description, count_pay, cost_one_pay, months, plans, reports', 'safe', 'on'=>'search'),
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
            'incomeProjects'    => array(self::HAS_MANY, 'IncomeProject', 'project_id'),
            'feedTransaction'   => array(self::HAS_MANY, 'FeedTransaction', 'project_id')
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Название',
			//'type' => '0 - Под нашим управлением, 1 - Возможно приобретение',
			'type' => 'Тип проекта',
			'income' => 'Доход',
			'yield' => 'Доходность (%)',
			'total_capitalization' => 'Общая капитализация',
			'info' => 'Информация',
			'description' => 'Описание',
			'count_pay' => 'Количество паев',
			'cost_one_pay' => 'Стоимость 1 пая',
			'months' => 'Срок владения проектом (мес.)',
			'plans' => 'План',
			'reports' => 'Отчеты',
            'image' => 'Картинка',

            'sum_buy' => 'Сумма покупки (указывается единожды при создании)'
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('type',$this->type);
		$criteria->compare('income',$this->income,true);
		$criteria->compare('yield',$this->yield,true);
		$criteria->compare('total_capitalization',$this->total_capitalization,true);
		$criteria->compare('info',$this->info,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('count_pay',$this->count_pay,true);
		$criteria->compare('cost_one_pay',$this->cost_one_pay,true);
		$criteria->compare('months',$this->months,true);
		$criteria->compare('plans',$this->plans,true);
		$criteria->compare('reports',$this->reports,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Projects the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}


    public static function itemAlias($type, $code = NULL)
    {
        $_items = array(
            'ProjectType' => array(
                self::TYPE_1 => 'Под нашим управлением',
                self::TYPE_2 => 'Возможно приобретение',
            ),
        );
        if (isset($code))
            return isset($_items[$type][$code]) ? $_items[$type][$code] : false;
        else
            return isset($_items[$type]) ? $_items[$type] : false;
    }


    /**
     * Valid image upload
     */
    public function ImageValid($attribute, $params)
    {
        $allow = array('jpg', 'jpeg', 'gif', 'png');

        $imageUploadFile = CUploadedFile::getInstance($this, 'image');
        if (!empty($imageUploadFile) and isset($imageUploadFile))
        {
            list($key, $ext) = explode('/', $imageUploadFile->type);
            if (!in_array($ext, $allow)) {
                $this->addError($attribute, 'Формат файла не верный');
            }
        }
    }


    /**
     * Валидация поля `Сумма покупки`
     *
     * @param $attribute
     * @param $params
     * @return bool
     */
    public function sumbuyValid($attribute, $params)
    {
        if ($this->isNewRecord)
        {
            if ($this->type == self::TYPE_1)
            {
                if (!isset($this->sum_buy) or empty($this->sum_buy) or ($this->sum_buy == 0))
                {
                    $this->addError('sum_buy', 'Необходимо заполнить поле');
                }
                else
                {
                    return true;
                }
            }
            else
            {
                return true;
            }
        }
        else
        {
            return true;
        }
    }


    public function beforeSave()
    {
        if ($this->type == self::TYPE_2) // Возможно приобретение
        {
            if (!isset($this->cost_one_pay) or empty($this->cost_one_pay) or ($this->cost_one_pay == 0))
            {
                $this->addError('cost_one_pay', 'Необходимо заполнить данные');
            } else {
                return true;
            }
        }
        else
            return true;
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