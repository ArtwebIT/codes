<?php

class LandingpagesController extends Controller
{
    // for layout
    public $template;

    public function actionAjax($request)
    {
        die('actionAjax');
    }


    /**
     * LOAD TEMPLATE
     *
     * @param $id - ID template
     */
    public function actionView($id)
    {
        $this->layout = 'clickbuilder';
        $this->pageTitle = 'ClickBuilder';

        $template = Templates::model()->findByPk($id);
        $template_path = $this->getTemplatePath($template->name);

        // for layouts variable
        $this->template = array(
            'page_slug'             => $template->name,
            'page_title'            => trim($template->title),
            'meta_title'            => $template->meta_title,
            'meta_description'      => $template->meta_description,
            'meta_keywords'         => $template->meta_keywords
        );

        $this->render('view', array(
            'template_id'       => $template->id,
            'template_name'     => $template->name,
            'template_file'     => $template_path,
            'edit'              => 'false'
        ));
    }


    /**
     * Update template
     */
    public function actionUpdate()
    {
        if (!empty($_POST['user_variation_id'][0]))
        {
            $modelUserVariations = UserVariations::model()->findByPk($_POST['user_variation_id'][0]);

            $modelUserVariations->data = isset($_POST['data']) ? ($_POST['data']) : '';

            if ($modelUserVariations->update())
            {
                $modelVariations = Variations::model()->findByPk($modelUserVariations->id_variation);
                $modelVariations->name = $_POST['template_name'];
                $modelVariations->url = $_POST['page_url'];

                if ($modelVariations->update())
                {
                    $result = array(
                        'status'    => 200,
                        'body'      => $modelUserVariations->id
                    );
                    $result = json_encode($result);
                    echo $result;
                }
            }
        }
    }


    /**
     * Save template
     */
    public function actionSave()
    {
        $modelVariations = new Variations;
        $modelVariations->id_user   = Yii::app()->user->id;
        $modelVariations->name      = $_POST['template_name'];
        $modelVariations->url       = $_POST['page_url'];
        $modelVariations->create_time = new CDbExpression('NOW()');

        if ($modelVariations->save())
        {
            $modelUserVariations = new UserVariations;
            $modelUserVariations->id_template = $_POST['template_id'];

            if ($modelUserVariations->save())
            {
                $result = array(
                    'status'    => 200,
                    'body'      => $modelUserVariations->id
                );
                $result = json_encode($result);
                echo $result;
            }
            else
            {
                print_r($modelUserVariations->getErrors());
            }
        }
    }


    /**
     * My page regenerate status
     */
    public function actionRegenerate()
    {
        $result = array(
            'status'    => 200,
            'body'      => 'ok'
        );
        $result = json_encode($result);
        echo $result;
    }


    /**
     * Edit template, Preload template after saved
     */
    public function actionEdit($id)
    {
        $request_post = isset($_POST['request']) ? $_POST['request'] : '';

        $data = UserVariations::model()->with('idVariation')->findByPk($id);
        $template = Templates::model()->findByPk($data['id_template']);

        if ($template)
        {
            if ($request_post == 'page-data')
            {
                $result = array(
                    'status' => 200,
                    'body' => array(
                        'bribemail'             => null,
                        'color_data'            => json_decode($data['color_data']),
                        'edit_data'             => json_decode($data['data']),
                        'font_data'             => json_decode($data['font_data']),
                        'form'                  => null,
                        'js_variables_date'     => null
                    )
                );
                $result = json_encode($result);
                echo $result;
            }
            else
            {
                $this->layout = 'clickbuilder';
                $this->pageTitle = 'ClickBuilder';

                $user = User::model()->findByPk(Yii::app()->user->id);
                $user_subdomain = $user['username'];

                //list($controller) = Yii::app()->createController('clickbuilder/templates');
                $template_path = $this->getTemplatePath($template->name);

                $this->template = array(
                    'service_integration'   => $data['service_integration'],
                    'service_list'          => $data['service_list'],
                    'page_url'              => $data->idVariation['url'],
                    //'lp_publish_modal_url'  => 'index.php?r=clickbuilder/landingpages/publish/' .
                    'lp_publish_modal_url'  => '/clickbuilder/landingpages/publish/' .
                        '?id=' . $data['id_variation'] .
                        '&page_name=' . $data->idVariation['url'] .
                        '&id_variation=' . $data['id'] .
                        '&host=' . $_SERVER['HTTP_HOST'] .
                        '&user_subdomain=' . $user_subdomain .
                        '&mv_id=' . $id,
                    'page_title'            => $data->idVariation['name'],
                    'page_slug'             => $data->idVariation['url'],
                    'meta_title'            => $data['title'],
                    'meta_description'      => $data['description'],
                    'meta_keywords'         => $data['keywords'],
                    'user_head_code'        => $data['user_head_code'],
                    'user_analytics_code'   => $data['user_analytics_code'],
                );

                $this->render('view', array(
                    'template_id'       => $data['id_template'],
                    'template_name'     => $data->idVariation['name'],
                    'template_file'     => $template_path,
                    'edit'              => 'true'
                ));
            }
        }
        else
            throw new CHttpException(404, Yii::t('exceptions', 'Request is not valid.'));
    }


