<?php

/**
 * This is the model class for table "Content".
 *
 * The followings are the available columns in table 'Content':
 * @property string $id
 * @property integer $record_type
 * @property string $url
 * @property string $text_body
 * @property string $title
 * @property string $description
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $user_id
 * @property string $picture_path
 *
 * @property string $companyName
 * @property string $customer
 * @property string $customerEmail
 * @property boolean $isFavorite
 * @property string $keywords
 * @property string $phoneNumber
 * @property string $webId
 * @property string $website
 *
 * The followings are the available model relations:
 * @property User $user
 */
class Content extends CActiveRecord
{
	private $error_messages;

    public $ids;
    public $sync_records;
    public $delete;

    /**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Content the static model class
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
		return 'Content';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('record_type', 'required', 'message' => $this->error_messages[ErrorProcessor::CONTENT_RECORD_TYPE_EMPTY], 'on' => array('add','edit')),
            // array('url', 'url', 'defaultScheme' => "http", 'message' => $this->error_messages[ErrorProcessor::CONTENT_URL]),
			array('url', 'length', 'max'=>255, 'message' => $this->error_messages[ErrorProcessor::CONTENT_URL_LENGTH], 'on' => array('add','edit')),
            array('title', 'required', 'message' => $this->error_messages[ErrorProcessor::CONTENT_TITLE_EMPTY], 'on' => array('add','edit')),
            array('title', 'length', 'max'=>255, 'message' => $this->error_messages[ErrorProcessor::CONTENT_TITLE_LENGTH], 'on' => array('add','edit')),
            array('description', 'length', 'max'=>255, 'message' => $this->error_messages[ErrorProcessor::CONTENT_DESRIPTION_LENGTH], 'on' => array('add','edit')),
            // array('text_body', 'safe', 'message' => $this->error_messages[ErrorProcessor::CONTENT_TEXT_NOT_SAFE], 'on' => array('add','edit')),
            array('record_type', 'isValidRecordType', 'on' => array('add','edit')),
            array('url', 'isUrlValid', 'on' => array('add','edit')),
            array('text_body', 'length', 'max' => 10000, 'on' => array('add','edit')),
            array('ids', 'required', 'on' => 'delete'),
            array('ids', 'isIDsValid', 'on' => 'delete'),
            array('id', 'isIDValid', 'on' => 'edit'),
            array('phone_id', 'required', 'on' => array('add'), 'message' => $this->error_messages[ErrorProcessor::CONTENT_PHONE_ID_EMPTY]),
            array('phone_id', 'unique_phoneid', 'on' => array('add'), 'message' => $this->error_messages[ErrorProcessor::CONTENT_PHONE_ID_NOT_UNIQUE]),
            array('companyName', 'length', 'max' => 255, 'message' => $this->error_messages[ErrorProcessor::CONTENT_COMPANY_NAME_EMPTY]),
            //array('companyName', 'isValidCompanyName', 'message' => $this->error_messages[ErrorProcessor::CONTENT_COMPANY_NAME_VALID]),
            array('customer', 'length', 'max' => 255, 'message' => $this->error_messages[ErrorProcessor::CONTENT_CUSTOMER_EMPTY]),
            array('customerEmail', 'length', 'max' => 255, 'message' => $this->error_messages[ErrorProcessor::CONTENT_CUSTOMER_EMAIL_EMPTY]),
            array('isFavorite', 'length', 'max' => 1, 'message' => $this->error_messages[ErrorProcessor::CONTENT_FAVORITE_EMPTY]),
            array('keywords', 'length', 'max' => 255, 'message' => $this->error_messages[ErrorProcessor::CONTENT_KEYWORDS_EMPTY]),
            array('phoneNumber', 'length', 'max'=> 255, 'message' => $this->error_messages[ErrorProcessor::CONTENT_PHONE_NUMBER_EMPTY]),
            array('webId', 'length', 'max' => 255, 'message' => $this->error_messages[ErrorProcessor::CONTENT_WEB_ID_EMPTY]),
            array('website', 'length', 'max' => 255, 'message' => $this->error_messages[ErrorProcessor::CONTENT_WEBSITE_EMPTY]),
            array('picture_path', 'length', 'max' => 255, 'message' => $this->error_messages[ErrorProcessor::CONTENT_KEYWORDS_EMPTY]),


            // array('picture_path', 'url', 'message' => "PICTURE PATH"),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, user_id, record_type, url, text_body, companyName, customer, customerEmail, isFavorite, keywords, phoneNumber, webId, website, title, description, picture_path', 'safe', 'on'=>'search'),
		);
	}

    public function beforeSave()
    {
        if ($this->isNewRecord)
        {
            $this->created_at = new CDbExpression('NOW()');
            $this->updated_at = new CDbExpression('NOW()');
        }
        else
        {
            $this->updated_at = new CDbExpression('NOW()');
        }

        return parent::beforeSave();
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

    public function init()
    {
        $this->error_messages = ErrorProcessor::getErrors("API", get_class($this));
    }

    /* Validation */

