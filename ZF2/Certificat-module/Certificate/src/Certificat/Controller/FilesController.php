<?php

/**
 * Controller class for file uploads and downloads
 */

namespace Certificat\Controller;

use Zend\View\Model\JsonModel;
use SNJ\Model\File;

/**
 * Controller class
 */
class FilesController extends BaseController
{

    /**
     * Action for uploading organization logo
     * 
     * @return \Zend\View\Model\JsonModel
     */
    public function uploadLogoAction()
    {
        $identity = $this->getAuthService()->getIdentity();
        $user_id = (int) $identity['id'];
        $organization_id = (int) $identity['organization_id'];

        $file = new File();
        $file->user_id = $user_id;
        $file->organization_id = $organization_id;
        $file->name = $this->getTranslator()->translate('Logo');
        $file->description = $this->getTranslator()->translate('Organization logo');

        return $this->_imageUpload($file);
    }

    /**
     * Action for uploading user avatar
     * 
     * @return \Zend\View\Model\JsonModel
     */
    public function uploadAvatarAction()
    {
        $identity = $this->getAuthService()->getIdentity();
        $user_id = (int) $identity['id'];

        $file = new File();
        $file->user_id = $user_id;
        $file->name = $this->getTranslator()->translate('Profilfoto');
        $file->description = $this->getTranslator()->translate('Profilfoto');

        return $this->_imageUpload($file);
    }

    /**
     * File Show Action
     *
     * @return Response
     */
    public function showAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        $file = $this->getFileTable()->getFile($id);

        $size = getimagesize($file->path);
        $fp = fopen($file->path, "rb");
        if ($size && $fp) {
            header("Content-type: " . $file->type);
            \fpassthru($fp);
            exit();
        } else {
            // Error
        }
        exit();
    }

    /**
     * Image Crop Action
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function cropAction()
    {
        $errors = array();
        $img = array();
        $id = (int) $this->params()->fromRoute('id');

        $identity = $this->getAuthService()->getIdentity();
        $userId = (int) $identity['id'];

        $file = $this->getFileTable()->getFileByUser($id, $userId);

        if (strncmp($file->type, 'image', 5) !== 0) {
            $errors[] = $this->getTranslator()->translate("Die Datei ist kein Bild");
        } else {
            $sizes = getimagesize($file->path);

            if (!in_array($sizes[2], array(2, 3))) {
                $errors[] = $this->getTranslator()->translate("Die hochgeladene Datei ist nicht im JPG- oder PNG-Format.");
            }
        }

        if (empty($errors)) {
            $form = $this->getFormElementManager()->get('CropForm');
            $request = $this->getRequest();
            if ($request->isPost()) {
                $form->setData($request->getPost());

                if ($form->isValid()) {
                    $data = $form->getData();
                    $image = imagecreatetruecolor($data['w'], $data['h']);

                    switch ($sizes[2]) {
                        case 3:
                            $src_im = imagecreatefrompng($file->path);
                            break;
                        default:
                            $src_im = imagecreatefromjpeg($file->path);
                            break;
                    }
                    imagecopyresized($image, $src_im, 0, 0, $data['x1'], $data['y1'], $data['w'], $data['h'], ($data['x2'] - $data['x1']), ($data['y2'] - $data['y1']));

                    imagejpeg($image, $file->path);
                    imagedestroy($image);

                    if ($file->organization_id) {
                        $organization = $this->getOrganizationTable()->getOrganization($file->organization_id);

                        // Delete old logo if exist
                        if ($organization->logo_id) {
                            $this->getOrganizationTable()->deleteOrganizationLogo($organization->id);
                            $this->getFileTable()->deleteFile($organization->logo_id);
                        }

                        $this->getOrganizationTable()->addOrganizationLogo($file);
                    } else {
                        $user = $this->getUserTable()->getUser($file->user_id);

                        // Delete old photo if exist
                        if ($user->photo_id) {
                            $this->getUserTable()->deletePhotoForUser($file->user_id);
                            $this->getFileTable()->deleteFile($user->photo_id, $userId);
                        }

                        $this->getUserTable()->addUserPhoto($file);
                    }

                    $img['src'] = $this->url()->fromRoute('ce/files', array('action' => 'show', 'id' => $file->id)) . '?' . time();
                }
            }
        }

        return new JsonModel(array(
            'success' => empty($errors),
            'errors' => $errors,
            'img' => $img,
        ));
    }

    /**
     * Uploade the image
     * 
     * @param \SNJ\Model\File $file
     * @return \Zend\View\Model\JsonModel
     */
    private function _imageUpload(File $file)
    {
        $img = array();
        $errors = array();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $files = $request->getFiles();
            if (!isset($files['file'])) {
                $errors[] = $this->getTranslator()->translate('Bitte wähle eine Datei aus.');
            } else if ($files['file']['error'] != UPLOAD_ERR_OK) {
                $errors[] = $this->getTranslator()->translate('Diese Datei überschreitet die maximale Dateigröße.');
            } else if (!preg_match('/\.(png|jpg|jpeg)$/i', $files['file']['name'])) {
                $errors[] = $this->getTranslator()->translate('Bitte wähle ein Foto im Format jpg oder png.');
            } else {
                $image = $files['file'];
                $sizes = getimagesize($image['tmp_name']);
                $img['width'] = $sizes[0];
                $img['height'] = $sizes[1];

                if ($img['height'] < 150 || $img['width'] < 150) {
                    $errors[] = $this->getTranslator()->translate("Die hochgeladene Datei ist zu klein.");
                } elseif (!in_array($sizes[2], array(2, 3))) {
                    $errors[] = $this->getTranslator()->translate("Die hochgeladene Datei ist nicht im JPG- oder PNG-Format.");
                } else {
                    $filter = new \Zend\Filter\File\RenameUpload(array(
                        'target' => './data/upload/img',
                        'randomize' => true,
                        'use_upload_extension' => true,
                    ));
                    $filtered = $filter->filter($image);

                    $file->path = $filtered['tmp_name'];
                    $file->type = $sizes['mime'];
                    $file->size = $image['size'];
                    $file->original = $image['name'];

                    $img['id'] = $this->getFileTable()->saveFile($file, 'photo');
                    $img['src'] = $this->url()->fromRoute('ce/files', array('action' => 'show', 'id' => $img['id']));
                }
            }
        }

        return new JsonModel(array(
            'success' => empty($errors),
            'errors' => $errors,
            'img' => $img
        ));
    }

}