    /**
     * Publish template
     */
    public function actionPublish()
    {
        $this->pageTitle = 'Publish';
        $this->layout = false;
        $this->render('publish');
    }


    /**
     * Preview template
     *
     * @param $id - ID page
     */
    public function actionPreview($id)
    {
        $model = UserVariations::model()->with('idVariation')->findByPk($id);
        $template = Templates::model()->findByPk($model['id_template']);

        $data = $this->getPageHtmlCode($id, $template['name']);

        $this->layout = false;
        $this->render('preview', array(
            'style' => $data['style'],
            'fonts' => $data['fonts'],
            'body'  => $data['body'],
            'title' => isset($model['title']) ? ($model['title']) : ($template['title']),
            'meta_description' => isset($model['description']) ? ($model['description']) : ($template['meta_description']),
            'meta_keywords' => isset($model['keywords']) ? ($model['keywords']) : ($template['meta_keywords']),
            'user_head_code' => isset($model['user_head_code']) ? ($model['user_head_code']) : '',
            'user_analytics_code' => isset($model['user_analytics_code']) ? ($model['user_analytics_code']) : '',
        ));
    }


    /**
     * Load all images on generate builder template
     */
    public function actionLoadImages()
    {
        $images = UserImages::model()->findAll('user_id = :userID', array(':userID' => Yii::app()->user->id));
        $images_array = array();
        $i = 0;
        foreach ($images as $image)
        {
            $images_array[$i]['id']             = $image['id'];
            $images_array[$i]['name']           = $image['name'];
            $images_array[$i]['hash_file']      = $image['hash_file'];
            $images_array[$i]['original_url']   = Yii::app()->params['upload_folder'] . '/' . Yii::app()->user->id . '/original/' . $image['name'];
            $images_array[$i]['thumbnail_url']  = Yii::app()->params['upload_folder'] . '/' . Yii::app()->user->id . '/mini/' . $image['name'];
            $i++;
        }

        $result = array(
            'status'    => 200,
            'body'      => array(
                'has_more'  => false,
                'cstr'      => '',
                'images'    => $images_array
            )
        );
        $result = json_encode($result);
        echo $result;
    }


