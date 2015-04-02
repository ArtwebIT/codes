<?php

/**
 * Controller class for manage competences
 */

namespace Certificat\Controller;

use Certificat\Model\Competence;
use Certificat\Model\CompetenceCategory;

/**
 * Controller class
 */
class CompetencesController extends BaseController
{

    /**
     * Default Action. Shows competences by categories.
     *
     */
    public function indexAction()
    {
        $userRole = $this->layout()->userRole;
        if ($userRole == 'application_admin') {
            $categories = $this->getCompetenceCategoryTable()->getCategoriesWithAllCompetences();
        } else {
            $identity = $this->getAuthService()->getIdentity();
            $organization_id = (int) $identity['organization_id'];
            $categories = $this->getCompetenceCategoryTable()->getCategoriesWithOrganizationCompetences($organization_id);
        }

        try {
            $id = $this->params()->fromQuery('last_id');
            $lastCompetence = $this->getCompetenceTable()->getCompetence($id);
            $this->_checkCompetencePermission($lastCompetence);
        } catch (\Exception $ex) {
            $lastCompetence = null;
        }

        return array(
            'competenceCategoryForm' => $this->getFormElementManager()->get('CompetenceCategoryForm'),
            'competenceForm' => $this->getFormElementManager()->get('CompetenceForm'),
            'categories' => $categories,
            'languages' => $this->getCompetenceCategoryTable()->getLanguages(),
            'lastCompetence' => $lastCompetence
        );
    }

    /**
     * Save competence
     *
     */
    public function saveAction()
    {
        if ($id = $this->params()->fromRoute('id')) {
            $competence = $this->getCompetenceTable()->getCompetence($id);
        } else {
            $competence = new Competence();
        }
        $this->_checkCompetencePermission($competence);

        $form = $this->getFormElementManager()->get('CompetenceForm');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $data = $request->getPost();

            // If new competence then set the organization_id. It NULL for admin
            if (is_null($competence->id)) {
                $identity = $this->getAuthService()->getIdentity();
                $data['organization_id'] = (int) $identity['organization_id'];
            }

            $form->setData($data);
            $form->setInputFilter($competence->getInputFilter());
            if ($form->isValid()) {
                $competence->exchangeArray($form->getData());
                try {
                    $competence->id = $this->getCompetenceTable()->saveCompetence($competence);
                    $message = $this->getTranslator()->translate('Competence was successfully saved.');
                    $this->flashMessenger()->addSuccessMessage($message);
                } catch (\Exception $ex) {
                    $this->flashMessenger()->addErrorMessage($ex->getMessage());
                }
            } else {
                foreach ($form->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage(implode('<br>', $message));
                }
            }

            return $this->redirect()->toRoute('ce/competences', array(), array('query' => array('last_id' => $competence->id)));
        }
    }

    /**
     * Save competence category
     *
     */
    public function saveCategoryAction()
    {
        if ($id = $this->params()->fromRoute('id')) {
            $competenceCategory = $this->getCompetenceCategoryTable()->getCompetenceCategory($id);
        } else {
            $competenceCategory = new CompetenceCategory();
        }
        $this->_checkCompetenceCategoryPermission($competenceCategory);

        $form = $this->getFormElementManager()->get('CompetenceCategoryForm');
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            $form->setInputFilter($competenceCategory->getInputFilter());
            if ($form->isValid()) {
                $competenceCategory->exchangeArray($form->getData());
                try {
                    $this->getCompetenceCategoryTable()->saveCompetenceCategory($competenceCategory);
                    $message = $this->getTranslator()->translate('Competence category was successfully saved.');
                    $this->flashMessenger()->addSuccessMessage($message);
                } catch (\Exception $ex) {
                    $this->flashMessenger()->addErrorMessage($ex->getMessage());
                }
            } else {
                foreach ($form->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage(implode('<br>', $message));
                }
            }

            return $this->redirect()->toRoute('ce/competences');
        }
    }

    /**
     * Delete competence
     *
     */
    public function deleteAction()
    {
        $id = $this->params()->fromRoute('id', null);
        $competence = $this->getCompetenceTable()->getCompetence($id);
        $this->_checkCompetencePermission($competence);

        try {
            $this->getCompetenceTable()->deleteCompetence($competence->id);
            $message = $this->getTranslator()->translate('Competence was successfully deleted.');
            $this->flashMessenger()->addSuccessMessage($message);
        } catch (\Exception $ex) {
            $this->flashMessenger()->addErrorMessage($ex->getMessage());
        }

        return $this->redirect()->toRoute('ce/competences');
    }

    /**
     * Delete competence category
     *
     */
    public function deleteCategoryAction()
    {
        $id = $this->params()->fromRoute('id', null);
        $competenceCategory = $this->getCompetenceCategoryTable()->getCompetenceCategory($id);
        $this->_checkCompetenceCategoryPermission($competenceCategory);

        try {
            $this->getCompetenceCategoryTable()->deleteCompetenceCategory($competenceCategory->id);
            $message = $this->getTranslator()->translate('Competence category was successfully deleted.');
            $this->flashMessenger()->addSuccessMessage($message);
        } catch (\Exception $ex) {
            $this->flashMessenger()->addErrorMessage($ex->getMessage());
        }

        return $this->redirect()->toRoute('ce/competences');
    }

    /**
     * Check current user permissions for competence
     *
     * @param \Certificat\Model\Competence $competence
     */
    private function _checkCompetencePermission(Competence $competence)
    {
        $userRole = $this->layout()->userRole;
        $possibleRoles = array('application_admin', 'organization_admin');
        if (in_array($userRole, $possibleRoles)) {
            $identity = $this->getAuthService()->getIdentity();
            $organization_id = $identity['organization_id']; // NULL for application_admin
            if (is_null($competence->id) || $competence->organization_id === $organization_id) {
                return TRUE;
            }            
        }

        throw new \Exception("Only application_admin/organization_admin can edit information.");
    }

    /**
     * Check current user permissions for competence category
     *
     * @param \Certificat\Model\CompetenceCategory $competenceCategory
     */
    private function _checkCompetenceCategoryPermission(CompetenceCategory $competenceCategory)
    {
        $userRole = $this->layout()->userRole;
        if ($userRole == 'application_admin') {
            return TRUE;
        }

        throw new \Exception("Only application_admin can edit information.");
    }

}