    public function unique_phoneid($attribute,$params)
    {
        $exist = Content::model()->exists("phone_id=?", array($this->phone_id));

        if ($exist)
        {
            $this->addError('phone_id', $this->error_messages[ErrorProcessor::CONTENT_PHONE_ID_NOT_UNIQUE]);
        }
    }

    public function isIDValid($attribute,$params)
    {
        $record = Content::model()->findByPk($this->id);

        $valid = true;

        if ($record == null)
        {
            $valid = false;
        }
        else
        {
            if (Auth::getUser()->id != $record->user_id)
            {
                $valid = false;
            }
        }

        if (!$valid)
        {
            $this->addError('id', $this->error_messages[ErrorProcessor::CONTENT_EDIT_ID_NOT_VALID]);
        }
    }

    /**
     * @param $attribute
     * @param $params
     */
    public function isIDsValid($attribute,$params)
    {
        $valid = true;

        foreach ($this->ids as $id)
        {
            $record = Content::model()->findByPk($id);

            if ($record == null)
            {
                $valid = false;
                break;
            }
            else
            {
                if (Auth::getUser()->id != $record->user_id)
                {
                    $valid = false;
                    break;
                }
            }
        }

        if (!$valid)
        {
            $this->addError('ids', $this->error_messages[ErrorProcessor::CONTENT_DELETE_IDS_NOT_VALID]);
        }
    }

    /* Custom validation */

    /**
     * Validate url presents if record type eq 1 | 2 | 3
     */
    public function isUrlValid($attribute,$params)
    {
        if ($this->record_type == VIDEO || $this->record_type == AUDIO || $this->record_type == PHOTO)
        {
            if (empty($this->url) || !isset($this->url))
            {
                $this->addError('record_type',$this->error_messages[ErrorProcessor::CONTENT_URL_EMPTY]);
            }
        }
    }


    /**
     * Validate User
     */
    public function isValidUser($attribute, $params)
    {
        $user = User::model()->findByPk($this->user_id);

        if ($user->activated != true || $user->auth_token != Auth::getUser()->auth_token)
        {
            $this->addError('user_id',$this->error_messages[ErrorProcessor::CONTENT_USERID_NOT_FOUND]);
        }
    }

