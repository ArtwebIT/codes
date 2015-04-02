<?php

/**
 * Controller class for manage organizations
 */

namespace Certificat\Controller;

use Zend\Paginator\Adapter\ArrayAdapter;
use Zend\Paginator\Paginator;
use Zend\View\Model\JsonModel;
use Certificat\Model\UserOrganization;

/**
 * Controller class
 */
class OrganizationsController extends BaseController
{

    /**
     * Default Action. Shows organizations.
     *
     */
    public function indexAction()
    {
        $searchTerm = $this->params()->fromQuery('search');
        $page = (int) $this->params()->fromRoute('page', 1);
        $itemsPerPage = 10;
        $organizations = $this->getOrganizationTable()->fetchAll($searchTerm);

        $pageAdapter = new ArrayAdapter($organizations->toArray());
        $paginator = new Paginator($pageAdapter);
        $paginator->setCurrentPageNumber($page)
                ->setItemCountPerPage($itemsPerPage)
                ->setPageRange(7);

        return array(
            'paginator' => $paginator
        );
    }

    /**
     * Ajax action to approve or disapprove organization.
     * 
     * @return \Zend\View\Model\JsonModel
     */
    public function toggleApproveAction()
    {
        if ($this->getRequest()->isXmlHttpRequest() && $this->getRequest()->isPost()) {
            $id = $this->params()->fromRoute('id', null);
            $organization = $this->getOrganizationTable()->getOrganization($id);
            if ($organization->approved == 1) {
                $this->getOrganizationTable()->disapproveOrganization($organization->id);
            } else {
                $this->getOrganizationTable()->approveOrganization($organization->id);
            }

            return new JsonModel(array('success' => true));
        }

        return $this->notFoundAction();
    }

    /**
     * Show organization
     *
     */
    public function showAction()
    {
        $id = $this->params()->fromRoute('id', null);
        $organization = $this->getOrganizationTable()->getOrganization($id);
       
        return array(
            'organization' => $organization,
            
        );
    }

    public function editAction()
    {
        try {
            $identity = $this->getAuthService()->getIdentity();
            $user_id = (int) $identity['id'];
            $organization = $this->getUserOrganizationTable()->getUserOrganization($user_id);
            $roleInOrganization = $this->getUserOrganizationTable()->getUserRoleInOrganization($user_id, $organization->id);
            if ($roleInOrganization != UserOrganization::ROLE_ADMIN) {
                throw new \Exception("Only organization admin can edit information.");
            }
        } catch (\Exception $ex) {
            return $this->notFoundAction();
        }

        $form = $this->getFormElementManager()->get('OrganizationForm');
        $cropForm = $this->getFormElementManager()->get('CropForm');

        // Populate login form with submitted data.
        if (isset($_SESSION['organization_form_data'])) {
            $form->setData($_SESSION['organization_form_data']);
            unset($_SESSION['organization_form_data']);
        } else {
            $form->setData($organization->getArrayCopy());
        }

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setData($request->getPost());
            $form->setInputFilter($organization->getInputFilter());
            if ($form->isValid()) {
                $organization->exchangeArray($form->getData());
                try {
                    $this->getOrganizationTable()->saveOrganization($organization);
                    $message = $this->getTranslator()->translate('Your changes was successfully saved.');
                    $this->flashMessenger()->addSuccessMessage($message);
                } catch (\Exception $ex) {
                    $this->flashMessenger()->addErrorMessage($ex->getMessage());
                }
            } else {
                foreach ($form->getMessages() as $message) {
                    $this->flashMessenger()->addErrorMessage(implode('<br>', $message));
                }

                $_SESSION['organization_form_data'] = $request->getPost();
            }

            return $this->redirect()->refresh();
        }

        return array(
            'organization' => $organization,
            'form' => $form,
            'cropForm' => $cropForm
        );
    }

}
