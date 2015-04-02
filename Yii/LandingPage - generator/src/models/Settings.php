<?php
/**
 * This is the model class for table "Settings".
 *
 * The followings are the available columns in table 'Settings':
 * @property string $name
 * @property string $value
 */
class Settings extends CActiveRecord
{
    public $id;
    public $name;
    public $value;

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return User the static model class
     */
    public static function model($className=__CLASS__)
    {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'Settings';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {

        $error_messages = ErrorProcessor::getErrors("WEB", 'User');

        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('name', 'length', 'max' => 255),
            array('value', 'length', 'max' => 255),

            // The following rule is used by search().
            // Please remove those attributes that should not be searched.
            array('name, value', 'safe', 'on'=>'search'),
        );
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'name'      => 'Name',
            'value'     => 'Value',
        );
    }


    /**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.
        $criteria=new CDbCriteria;

        $criteria->compare('name', $this->name);
        $criteria->compare('value', $this->value);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
}