    /**
     * Checkrecord type. It should be 1 | 2 | 3 | 4
     */
    public function isValidRecordType($attribute,$params)
    {
        // convert to int
        $rt = intval($this->record_type);

        // allowed recor type values
        $r_types = array(AUDIO, VIDEO, PHOTO, TEXT);

        if (! in_array($rt, $r_types))
        {
            $this->addError('record_type', $this->error_messages[ErrorProcessor::CONTENT_RECORD_TYPE_WRONG_DATATYPE]);
        }
    }

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id'            => 'ID',
			'record_type'   => 'Record Type',
			'url'           => 'Url',
			'text_body'     => 'Text',
			'title'         => 'Title',
			'description'   => 'Description',
			'created_at'    => 'Created At',
			'updated_at'    => 'Updated At',
			'user_id'       => 'User',
            'phone_id'      => 'phone_id',
            'picture_path'  => 'picture_path',
            'companyName'   => 'Company Name',
            'customer'      => 'Customer',
            'customerEmail' => 'Customer Email',
            'isFavorite'    => 'Favorite',
            'keywords'      => 'Keywords',
            'phoneNumber'   => 'Phone Number',
            'webId'         => 'webId',
            'website'       => 'Website'
		);
	}

    /**
     * Modify
     */

    public function add_record()
    {
        // fill user id in
        $this->user_id = Auth::getUser()->id;

        // do some work here if need
        return $this->save();
    }

    public function update_record($content)
    {
        unset($content["id"]);

        return Content::model()->updateByPk($this->id, $content);
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('record_type',$this->record_type);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('text_body',$this->text_body,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('description',$this->description,true);
		$criteria->compare('created_at',$this->created_at);
		$criteria->compare('updated_at',$this->updated_at);
		$criteria->compare('user_id',$this->user_id,true);
        $criteria->compare('picture_path',$this->picture_path,true);
        $criteria->compare('companyName',$this->companyName, true);
        $criteria->compare('customer', $this->customer, true);
        $criteria->compare('customerEmail', $this->customerEmail, true);
        $criteria->compare('isFavorite', $this->isFavorite, true);
        $criteria->compare('keywords', $this->keywords, true);
        $criteria->compare('phoneNumber', $this->phoneNumber, true);
        $criteria->compare('webId', $this->webId, true);
        $criteria->compare('website', $this->website, true);


		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    /*
     * Action methods
     */

    public function getDetails($record_id)
    {
        $record = null;

        if (intval($record_id) == 0)
        {
            $this->addError('user_id',$this->error_messages[ErrorProcessor::CONTENT_RECORD_NOT_FOUND]);
        }
        else
        {
            $record = Content::model()->findByPk($record_id);

            if ($record == null)
            {
                $this->addError('user_id',$this->error_messages[ErrorProcessor::CONTENT_RECORD_NOT_FOUND]);
            }
        }

        return $record;
    }

    /**
     * Paging ???
     */
    public function getAll($page_number, $page_size)
    {
        $models = Content::model()->findAll($this->page_criteria($page_number, $page_size, new CDbCriteria()));

        return $models;
    }

    /**
     * Display all the merchant’s testimonials in descending order
     */
    public function scopes()
    {
        return array(
            'recently' => array(
                'order' => 'created_at DESC'
            ),
        );
    }

    private function page_criteria($page_number, $page_size, $criteria)
    {
        if ($page_number > 0)
        {
            $count=Content::model()->count($criteria);

            $pages=new CPagination($count);

            $pages->pageSize= $page_size > 0 ? $page_size : PAGE_SIZE;
            $pages->currentPage = $page_number > 0 ? $page_number : 1;

            $pages->applyLimit($criteria);
        }

        return $criteria;
    }

    /**
     * Paging ???
     * works only for API
     */
    public function getMine($page_number, $page_size)
    {
        $criteria = new CDbCriteria();
        $criteria->condition = 'user_id=:user_id';
        $criteria->params = array(':user_id'=>Auth::getUser()->id);

        $this->page_criteria($page_number, $page_size, $criteria);

        return Content::model()->findAll($criteria);
    }

    public function delete_records()
    {
        $condition = "";

        foreach($this->ids as $id)
        {
            $condition .= "id=" . $id . " or ";
        }

        $condition = substr($condition, 0, strlen($condition) - 4);

        return Content::model()->deleteAll($condition);
    }

    public function get_record($model)
    {
        return Content::model()->findByPk($model->id);
    }

    public function get_content_by_phoneid($phone_id)
    {
        return Content::model()->find("phone_id=?", array($phone_id));
    }

    public function sync($params)
    {
        $deleted = array();
        $updated = array();
        $added = array();
        $brocken = array();

        // validate content items
        foreach($params as $content)
        {
            $content["user_id"] = Auth::getUser()->id;

            if (isset($content["phone_id"]) && !empty($content["phone_id"]))
            {
                // try to find record in DB
                $current_item = $this->get_content_by_phoneid($content["phone_id"]);

                // if content item has found,
                if ($current_item)
                {
                    // if item has marked for delete, delete it
                    if (isset($content["delete"]) &&  $content["delete"] != 0)
                    {
                        $current_item->delete();

                        array_push($deleted, $content["phone_id"]);
                    }
                    else
                    {
                        // try to update it, if it's valid
                        $current_item->attributes = $content;
                        if (! $current_item->validate())
                        {
                            // otherwise add error to brocken storage
                            $content["errors"] = $this->get_errors($current_item->Errors);
                            array_push($brocken, $content);
                        }
                        else
                        {
                            unset($content["id"]);

                            if (isset($content['url']))
                            {
                                $str_pos = strpos($content['url'], 'youtube.com');

                                if (isset($str_pos) and ($str_pos > 0))
                                {
                                    Content::model()->updateByPk($current_item->id, $content);
                                    // fill in updated storage
                                    array_push($updated, $content["phone_id"]);
                                }
                                else
                                {
                                    $tmp_content = new Content();
                                    $tmp_content->addError("sync_update_records_error", $this->error_messages[ErrorProcessor::SYNC_UPDATE_ERROR]);
                                    $content["errors"] = $this->get_errors($tmp_content->Errors);
                                    // otherwise add error to brocken storage
                                    array_push($brocken, $content);
                                }
                            }
                            else
                            {
                                Content::model()->updateByPk($current_item->id, $content);
                                // fill in updated storage
                                array_push($updated, $content["phone_id"]);
                            }
                        }
                    }
                }
                else
                {
                    // if record has not found in DB, and marked for deletion, it is probably error, save it
                    if (isset($content["delete"]) &&  $content["delete"] != 0)
                    {
                        $tmp_content = new Content();
                        $tmp_content->addError("sync_records", $this->error_messages[ErrorProcessor::CONTENT_SYNC_DELETE_PHONE_ID_NOT_FOUND]);
                        $content["errors"] = $this->get_errors($tmp_content->Errors);
                        array_push($brocken, $content);
                    }
                    else
                    {
                        // if phone_id not found in DB, perhaps it is new
                        $new_item = new Content('add');
                        $new_item->attributes = $content;

                       // validate and create new record
                        if ($new_item->validate())
                        {
                            $new_item->user_id = Auth::getUser()->id;

                            if (isset($content['url']))
                            {
                                $str_pos = strpos($content['url'], 'youtube.com');

                                if (isset($str_pos) and ($str_pos > 0))
                                {
                                    $new_item->save();
                                    // fill in updated storage
                                    array_push($added, $content["phone_id"]);
                                }
                                else
                                {
                                    // ERROR
                                    $tmp_content = new Content();
                                    $tmp_content->addError("sync_update_records_error", $this->error_messages[ErrorProcessor::SYNC_UPDATE_ERROR]);
                                    $content["errors"] = $this->get_errors($tmp_content->Errors);
                                    // otherwise add error to brocken storage
                                    array_push($brocken, $content);
                                }
                            }
                            else
                            {
                                $new_item->save();
                                // fill in updated storage
                                array_push($added, $content["phone_id"]);
                            }
                        }
                        else
                        {
                            $content["errors"] = $this->get_errors($new_item->Errors);
                            array_push($brocken, $content);
                        }
                    }
                }
            }
            else
            {
                $tmp_content = new Content();
                $tmp_content->addError("sync_records", $this->error_messages[ErrorProcessor::CONTENT_SYNC_PHONE_ID_EMPTY]);
                $content["errors"] = $this->get_errors($tmp_content->Errors);
                // otherwise add error to brocken storage
                array_push($brocken, $content);
            }
        }
        return array("added_items" => $added, "deleted_items" => $deleted, "updated_items" => $updated, "skipped_items" => $brocken);
    }

    private function get_errors($errros)
    {
        return ErrorProcessor::getErrorCode('API',get_class($this), $errros);
    }
}
