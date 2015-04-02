<?php

/**
 * Module configuration for module 'Certificat'
 */
return array(
    'controllers' => array(
        'invokables' => array(
            'Certificat\Controller\Index' => 'Certificat\Controller\IndexController',
            'Certificat\Controller\Auth' => 'Certificat\Controller\AuthController',
            'Certificat\Controller\Certificates' => 'Certificat\Controller\CertificatesController',
            'Certificat\Controller\Templates' => 'Certificat\Controller\TemplatesController',
            'Certificat\Controller\Profile' => 'Certificat\Controller\ProfileController',
            'Certificat\Controller\Organizations' => 'Certificat\Controller\OrganizationsController',
            'Certificat\Controller\Competences' => 'Certificat\Controller\CompetencesController',
            'Certificat\Controller\Users' => 'Certificat\Controller\UsersController',
            'Certificat\Controller\Files' => 'Certificat\Controller\FilesController',
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'navigation' => 'Zend\Navigation\Service\DefaultNavigationFactory',
            'language_navigation' => 'Certificat\Service\LanguageNavigationFactory',
            'meta_navigation' => 'Certificat\Service\MetaNavigationFactory',
            'personal_navigation' => 'Certificat\Service\PersonalNavigationFactory',
        ),
    ),
    'module_layouts' => array(
        'Certificat' => 'layout/certificat',
    ),
    'view_manager' => array(
        'template_map' => array(
            'layout/certificat' => __DIR__ . '/../view/layout/layout.phtml',
            'paginator_slide' => __DIR__ . '/../view/layout/paginator_slide.phtml',
        ),
        'template_path_stack' => array(
            'certificat' => __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'router' => include realpath(__DIR__ . '/module.router.php'),
    'navigation' => include realpath(__DIR__ . '/module.navigation.php'),
    'assetic_configuration' => include realpath(__DIR__ . '/module.assets.php'),
    'view_helper_config' => array(
        'flashmessenger' => array(
            'message_open_format' => '<div%s><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button><ul><li>',
            'message_close_string' => '</li></ul></div>',
            'message_separator_string' => '</li><li>'
        )
    ),
    'mail' => array(
        'recipient' => array(
            'admin' => 'admin@certificat.localhost',
        ),        
        'sender' => array(
            'activation' => array(
                'name' => 'SNJ',
                'email' => 'register@certificat.localhost',
            ),
            'newpassword' => array(
                'name' => 'SNJ',
                'email' => 'no-reply@certificat.localhost',
            ),
            'no-reply' => array(
                'name' => 'SNJ',
                'email' => 'no-reply@certificat.localhost',
            ),
            'inactivity' => array(
                'name' => 'SNJ',
                'email' => 'info@certificat.localhost',
            ),
            'admin' => array(
                'name' => 'SNJ',
                'email' => 'admin@certificat.localhost',
            ),
        ),
    ),
);
