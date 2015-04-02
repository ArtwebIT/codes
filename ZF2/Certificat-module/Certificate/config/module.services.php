<?php

namespace Certificat;

use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Stdlib\Hydrator\ObjectProperty;

return array(
    'invokables' => array(
        'LoginForm' => 'Certificat\Form\LoginForm',
        'LostpasswordForm' => 'Certificat\Form\LostpasswordForm',
        'RegisterForm' => 'Certificat\Form\RegisterForm',
        'RegisterOrganizationForm' => 'Certificat\Form\RegisterOrganizationForm',
        'OrganizationForm' => 'Certificat\Form\OrganizationForm',
        'CompetenceForm' => 'Certificat\Form\CompetenceForm',
        'CompetenceCategoryForm' => 'Certificat\Form\CompetenceCategoryForm',
        'TemplateForm' => 'Certificat\Form\TemplateForm',
        'CertificateForm' => 'Certificat\Form\CertificateForm',
        'OrganizationEditorForm' => 'Certificat\Form\OrganizationEditorForm',
        'OrganizationUserProfileForm' => 'Certificat\Form\OrganizationUserProfileForm',
        'ParticipantUserProfileForm' => 'Certificat\Form\ParticipantUserProfileForm',
        'CropForm' => 'SNJ\Form\CropForm',
    ),
    'factories' => array(
        'Certificat\Model\CertificateParticipantTable' => function($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ObjectProperty());
            $resultSetPrototype->setObjectPrototype(new Model\CertificateParticipant());
            $tableGateway = new TableGateway('ce_certificate_participant', $dbAdapter, null, $resultSetPrototype);
            $table = new Model\CertificateParticipantTable($tableGateway);
            return $table;
        },
        'Certificat\Model\CertificateTable' => function($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ObjectProperty());
            $resultSetPrototype->setObjectPrototype(new Model\Certificate());
            $tableGateway = new TableGateway('ce_certificate', $dbAdapter, null, $resultSetPrototype);
            $table = new Model\CertificateTable($tableGateway);
            return $table;
        },
        'Certificat\Model\TemplateCompetenceTable' => function($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ObjectProperty());
            $resultSetPrototype->setObjectPrototype(new Model\TemplateCompetence());
            $tableGateway = new TableGateway('ce_template_competence', $dbAdapter, null, $resultSetPrototype);
            $table = new Model\TemplateCompetenceTable($tableGateway);
            return $table;
        },
        'Certificat\Model\TemplateTable' => function($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ObjectProperty());
            $resultSetPrototype->setObjectPrototype(new Model\Template());
            $tableGateway = new TableGateway('ce_template', $dbAdapter, null, $resultSetPrototype);
            $table = new Model\TemplateTable($tableGateway);
            return $table;
        },
        'Certificat\Model\CompetenceTable' => function($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ObjectProperty());
            $resultSetPrototype->setObjectPrototype(new Model\Competence());
            $tableGateway = new TableGateway('ce_competence', $dbAdapter, null, $resultSetPrototype);
            $table = new Model\CompetenceTable($tableGateway);
            return $table;
        },
        'Certificat\Model\CompetenceCategoryTable' => function($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ObjectProperty());
            $resultSetPrototype->setObjectPrototype(new Model\CompetenceCategory());
            $tableGateway = new TableGateway('ce_competence_category', $dbAdapter, null, $resultSetPrototype);
            $table = new Model\CompetenceCategoryTable($tableGateway);
            return $table;
        },
        'Certificat\Model\OrganizationTable' => function($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ObjectProperty());
            $resultSetPrototype->setObjectPrototype(new Model\Organization());
            $tableGateway = new TableGateway('organization', $dbAdapter, null, $resultSetPrototype);
            $table = new Model\OrganizationTable($tableGateway);
            return $table;
        },
        'Certificat\Model\UserOrganizationTable' => function($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $resultSetPrototype = new HydratingResultSet();
            $resultSetPrototype->setHydrator(new ObjectProperty());
            $resultSetPrototype->setObjectPrototype(new Model\UserOrganization());
            $tableGateway = new TableGateway('user_organization', $dbAdapter, null, $resultSetPrototype);
            $table = new Model\UserOrganizationTable($tableGateway);
            return $table;
        },
        'AuthService' => function($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $dbTableAuthAdapter = new \Zend\Authentication\Adapter\DbTable($dbAdapter, 'user', 'email', 'password', 'MD5(?)');
            $authService = new Service\Authentication();
            $authService->setAdapter($dbTableAuthAdapter);
            $authService->setStorage($sm->get('AuthStorage'));
            return $authService;
        },
    ),
);
