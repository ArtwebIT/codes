<?php

class C_Image
{
    /**
     * генерация уникального имени файла
     *
     * @param $name - file name
     * @return string
     */
    public static function uniqName($name)
    {
        $imageFileName = substr(md5(uniqid(rand(), true)), 0, rand(7, 13)).preg_replace('/(^.*)(\.)/','$2', $name);
        return $imageFileName;
    }


    /**
     * Upload images
     *
     * @param $obj - file object
     * @param $imageFileName file name
     */
    public static function saveImage($obj, $folder, $imageFileName)
    {
        $obj->saveAs($folder . '/' . $imageFileName);
        $image = Yii::app()->image->load($folder . '/' . $imageFileName);
        $image->resize(Yii::app()->params['thumbnails']['l_width'], Yii::app()->params['thumbnails']['l_height'], Image::NONE);
        $image->save($folder . '/' . $imageFileName);
    }
}