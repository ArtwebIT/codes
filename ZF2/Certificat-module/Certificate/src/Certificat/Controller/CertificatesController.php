<?php

/**
 * Controller class for manage certificates
 */

namespace Certificat\Controller;

use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Certificat\Model\Certificate;
use Zend\I18n\Filter\Alnum;
use SNJ\Util\LanguageHelper;

/**
 * Controller class
 */
class CertificatesController extends BaseController
{

    /**
     * Default Action. Shows certificates.
     *
     */
    public function indexAction()
    {
        $identity = $this->getAuthService()->getIdentity();
        $organization_id = (int) $identity['organization_id'];

        return array(
            'activeCertificates' => $this->getCertificateTable()->getByOrganizationId($organization_id)->toArray(),
            'acrchivedCertificates' => $this->getCertificateTable()->getByOrganizationId($organization_id, true)->toArray(),
            'activeTab' => $this->params()->fromQuery('tab', 'active')
        );
    }

    /**
     * Add new certificate
     *
     */
    public function newAction()
    {
        $view = new ViewModel($this->_getForm());

        // Set render script
        $view->setTemplate('certificat/certificates/overview-editable');
        return $view;
    }

    /**
     * Overview certificate
     *
     */
    public function overviewAction()
    {
        $vars = $this->_getForm();
        $templateName = ($vars['status'] == Certificate::STATUS_DRAFT) ? 'overview-editable' : 'overview-readonly';
        $view = new ViewModel($vars);

        // Set render script
        $view->setTemplate("certificat/certificates/$templateName");
        return $view;
    }

    /**
     * Get edit or add form. If isset id then edit
     *
     * @return array View variables
     */
    private function _getForm()
    {
        if ($id = $this->params()->fromRoute('id')) {
            $certificate = $this->getCertificateTable()->getCertificate($id);
            $this->_checkCertificatePermission($certificate);
            $participants = $this->getCertificateParticipantTable()->getParticipantsByCertificateId($certificate->id)->toArray();
            $formAction = $this->url()->fromRoute('ce/certificates', array('action' => 'save', 'id' => $certificate->id));
            $title = $certificate->name;
            $status = $certificate->status;
        } else {
            $certificate = null;
            $participants = array();
            $formAction = $this->url()->fromRoute('ce/certificates', array('action' => 'save'));
            $title = $this->getTranslator()->translate('Add certificate');
            $status = Certificate::STATUS_DRAFT;
        }
        
        // Get template options for current organization
        $identity = $this->getAuthService()->getIdentity();
        $organization_id = (int) $identity['organization_id'];
        $templateOptions = $this->getTemplateTable()->getTemplatesForSelect($organization_id);
        
        $template = null;
        if ($certificate) {
            $template = $this->getTemplateTable()->getTemplate($certificate->template_id);
        } else if ($templateOptions) {
            // First template in select list
            $template = $this->getTemplateTable()->getTemplate(current(array_keys($templateOptions)));
        }

        // Get and prepare form
        $form = $this->getFormElementManager()->get('CertificateForm')
                ->setAttribute('action', $formAction)
                ->setTemplateOptions($templateOptions);

        // Populate login form with submitted data or from db.
        if (isset($_SESSION['certificate_form_data'])) {
            $form->setData($_SESSION['certificate_form_data']);
            unset($_SESSION['certificate_form_data']);
        } elseif (isset($certificate)) {
            $form->setData($certificate->getArrayCopy());
        }

        return array(
            'form' => $form,
            'title' => $title,
            'certificate' => $certificate,
            'participants' => $participants,
            'status' => $status,
            'template' => $template
        );
    }