    /**
     * LOAD image file
     *
     * @return bool
     */
    public function actionUploadSimple()
    {
        // Config
        $thumbnail_width = Yii::app()->params['image_thumbnail_width'];
        $image_allow_ext = array('image/png', 'image/gif', 'image/jpeg');

        if (isset($_FILES['file']) === true)
        {
            // Info File
            $filename = $_FILES['file']['name'];
            $file_tmp_name = $_FILES['file']['tmp_name'];
            $file_size = $_FILES['file']['size'];
            // Current Iser ID
            $user_id = Yii::app()->user->id;
            // Folder
            $upload_folder = Yii::app()->params['upload_folder'];

            // Check file type
            if (!in_array($_FILES['file']['type'], $image_allow_ext))
            {
                $error = array(
                    'status' => 'error',
                    'message' => Yii::t('error', 'Error in the file extension.')
                );
                echo json_encode($error);
                Yii::app()->end();
            }

            // check file size
            if ($file_size > Yii::app()->params['image_limit_file_size'])
            {
                $error = array(
                    'status' => 'error',
                    'message' => Yii::t('error', 'File size error.')
                );
                echo json_encode($error);
                Yii::app()->end();
            }

            // hash file
            $md5_filename = md5_file($file_tmp_name);

            // check image on user
            if ($md5_filename)
            {
                $check_image = UserImages::model()->find('hash_file = :hash AND user_id = :user_id', array(':hash' => $md5_filename, ':user_id' => $user_id));

                if ($check_image)
                {
                    $error = array(
                        'status' => 'error',
                        'message' => Yii::t('error', 'The file already exists.')
                    );
                    echo json_encode($error);
                    Yii::app()->end();
                }
            }

            // get extension
            $extension = end(explode(".", $filename));

            $uploadedfile = $file_tmp_name;

            // Image Sampled
            if ($extension == "jpg" || $extension == "jpeg" ) {
                $uploadedfile = $file_tmp_name;
                $src = imagecreatefromjpeg($uploadedfile);
            } else if ($extension == "png") {
                $uploadedfile = $file_tmp_name;
                $src = imagecreatefrompng($uploadedfile);
            } else {
                $src = imagecreatefromgif($uploadedfile);
            }
            list($width, $height) = getimagesize($uploadedfile);
            $newwidth = $thumbnail_width;
            $newheight = ($height / $width) * $newwidth;
            $tmp = imagecreatetruecolor($newwidth, $newheight);
            imagecopyresampled($tmp, $src, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

            //create folders
            if (!file_exists($upload_folder . '/' . $user_id))
            {
                mkdir($upload_folder . '/' . $user_id, 0777);
                mkdir($upload_folder . '/' . $user_id . '/mini', 0777);
                mkdir($upload_folder . '/' . $user_id . '/original', 0777);
            }


            // CREATE THUMBNAIL image
            $filename_tmp = $upload_folder . '/' . $user_id . '/mini/' . $filename;
            imagejpeg($tmp, $filename_tmp, 100);
            imagedestroy($src);
            imagedestroy($tmp);


            // CREATE ORIGINAl image
            $uploadfile = $upload_folder . '/' . $user_id . '/original/' . $filename;
            if (!move_uploaded_file($file_tmp_name, $uploadfile))
            {
                $error = array(
                    'status' => 'error',
                    'message' => Yii::t('app', 'File don`t upload.')
                );
                echo json_encode($error);
                Yii::app()->end();
            }

            //unlink($file_tmp_name);

            // Model
            $model = new UserImages;
            $model->user_id         = $user_id;
            $model->name            = Rus2LatHelper::rus2lat($filename);
            $model->hash_file       = $md5_filename;

            // Save
            if ($model->save())
            {
                $this->layout = false;

                if ($_GET['render'] == 'image')
                {
                    $output = $this->renderPartial('image', array(
                        'id'                => $model->id,
                        'filename'          => Rus2LatHelper::rus2lat($filename),
                        'original_url'      => '../../' . $upload_folder . '/' . $user_id . '/original/' . $filename,
                        'thumbnail_url'     => $upload_folder . '/' . $user_id . '/mini/' . $filename,
                    ), true);
                }

                if ($_GET['render'] == 'ajax_image')
                {
                    $output = $this->renderPartial('ajax_image', array(
                        'id'                => $model->id,
                        'filename'          => Rus2LatHelper::rus2lat($filename),
                        'thumbnail_url'     => $upload_folder . '/' . $user_id . '/mini/' . $filename,
                    ), true);
                }

                $result = array(
                    'status' => 'OK',
                    'content' => $output
                );
                echo json_encode($result);
            }
        }
        else
        {
            $error = array(
                'status' => 'error',
                'message' => Yii::t('error', 'The file not found.')
            );
            echo json_encode($error);
            Yii::app()->end();
        }
    }



    /**
     * Common page HTML code
     *
     * @param $id - ID variation template (ID field on table user_variations)
     * @param $template_origin_name - Origin template name on table `templates`
     * @return array - data on template
     */
    public function getPageHtmlCode($id, $template_origin_name)
    {
        $data = UserVariations::model()->with('idVariation')->findByPk($id);
        $template = Templates::model()->findByPk($data['id_template']);

        $file = YiiBase::getPathOfAlias('webroot') . $this->getTemplatePath($template['name']);

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTMLFile($file);
        libxml_clear_errors();

        $el_body = $doc->getElementsByTagName('body');
        $el_script = $doc->getElementsByTagName('style');

        $array_changed = array();
        $array_changed_img = array();
        $array_changed_p = array();
        $i = 0;
        $i_p = 0;

        $_data = json_decode($data['data']);

        $img    = $doc->getElementsByTagName('img');
        $p      = $doc->getElementsByTagName('p');
        $input  = $doc->getElementsByTagName('input');
        $span   = $doc->getElementsByTagName('span');
        $form   = $doc->getElementsByTagName('form');

        // SEO
        $title  = $doc->getElementsByTagName('title');
        $meta   = $doc->getElementsByTagName('meta');

        foreach ($title as $element)
        {
            $element->setAttribute('value', 'test');
        }

        $xpath = new DomXpath($doc);

        $hidden_member = $xpath->query('//input[@name="member"]/@value');
        foreach ($hidden_member as $rowNode) {
            if (empty($rowNode->nodeValue)) {
                $user_id = $data->idVariation['id_user'];
                $rowNode->nodeValue = $user_id;
            }
        }

        $hidden_landing_page = $xpath->query('//input[@name="landing_page"]/@value');
        foreach ($hidden_landing_page as $rowNode) {
            if (empty($rowNode->nodeValue)) {
                $landing_page = $data['id_template'];
                $rowNode->nodeValue = $landing_page;
            }
        }

        $hidden_variation_id = $xpath->query('//input[@name="variation_id"]/@value');
        foreach ($hidden_variation_id as $rowNode) {
            if (empty($rowNode->nodeValue)) {
                $rowNode->nodeValue = $id;
            }
        }

        $lb_id = 'lb-id';

        foreach ($_data as $value)
        {
            // Parse IMAGE
            if ($value->type == 'image')
            {
                foreach($img as $element)
                {
                    if ($element->getAttribute('data-lb-id') == $value->$lb_id)
                    {
                        // if hidden
                        if ($value->removed == 1)
                        {
                            $element->parentNode->removeChild($element);
                        }
                        else
                        {
                            if (!empty($value->url))
                            {
                                if ($element->getAttribute('src') != $value->url)
                                {
                                    $i++;
                                    $array_changed[$i]['original'] = $element->getAttribute('src');
                                    $array_changed[$i]['new'] = $value->url;
                                }
                                else
                                {
                                    $i++;
                                    $array_changed[$i]['original'] = $element->getAttribute('src');
                                    $array_changed[$i]['new'] = '/' . Yii::app()->params['landing_pages_folder'] .  '/' . $template_origin_name . '/' . $value->url;
                                }
                            }
                        }
                    }
                }
            }

            // Parse TEXT
            if ($value->type == 'text')
            {
                foreach ($p as $element)
                {
                    if ($element->getAttribute('data-lb-id') == $value->$lb_id)
                    {
                        // if hidden
                        if ($value->removed == 1)
                        {
                            $element->parentNode->removeChild($element);
                        }
                        else
                        {
                            if (!empty($value->text))
                            {
                                if ($element->hasChildNodes())
                                {
                                    foreach ($element->childNodes as $c)
                                    {
                                        $_html = $c->ownerDocument->saveXML($c);
                                        if ($_html != $value->text)
                                        {
                                            $i++;
                                            $array_changed[$i]['original'] = $_html;
                                            $array_changed[$i]['new'] = $value->text;
                                        }
                                    }
                                }
                                else
                                {
                                    if ($element->nodeValue != $value->text)
                                    {
                                        $i++;
                                        $array_changed[$i]['original'] = $element->nodeValue;
                                        $array_changed[$i]['new'] = $value->text;
                                    }
                                }
                            }
                        }
                    }
                }

                foreach ($span as $element)
                {
                    if ($element->getAttribute('data-lb-id') == $value->$lb_id)
                    {
                        // if hidden
                        if ($value->removed == 1)
                        {
                            $element->parentNode->removeChild($element);
                        }
                        else
                        {
                            if (!empty($value->text))
                            {
                                if ($element->nodeValue != $value->text)
                                {
                                    $i++;
                                    $array_changed[$i]['original'] = $element->nodeValue;
                                    $array_changed[$i]['new'] = $value->text;
                                }
                            }
                        }
                    }
                }

            }

            // Parse TEXT INPUT
            if ($value->type == 'text_input')
            {
                foreach ($input as $element)
                {
                    if ($element->getAttribute('data-lb-id') == $value->$lb_id)
                    {
                        // if hidden
                        if ($value->removed == 1)
                        {
                            $element->parentNode->removeChild($element);
                        }
                        else
                        {
                            if (!empty($value->title))
                            {
                                if ($element->getAttribute('value') != $value->title)
                                {
                                    $i++;
                                    $array_changed[$i]['original'] = $element->getAttribute('value');
                                    $array_changed[$i]['new'] = $value->title;
                                }
                            }
                        }
                    }
                }
            }
        }

        $body = $doc->saveHTML($el_body->item(0));

        foreach ($array_changed as $key => $item)
        {
            $body = str_replace($item['original'], $item['new'], $body);
        }

        $style = strip_tags($doc->saveHTML($el_script->item(0)));

        // Parse COLOR
        $color = json_decode($data['color_data']);
        foreach ($color as $key => $value) {
            $style = str_replace($value->color, $value->value, $style);
        }

        // Parse FONT
        $font = json_decode($data['font_data']);
        foreach ($font as $key => $value) {
            $style = str_replace($value->font, $value->value, $style);
            $my_font = $value->value;
        }

        $_style = "<style type='text/css'>";
        $_style .= $style;
        $_style .= "</style>";

        $_fonts = '<link href="https://fonts.googleapis.com/css?family=' . $my_font . '" rel="stylesheet" type="text/css">';

        $array_html = array(
            'style' => $_style,
            'fonts' => $_fonts,
            'body'  => $body
        );

        return $array_html;
    }


    /**
     * get absolute path on template file
     *
     * @param $template_slug_name - slug name template
     * @return string - absolute path
     * @throws CHttpException
     */
    public function getTemplatePath($template_slug_name)
    {
        $folder = DS . Yii::app()->params['landing_pages_folder'];

        if (!file_exists(YiiBase::getPathOfAlias('webroot') . $folder)) {
            throw new CHttpException(404, 'Base top folder ' . $folder . ' not exists' );
        } else {
            if (!file_exists(YiiBase::getPathOfAlias('webroot') . $folder . DS . $template_slug_name)) {
                throw new CHttpException(404, 'Children folder ' . $folder . DS . $template_slug_name . ' not exists' );
            } else {
                if (!file_exists(YiiBase::getPathOfAlias('webroot') . $folder . DS . $template_slug_name . DS . 'index.phtml')) {
                    throw new CHttpException(404, 'File ' . $folder . DS . $template_slug_name . DS . 'index.phtml' . ' not exists' );
                } else {
                    return $folder . DS . $template_slug_name . DS .'index.phtml';
                }
            }
        }
    }


    /**
     * VIEW user templates on global url on internet
     */
    public function actionPromo()
    {
        preg_match_all('/(\w+).' . Yii::app()->params['site_url'] . '/', $_SERVER['HTTP_HOST'], $matches);
        if (isset($matches[1][0]) and !empty($matches[1][0]))
        {
            $subdomain = $matches[1][0];

            if (!empty($_GET['action']))
            {
                $modelUser = User::model()->find('username = :username', array(':username' => $subdomain));
                $user_id = $modelUser->id;

                $modelVariations = Variations::model()->with('userVariations')->find('id_user = :id_user and url = :url', array(':id_user' => $user_id, ':url' => $_GET['action']));

                $modelTemplates = Templates::model()->find('id = :id_template', array(':id_template' => $modelVariations->userVariations[0]['id_template']));

                $p = Yii::app()->createController('clickbuilder/landingpages');

                $data = $p[0]->getPageHtmlCode($modelVariations->userVariations[0]['id'], $modelTemplates->name);

                $this->layout = false;
                $this->render('preview', array(
                    'style' => $data['style'],
                    'fonts' => $data['fonts'],
                    'body'  => $data['body'],
                    'title' => isset($model['title']) ? ($model['title']) : ($template['title']),
                    'meta_description' => isset($model['description']) ? ($model['description']) : ($template['meta_description']),
                    'meta_keywords' => isset($model['keywords']) ? ($model['keywords']) : ($template['meta_keywords']),
                    'user_head_code' => isset($model['user_head_code']) ? ($model['user_head_code']) : '',
                    'user_analytics_code' => isset($model['user_analytics_code']) ? ($model['user_analytics_code']) : '',
                ));

            }
        }
    }
}