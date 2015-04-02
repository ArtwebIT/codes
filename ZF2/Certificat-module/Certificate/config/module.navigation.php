<?php

use SNJ\Util\Translator;

return array(
    'language' => array(
        array(
            'label' => 'FR',
            'route' => 'fr',
        ),
        array(
            'label' => 'DE',
            'route' => 'de',
        ),
    ),
    'meta' => array(
        array(
            'label' => Translator::translate('Home'),
            'route' => 'ce/index',
            'resource' => 'ce/index'
        ),
        array(
            'label' => Translator::translate('About'),
            'route' => 'ce/about',
            'resource' => 'ce/about'
        ),
        array(
            'label' => Translator::translate('Contact'),
            'route' => 'ce/contact',
            'resource' => 'ce/contact'
        ),
        array(
            'label' => Translator::translate('Legals'),
            'route' => 'ce/legals',
            'resource' => 'ce/legals'
        ),
    ),
    'personal' => array(
        array(
            'label' => Translator::translate('My Profile'),
            'route' => 'ce/my-profile',
            'resource' => 'ce/my-profile'
        ),
        array(
            'label' => Translator::translate('Log out'),
            'route' => 'ce/logout',
            'resource' => 'ce/logout',
            'id' => 'logout-link'
        ),
    ),
    'default' => array(
        array(
            'label' => Translator::translate('Competences'),
            'route' => 'ce/competences',
            'resource' => 'ce/competences'
        ),        
        array(
            'label' => Translator::translate('Organizations'),
            'route' => 'ce/organizations',
            'resource' => 'ce/organizations'
        ),
        array(
            'label' => Translator::translate('Templates'),
            'route' => 'ce/templates',
            'resource' => 'ce/templates'
        ),
        array(
            'label' => Translator::translate('Certificates'),
            'route' => 'ce/certificates',
            'resource' => 'ce/certificates'
        ),     
        array(
            'label' => Translator::translate('Users'),
            'route' => 'ce/users',
            'resource' => 'ce/users'
        ),        
        array(
            'label' => Translator::translate('Profile'),
            'route' => 'ce/my-organization',
            'resource' => 'ce/my-organization'
        ),
    ),
);