    /**
     * Save the certificate.
     *
     */
    public function saveAction()
    {
        if ($id = $this->params()->fromRoute('id')) {
            $certificate = $this->getCertificateTable()->getCertificate($id);
            $backUrlParams = array('action' => 'overview', 'id' => $certificate->id);
        } else {
            $certificate = new Certificate();
            $backUrlParams = array('action' => 'new');
        }
        $this->_checkCertificatePermission($certificate);

        if (isset($certificate->id) && $certificate->status != Certificate::STATUS_DRAFT) {
            throw new \Exception("Only certificates with status `draft` can be edited.");
        }

        // Get template options for current organization
        $identity = $this->getAuthService()->getIdentity();
        $organization_id = (int) $identity['organization_id'];
        $templateOptions = $this->getTemplateTable()->getTemplatesForSelect($organization_id);

        // Get and prepare form
        $form = $this->getFormElementManager()->get('CertificateForm')
                ->setTemplateOptions($templateOptions);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();

            // If new certificate then set the organization_id and user_id
            if (is_null($certificate->id)) {
                $data['user_id'] = (int) $identity['id'];
                $data['organization_id'] = (int) $identity['organization_id'];
            }

            if (isset($data['complete'])) {
                $data['status'] = Certificate::STATUS_COMPLETED;
            } else {
                $data['status'] = Certificate::STATUS_DRAFT;
            }

            $form->setData($data);
            $form->setInputFilter($certificate->getInputFilter());

            if ($form->isValid()) {
                $certificate->exchangeArray($form->getData());
                try {
                    $certificate->id = $this->getCertificateTable()->saveCertificate($certificate);

                    // Save certificate participants
                    $participants = is_array($data['participant']) ? $data['participant'] : array();
                    $this->getCertificateParticipantTable()->syncParticipantsByCertificateId($certificate->id, $participants);

                    // Generate pdf for certificate participants
                    if ($certificate->status == Certificate::STATUS_COMPLETED) {
                        $participants = $this->getCertificateParticipantTable()->getParticipantsByCertificateId($certificate->id)->toArray();
                        if (count($participants) > 0) {
                            $certificateDir = "data/certificates/{$certificate->id}";
                            // Make directory for certificates
                            if (!is_dir($certificateDir)) {
                                mkdir($certificateDir);
                            }

                            $template = (array) $this->getTemplateTable()->getTemplate($certificate->template_id);

                            # TODO Add a job for gearman-server
                            $update = array();
                            $filter = new Alnum();
                            foreach ($participants as $participant) {
                                $fullName = $filter->filter($participant['first_name']) . '_' . $filter->filter($participant['last_name']);
                                $saveFileName = $template['type'] . '_' . $fullName . '.pdf';
                                $saveFilePath = "{$certificateDir}/{$participant['id']}/{$saveFileName}";
                                if ($this->generateParticipantCertificate($certificate->getArrayCopy(), $participant, $saveFilePath)) {
                                    $update[] = array(
                                        'id' => $participant['id'],
                                        'attachment' => $saveFileName
                                    );
                                }
                            }
                            if (count($update) > 0) {
                                $this->getCertificateParticipantTable()->multiUpdateParticipants($update, 'id', $certificate->id);
                            }
                        }
                    }

                    $message = $this->getTranslator()->translate('Certificate was successfully saved.');
                    $this->flashMessenger()->addSuccessMessage($message);
                    return $this->redirect()->toRoute('ce/certificates');
                } catch (\Exception $ex) {
                    $this->flashMessenger()->addErrorMessage($ex->getMessage());
                }
            } else {
                foreach ($form->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage(implode('<br>', $message));
                }
            }

            $_SESSION['certificate_form_data'] = $data;

            return $this->redirect()->toRoute('ce/certificates', $backUrlParams);
        }
    }

    /**
     * Delete certificate
     *
     */
    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');
        $certificate = $this->getCertificateTable()->getCertificate($id);
        $this->_checkCertificatePermission($certificate);

        if ($certificate->status != Certificate::STATUS_DRAFT) {
            throw new \Exception("Only certificates with status `" . Certificate::STATUS_DRAFT . "` can be deleted.");
        }

        try {
            $this->getCertificateTable()->deleteCertificate($certificate->id);
            $message = $this->getTranslator()->translate('Certificate was successfully deleted.');
            $this->flashMessenger()->addSuccessMessage($message);
            return $this->redirect()->toRoute('ce/certificates');
        } catch (\Exception $ex) {
            $this->flashMessenger()->addErrorMessage($ex->getMessage());
            return $this->redirect()->toRoute('ce/certificates', array('action' => 'overview', 'id' => $certificate->id));
        }
    }

    /**
     * Set fot certificate status = `archived`
     *
     */
    public function archiveAction()
    {
        $id = $this->params()->fromRoute('id');
        $certificate = $this->getCertificateTable()->getCertificate($id);
        $this->_checkCertificatePermission($certificate);

        if ($certificate->status != Certificate::STATUS_COMPLETED) {
            throw new \Exception("Only certificates with status `" . Certificate::STATUS_COMPLETED . "` can be archived.");
        }

        try {
            $this->getCertificateTable()->archiveCertificate($certificate);
            $message = $this->getTranslator()->translate('Certificate was successfully archived.');
            $this->flashMessenger()->addSuccessMessage($message);
            return $this->redirect()->toRoute('ce/certificates');
        } catch (\Exception $ex) {
            $this->flashMessenger()->addErrorMessage($ex->getMessage());
            return $this->redirect()->toRoute('ce/certificates', array('action' => 'overview', 'id' => $certificate->id));
        }
    }

    /**
     * Transform csv to json for import file
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function csvToJsonAction()
    {
        $data = array();
        $header = null;
        $errorCount = 0;
        $request = $this->getRequest();
        $files = $request->getFiles();
        $filename = $files['file']['tmp_name'];

        if (file_exists($filename) && is_readable($filename)) {
            if (($handle = fopen($filename, 'r')) !== FALSE) {
                while (($row = fgetcsv($handle, 1000, ';')) !== FALSE) {
                    if (!$header) {
                        $header = $row;
                        continue;
                    }

                    // If isset `comment` field & email is valid
                    if (isset($row[9]) && filter_var($row[2], FILTER_VALIDATE_EMAIL)) {
                        $data[] = array(
                            'first_name' => $row[0],
                            'last_name' => $row[1],
                            'email' => $row[2],
                            'birthday' => ($row[3]) ? date('d/m/Y', strtotime($row[3])) : '',
                            'comment' => $row[9],
                        );
                    } else {
                        $errorCount++;
                    }
                }
                fclose($handle);
                unlink($filename);
            }
        }

        return new JsonModel(compact('data', 'errorCount'));
    }

    /**
     * Download existing certificate of participant. 
     *  
     */
    public function downloadAction()
    {
        $participantId = (int) $this->params()->fromRoute('id');
        $participant = $this->getCertificateParticipantTable()->getParticipant($participantId);
        $certificate = $this->getCertificateTable()->getCertificate($participant->certificate_id);
        $userRole = $this->layout()->userRole;

        if ($userRole == 'participant') {
            $identity = $this->getAuthService()->getIdentity();
            if ($participant->email != $identity['email']) {
                return $this->notFoundAction();
            }
        } else {
            $this->_checkCertificatePermission($certificate);
        }

        $fileName = $participant->attachment;
        $path = "data/certificates/{$certificate->id}/{$participant->id}/{$fileName}";

        if (!is_file($path)) {
            throw new \Exception("Certificate for participant #$participantId is not exist.");
        }

        $this->sendPdfToBrowber($path, $fileName);
    }

    /**
     * Preview certificate of participant. Data from post
     *
     */
    public function previewAction()
    {
        $rowKey = (int) $this->params()->fromRoute('id');
        $certificate = (array) $this->getRequest()->getPost();
        $participant = $certificate['participant'][$rowKey];
        unset($certificate['participant']);

        $filePath = sys_get_temp_dir() . '/certificate_preview_' . time();

        $this->generateParticipantCertificate($certificate, $participant, $filePath);

        // Generated PDF
        $pdfFilePath = $filePath . '.pdf';

        if (!is_file($pdfFilePath)) {
            throw new \Exception("Preview of participant`s certificate is not exist.");
        }
        
        $this->sendPdfToBrowber($pdfFilePath, 'certificate_preview.pdf');

        // Delete all files for this preview
        array_map("unlink", glob($filePath . "*"));
    }

    /**
     * Send file to browser
     *
     * @param string $filePath
     * @param string $fileName
     */
    protected function sendPdfToBrowber($filePath, $fileName)
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit();
    }

    /**
     * Generate PDF certificate of participant
     *
     * @param array $certificate
     * @param array $participant
     * @param string $saveFilePath
     */
    protected function generateParticipantCertificate(array $certificate, array $participant, $saveFilePath)
    {
        $template = (array) $this->getTemplateTable()->getTemplate($certificate['template_id']);
        $organization = (array) $this->getOrganizationTable()->getOrganization($template['organization_id']);
        $organizationAdmin = (array) $this->getUserOrganizationTable()->getOrganizationAdmin($organization['id']);
        $competenceCategories = $this->getCompetenceCategoryTable()
                ->getCategoriesWithOrganizationCompetencesForSelect($organization['id']);

        // Path to template directory.
        $templateDir = "templates/{$template['type']}/";

        // Make sure that the file folder exists.
        $saveFileDir = dirname($saveFilePath);
        if (!is_dir($saveFileDir)) {
            mkdir($saveFileDir);
        }

        $publicDir = str_replace('\\', '/', realpath('./module/Certificat/public'));
        
        // Set data for template
        $data = compact('certificate', 'participant', 'template', 'organization', 'organizationAdmin', 'competenceCategories', 'publicDir');

        // Save original locale and set from certificate
        $origLocale = $this->getTranslator()->getLocale();
        $this->getTranslator()->setLocale(LanguageHelper::getLocale($certificate['language']));

        // Render application template and convert resulting HTML code to PDF.
        $result = $this->getPdfCreator()->htmlToPdf($templateDir . 'certificate.phtml', $data, $saveFilePath);
        
        // Return original locale
        $this->getTranslator()->setLocale($origLocale);

        return $result;
    }

    /**
     * Check current user permissions for certificate
     *
     * @param \Certificat\Model\Certificate $certificate
     */
    private function _checkCertificatePermission(Certificate $certificate)
    {
        $userRole = $this->layout()->userRole;
        $possibleRoles = array('organization_admin', 'organization_editor');
        if (in_array($userRole, $possibleRoles)) {
            $identity = $this->getAuthService()->getIdentity();
            $organization_id = (int) $identity['organization_id'];
            if (is_null($certificate->id) || $certificate->organization_id == $organization_id) {
                return TRUE;
            }
        }

        throw new \Exception("Only 'organization_editor' / 'organization_admin' has access.");
    }

}
