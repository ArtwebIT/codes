<?php

class TemplatesController extends Controller
{
    // for layout
    public $template;

    public $layout = '//layouts/backend';
    public $defaultAction = 'index';


    public function init()
    {
        if (Yii::app()->user->isGuest)
            $this->redirect('/login');

        $this->pageTitle = Yii::t('app', 'Templates');
    }


    /*
     * List All templates
     */
    public function actionIndex()
    {
        $categories = Categories::model()->FindAll();
        $templates = Templates::model()->FindAll();

        $this->render('index', array(
            'categories'    => $categories,
            'templates'     => $templates,
            'active_id'     => 0
        ));
    }


    /**
     * List template by Category
     *
     * @param $id - ID category
     */
    public function actionCategory($id)
    {
        $categories = Categories::model()->FindAll();
        $all_templates = Templates::model()->FindAll();
        $my_templates = Categories::model()->with('my_templates')->findByPk($id);

        $this->render('index', array(
            'categories'    => $categories,
            'all_templates' => $all_templates,
            'my_templates'  => $my_templates,
            'active_id'     => $id
        ));
    }


    /**
     * CSS Parser, ajax method
     * PARSE COLORS
     * PARSE FONTS
     */
    protected function ParseStyleTag($id)
    {
        $model = Templates::model()->findByPk($id);

        $parse_html = new SimpleHtmlDom();
        $parse_html->load_file(YiiBase::getPathOfAlias('webroot') . '/' . Yii::app()->params['landing_pages_folder'] . '/' . $model['name'] . '/' . Yii::app()->params['default_index_file']);

        foreach ($parse_html->find('style') as $element)
            $style = $element;

        if ($style)
            preg_match_all('/(<style[\s]?(type="text\/css")?>)([\s\S]*)<\/style>/', $style, $matches);

        if (!empty($matches[3][0]))
        {
            $oCSS = new CssParse();
            $oCSS->parse_css(trim($matches[3][0]));
            $css = $oCSS->get_css();
        }

        $i=0;
        $array_colors = array();
        $array_fonts = array();

        $uniq_color = array();
        $uniq_fonts = array();

        foreach ($css as $selectors => $selector)
        {
            foreach ($selector as $attribute => $value)
            {
                // #cb06gg
                preg_match_all('/[#]\w{3,6}/', $value, $match);

                if (!empty($match) and count($match) > 0)
                {
                    foreach ($match as $item)
                    {
                        if (is_array($item))
                        {
                            if (!empty($item) and count($item) > 0)
                            {
                                foreach ($item as $value)
                                {
                                    $i++;

                                    if (!in_array($value, $uniq_color))
                                    {
                                        $sel_name = explode(",", $attribute);
                                        $sel_name = array_pop($sel_name);
                                        $sel_name = explode(' ', $sel_name);
                                        $sel_name = array_pop($sel_name);
                                        $sel_name = str_replace('.', '', $sel_name);
                                        $top_sel_name = str_replace('#', '', $sel_name);

                                        $array_colors[$i]['name'] = ucfirst($top_sel_name) . ' ' . ucfirst($sel_name);
                                        $array_colors[$i]['value'] = $value;

                                        array_push($uniq_color, $value);
                                    }
                                }
                            }
                        }
                    }
                }

                if (($attribute == 'font-family') and (!empty($value)))
                {
                    $font = explode(',', $value);
                    $font = array_shift($font);
                    $font = str_replace("'", '', $font);
                    $font = str_replace('"', '', $font);

                    if (!in_array($font, $uniq_fonts)) {
                        $array_fonts[$i]['name'] = $font;
                        array_push($uniq_fonts, $font);
                    }
                }
            }
        }

        return array(
            'colors'    => $array_colors,
            'fonts'     => $array_fonts
        );
    }


    /**
     * Upload file
     */
    public function actionUpload()
    {
        $this->pageTitle = 'Upload Template';

        $model = new Templates('upload');
        if (isset($_POST['Templates']))
        {
            $model->attributes = $_POST['Templates'];

            if ($_FILES['file'] == true)
            {
                $ZipUploadAndExtract = $this->ZipUploadAndExtract($_FILES);
                if ($ZipUploadAndExtract == true)
                {
                    if ($model->save()) {
                        Yii::app()->user->setFlash('success', Yii::t('messages', 'Template load'));
                        $this->redirect(array('edit', 'id' => $model->id));
                    }
                }
                else
                    $this->refresh();
            } else {
                Yii::app()->user->setFlash('error', Yii::t('exceptions', 'File not found.'));
                $this->refresh();
            }
        }

        $this->render('upload', array(
            'model' => $model,
        ));
    }


