<?php

/**
 * Controller class for manage templates
 */

namespace Certificat\Controller;

use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Certificat\Model\Template;
use Certificat\Model\TemplateCompetence;

/**
 * Controller class
 */
class TemplatesController extends BaseController
{

    /**
     * Default Action. Shows templates.
     *
     */
    public function indexAction()
    {
        $identity = $this->getAuthService()->getIdentity();
        $organization_id = (int) $identity['organization_id'];
        $page = (int) $this->params()->fromRoute('page', 1);
        $itemsPerPage = 10;

        $templates = $this->getTemplateTable()->getByOrganizationId($organization_id);

        $pageAdapter = new ArrayAdapter($templates->toArray());
        $paginator = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsPerPage)
                ->setPageRange(7);

        return array(
            'paginator' => $paginator
        );
    }

    /**
     * Add new template
     *
     */
    public function newAction()
    {
        $view = new ViewModel($this->_getForm());

        // Set render script
        $view->setTemplate('certificat/templates/edit');
        return $view;
    }

    /**
     * Edit template
     *
     */
    public function editAction()
    {
        return $this->_getForm();
    }

    /**
     * Save template. For edit and add
     *
     */
    public function saveAction()
    {
        if ($id = $this->params()->fromRoute('id')) {
            $template = $this->getTemplateTable()->getTemplate($id);
            $backUrlParams = array('action' => 'edit', 'id' => $template->id);
        } else {
            $template = new Template();
            $backUrlParams = array('action' => 'new');
        }
        $this->_checkTemplatePermission($template);

        // Get competences options for current organization
        $identity = $this->getAuthService()->getIdentity();
        $organization_id = (int) $identity['organization_id'];
        $competenceOptions = $this->getCompetenceCategoryTable()
                ->getCategoriesWithOrganizationCompetencesForSelect($organization_id);

        // Get form and set competences options
        $form = $this->getFormElementManager()->get('TemplateForm')
                ->setCompetencesOptions($competenceOptions);

        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();
            $data['persons_in_charge'] = $this->_preparePersonsInCharge($data['persons_in_charge']);

            // If new template then set the organization_id and user_id
            if (is_null($template->id)) {
                $data['user_id'] = (int) $identity['id'];
                $data['organization_id'] = (int) $identity['organization_id'];
            }

            $form->setData($data);
            $form->setInputFilter($template->getInputFilter());

            if ($form->isValid()) {
                $template->exchangeArray($form->getData());
                try {
                    $template->id = $this->getTemplateTable()->saveTemplate($template);

                    // Save template competences
                    $this->_saveTemplateCompetences($template->id, $data['competences']);

                    $message = $this->getTranslator()->translate('Template was successfully saved.');
                    $this->flashMessenger()->addSuccessMessage($message);
                    return $this->redirect()->toRoute('ce/templates');
                } catch (\Exception $ex) {
                    $this->flashMessenger()->addErrorMessage($ex->getMessage());
                }
            } else {
                foreach ($form->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage(implode('<br>', $message));
                }
            }

            $_SESSION['template_form_data'] = $data;

            return $this->redirect()->toRoute('ce/templates', $backUrlParams);
        }
    }

    /**
     * Delete template
     *
     */
    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id');
        $template = $this->getTemplateTable()->getTemplate($id);
        $this->_checkTemplatePermission($template);

        try {
            $this->getTemplateTable()->deleteTemplate($template->id);
            $message = $this->getTranslator()->translate('Template was successfully deleted.');
            $this->flashMessenger()->addSuccessMessage($message);
            return $this->redirect()->toRoute('ce/templates');
        } catch (\Exception $ex) {
            $this->flashMessenger()->addErrorMessage($ex->getMessage());
            return $this->redirect()->toRoute('ce/templates', array('action' => 'edit', 'id' => $template->id));
        }
    }

    /**
     * Ajax action to get the default description from template
     *
     * @return \Zend\View\Model\JsonModel
     */
    public function getDescriptionAction()
    {
        $id = $this->params()->fromRoute('id');
        $template = $this->getTemplateTable()->getTemplate($id);

        return new JsonModel(array(
            'success' => true,
            'description' => $template->additional_comment
        ));
    }

    /**
     * Check current user permissions for template
     *
     * @param \Certificat\Model\Template $template
     */
    private function _checkTemplatePermission(Template $template)
    {
        $userRole = $this->layout()->userRole;
        $possibleRoles = array('organization_admin', 'organization_editor');
        if (in_array($userRole, $possibleRoles)) {
            $identity = $this->getAuthService()->getIdentity();
            $organization_id = (int) $identity['organization_id'];
            if (is_null($template->id) || $template->organization_id == $organization_id) {
                return TRUE;
            }
        }

        throw new \Exception("Only organization_editor/organization_admin can edit information.");
    }

    /**
     * Get edit or add form. If isset id then edit
     *
     * @return array View variables
     */
    private function _getForm()
    {
        if ($id = $this->params()->fromRoute('id')) {
            $template = $this->getTemplateTable()->getTemplate($id);
            $this->_checkTemplatePermission($template);
            $templatePersons = json_decode($template->persons_in_charge);
            $templateCompetences = $this->getTemplateCompetenceTable()->getCompetencesIdsByTemplateId($template->id);
            $formAction = $this->url()->fromRoute('ce/templates', array('action' => 'save', 'id' => $template->id));
            $title = $template->name;
        } else {
            $template = null;
            $templatePersons = array();
            $templateCompetences = array();
            $formAction = $this->url()->fromRoute('ce/templates', array('action' => 'save'));
            $title = $this->getTranslator()->translate('Add template');
        }

        // Get and prepare form
        $form = $this->getFormElementManager()->get('TemplateForm')
                ->setAttribute('action', $formAction);

        // Get competences options for current organization
        $identity = $this->getAuthService()->getIdentity();
        $organization_id = (int) $identity['organization_id'];
        $competenceOptions = $this->getCompetenceCategoryTable()
                ->getCategoriesWithOrganizationCompetencesForSelect($organization_id);

        // Set competences options and values
        $form->setCompetencesOptions($competenceOptions)
                ->setCompetencesValues($templateCompetences);

        // Populate login form with submitted data or from db.
        if (isset($_SESSION['template_form_data'])) {
            $form->setData($_SESSION['template_form_data']);
            unset($_SESSION['template_form_data']);
        } elseif (isset($template)) {
            $form->setData($template->getArrayCopy());
        }

        return array(
            'form' => $form,
            'title' => $title,
            'template' => $template,
            'templatePersons' => $templatePersons
        );
    }

    /**
     * Prepare format for persons_in_charge field
     *
     * @param array $persons_in_charge
     * @return string
     */
    private function _preparePersonsInCharge($persons_in_charge)
    {
        $result = null;
        if (is_array($persons_in_charge)) {
            // Reset array keys and encode to json
            $persons_in_charge = array_values($persons_in_charge);
            $result = json_encode($persons_in_charge);
        }

        return $result;
    }

    /**
     * Save template competences. Delete old and add new
     *
     * @param int $template_id
     * @param array $competences Array of competences id
     */
    private function _saveTemplateCompetences($template_id, $competences)
    {
        // Delete old competences from template
        $this->getTemplateCompetenceTable()->deleteByTemplateId($template_id);

        // Add new competences to template
        if (count($competences) > 0) {
            foreach ($competences as $competence_id) {
                $templateCompetence = new TemplateCompetence();
                $templateCompetence->exchangeArray(array(
                    'template_id' => $template_id,
                    'competence_id' => $competence_id
                ));
                $this->getTemplateCompetenceTable()->save($templateCompetence);
            }
        }
    }

}
