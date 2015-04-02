<?php

/**
 * ACL role configuration for module 'Certificat'
 */
return array(
    'guest' => array(
        'login',
        'register',
        
        'index',
        'about',
        'contact',
        'legals',
    ),
    'participant' => array(
        'index',
        'about',
        'contact',
        'legals',
        
        'logout',
        'my-profile',
        
        'download-certificate'
    ),
    'organization_editor' => array(
        'index',
        'about',
        'contact',
        'legals',
        
        'logout',
        'my-profile',
        
        'templates',
        'certificates',
        'download-certificate',
        'preview-certificate',
        'csv-to-json-for-certificate'
    ),
    'organization_admin' => array(
        'index',
        'about',
        'contact',
        'legals',
        
        'logout',
        'my-profile',
        
        'templates',
        'certificates',
        'download-certificate',
        'preview-certificate',
        'csv-to-json-for-certificate',
        
        'my-organization',
        'competences',
        'users'
    ),
    'application_admin' => array(
        'index',
        'about',
        'contact',
        'legals',
        
        'logout',
        'my-profile',
        
        'competences',
        'organizations'
    )
);