    /**
     * View template
     *
     * @param $id - ID saved template
     */
    public function actionEdit($id)
    {
        if (isset($_POST['method']) and ($_POST['method'] == 'ajax_parse_style'))
        {
            $array_colors_fonts = $this->ParseStyleTag($_POST['id']);
            echo json_encode($array_colors_fonts);
            Yii::app()->end();
        }
        else
        {
            $this->pageTitle = Yii::t('app', 'View template');

            $clientScript = Yii::app()->clientScript;
            $theme = Yii::app()->theme->baseUrl;
            // JS
            $clientScript->registerScriptFile($theme . '/js/templates.upload.js');
            // CSS
            $clientScript->registerCssFile($theme . '/css/templates.upload.css');

            $model = Templates::model()->findByPk($id);

            $file = '/' . Yii::app()->params['landing_pages_folder'] . '/' . $model['name'] . '/' . Yii::app()->params['default_index_file'];

            $this->render('edit', array(
                'model' => $model,
                'file' => $file
            ));
        }
    }


    /**
     * Save template code
     *
     * @param $id - ID template
     */
    public function actionSave($id)
    {
        $html = $_POST['body'];
        $model = Templates::model()->findByPk($id);

        if (!$handle = fopen(YiiBase::getPathOfAlias('webroot') . '/landing_pages/' . $model['name'] . '/' . Yii::app()->params['default_index_file'], 'w+')) {
            echo "File not open.";
            exit;
        }
        if (fwrite($handle, $html) === FALSE) {
            echo "File don't record.";
            exit;
        }
    }


    /**
     * @param $id - ID template
     */
    public function actionDownloadIndexFile()
    {
        if (isset($_GET['id']) and (!empty($_GET['id'])))
        {
            $model = Templates::model()->findByPk($_GET['id']);
            $file = YiiBase::getPathOfAlias('webroot') . '/landing_pages/' . $model['name'] . '/' . Yii::app()->params['default_index_file'];
            if (file_exists($file))
            {
                if (ob_get_level()) {
                    ob_end_clean();
                }
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header('Content-Disposition: attachment; filename=' . basename($file));
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($file));
                readfile($file);
                exit;
            } else {
                echo 'File not found';
                Yii::app()->end();
            }
        }
    }


    /**
     * Upload and extract zip file
     *
     * @param $file -  it's $_FILES
     * @return array|bool
     */
    public function ZipUploadAndExtract($file)
    {
        $filename = $file['file']['name'];
        $file_tmp_name = $file['file']['tmp_name'];
        $uploadfile = Yii::app()->params['landing_pages_folder']  . '/'  . $filename;

        // get extension
        $extension = end(explode(".", $filename));

        // Check .ZIP extensions
        if (!in_array($extension, Yii::app()->params['zip_allow_ext'])) {
            Yii::app()->user->setFlash('error', Yii::t('messages', 'File not ZIP archive.'));
            return false;
        }

        // upload zip archive
        if (!move_uploaded_file($file_tmp_name, $uploadfile)) {
            Yii::app()->user->setFlash('error', Yii::t('messages', 'File not upload.'));
            return false;
        }

        $zip = new ZipArchive();
        if (!$zip->open(Yii::app()->params['landing_pages_folder'] . '/' . $filename)) {
            Yii::app()->user->setFlash('error', Yii::t('messages', 'Archive not open.'));
            return false;
        }

        $numFiles = $zip->numFiles;
        $zip_names = array();

        // set array of name files
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $obj = $zip->statIndex($i);
            preg_match_all('/(\w+[.-])+\w+/' , $obj['name'], $matches);
            if (isset($matches[0][0]) and !empty($matches[0][0]))
                array_push($zip_names, $matches[0][0]);
        }

        // check required files
        foreach (Yii::app()->params['required_template_file'] as $file) {
            if (!in_array($file, $zip_names)) {
                Yii::app()->user->setFlash('error', Yii::t('messages', 'File {file} not found.', array('{file}' => $file)));
                return false;
            }
        }

        // extract zip file
        $zip->extractTo(Yii::app()->params['landing_pages_folder'] . '/');
        $zip->close();

        //delete file
        unlink(Yii::app()->params['landing_pages_folder'] . '/' . $filename);

        return true;
    }
}