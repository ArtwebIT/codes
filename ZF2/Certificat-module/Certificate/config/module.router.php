<?php

return array(
    'routes' => array(
        'ce' => array(
            'type' => 'Method',
            'options' => array(
                'verb' => 'post,get',
                'defaults' => array(
                    '__NAMESPACE__' => 'Certificat\Controller',
                    'controller' => 'Index',
                    'action' => 'index',
                ),
            ),
            'may_terminate' => true,
            'child_routes' => array(
                // PAGE: Index (Login/Registration screen)
                'index' => array(
                    'type' => 'literal',
                    'options' => array(
                        'route' => '/',
                        'defaults' => array(
                            'action' => 'index',
                        )
                    ),
                ),
                // PAGE: Contact
                'contact' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route' => '/contact',
                        'defaults' => array(
                            'action' => 'contact',
                        ),
                    ),
                ),
                // PAGE: About
                'about' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route' => '/about',
                        'defaults' => array(
                            'action' => 'about',
                        ),
                    ),
                ),
                // PAGE: Legals
                'legals' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route' => '/legals',
                        'defaults' => array(
                            'action' => 'legals',
                        ),
                    ),
                ),
                // PAGE: Terms & conditions
                'terms' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route' => '/terms',
                        'defaults' => array(
                            'action' => 'terms'
                        ),
                    ),
                ),
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                // PAGE: Login
                'login' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/login[/][:action]',
                        'constraints' => array(
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        ),
                        'defaults' => array(
                            'controller' => 'Auth',
                            'action' => 'login',
                        ),
                    ),
                ),
                // PAGE: Passwort forgot
                'lost-password' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route' => '/lost-password',
                        'defaults' => array(
                            'controller' => 'Auth',
                            'action' => 'lost-password',
                        ),
                    ),
                ),
                // PAGE: Logout
                'logout' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route' => '/logout',
                        'defaults' => array(
                            'controller' => 'Auth',
                            'action' => 'logout',
                        ),
                    ),
                ),
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                // PAGE: Register
                'register' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route' => '/register',
                        'defaults' => array(
                            'controller' => 'Auth',
                            'action' => 'register',
                        ),
                    ),
                ),
                // PAGE: Register organization
                'register-organization' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route' => '/register-organization',
                        'defaults' => array(
                            'controller' => 'Auth',
                            'action' => 'register-organization',
                        ),
                    ),
                ),
                // PAGE: Check E-mail before register
                'check-email' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/check-email',
                        'defaults' => array(
                            'controller' => 'Auth',
                            'action' => 'check-email',
                            'target' => 'iframe', // Prevent errors when switching language after a request
                        ),
                    ),
                ),
                // PAGE: Register (activate a new registered user)
                'activate' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/activate[/user/:user][/code/:code]',
                        'constraints' => array(
                            'user' => '[0-9]+',
                        ),
                        'defaults' => array(
                            'controller' => 'Auth',
                            'action' => 'activate',
                        ),
                    ),
                ),
                // PAGE: Register (reload captcha by ajax)
                'captcha-reload' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route' => '/captcha-reload',
                        'defaults' => array(
                            'controller' => 'Auth',
                            'action' => 'captcha-reload',
                            'target' => 'iframe', // Prevent errors when switching language after a request
                        ),
                    ),
                ),
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                // PAGE: My Profile
                'my-profile' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/my-profile[/][:action]',
                        'constraints' => array(
                            'action' => 'edit|save',
                        ),
                        'defaults' => array(
                            'controller' => 'Profile',
                            'action' => 'edit'
                        ),
                    ),
                ),
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                // PAGE: Organizations (for application_admin)
                'organizations' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/organizations[/][:action][/id/:id][/page/:page]',
                        'constraints' => array(
                            'action' => 'show',
                            'id' => '[0-9]+',
                            'page' => '[0-9]+',
                        ),
                        'defaults' => array(
                            'controller' => 'Organizations',
                            'action' => 'index'
                        ),
                    ),
                ),
                'toggle-approve-organization' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/organizations/toggle-approve[/id/:id]',
                        'constraints' => array(
                            'id' => '[0-9]+',
                        ),
                        'defaults' => array(
                            'controller' => 'Organizations',
                            'action' => 'toggle-approve',
                            'target' => 'iframe', // Prevent errors when switching language after a request
                        ),
                    ),
                ),                
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                // PAGE: My Organization (for organization_admin)
                'my-organization' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route' => '/my-organization',
                        'defaults' => array(
                            'controller' => 'Organizations',
                            'action' => 'edit'
                        ),
                    ),
                ),
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                // PAGE: Certificates  
                'certificates' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/certificates[/][:action][/id/:id]',
                        'constraints' => array(
                            'action' => 'overview|new|save|delete|archive',
                            'id' => '[0-9]+',
                        ),
                        'defaults' => array(
                            'controller' => 'Certificates',
                            'action' => 'index',
                        ),
                    ),
                ),
                'csv-to-json-for-certificate' => array(
                    'type' => 'Literal',
                    'options' => array(
                        'route' => '/certificates/csv-to-json',
                        'defaults' => array(
                            'controller' => 'Certificates',
                            'action' => 'csv-to-json',
                            'target' => 'iframe', // Prevent errors when switching language after a request
                        ),
                    ),
                ),                   
                'preview-certificate' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/certificates/preview[/id/:id]',
                        'constraints' => array(
                            'id' => '[0-9]+',
                        ),
                        'defaults' => array(
                            'controller' => 'Certificates',
                            'action' => 'preview',
                            'target' => 'iframe', // Prevent errors when switching language after a request
                        ),
                    ),
                ),                
                'download-certificate' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/certificates/download[/id/:id]',
                        'constraints' => array(
                            'id' => '[0-9]+',
                        ),
                        'defaults' => array(
                            'controller' => 'Certificates',
                            'action' => 'download',
                            'target' => 'iframe', // Prevent errors when switching language after a request
                        ),
                    ),
                ),
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                // PAGE: Competences  
                'competences' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/competences[/][:action][/id/:id]',
                        'constraints' => array(
                            'action' => 'save-category|save|delete-category|delete',
                            'id' => '[0-9]+',
                        ),
                        'defaults' => array(
                            'controller' => 'Competences',
                            'action' => 'index'
                        ),
                    ),
                ),
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                // PAGE: Templates  
                'templates' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/templates[/][:action][/id/:id][/page/:page]',
                        'constraints' => array(
                            'action' => 'edit|new|save|delete',
                            'id' => '[0-9]+',
                            'page' => '[0-9]+',
                        ),
                        'defaults' => array(
                            'controller' => 'Templates',
                            'action' => 'index'
                        ),
                    ),
                ),
                'get-template-description' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/templates/get-description[/id/:id]',
                        'constraints' => array(
                            'id' => '[0-9]+',
                        ),
                        'defaults' => array(
                            'controller' => 'Templates',
                            'action' => 'get-description',
                            'target' => 'iframe',
                        ),
                    ),
                ),
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                // PAGE: Users  
                'users' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/users[/][:action][/id/:id]',
                        'constraints' => array(
                            'action' => 'save-editor|delete-editor',
                            'id' => '[0-9]+',
                        ),
                        'defaults' => array(
                            'controller' => 'Users',
                            'action' => 'index'
                        ),
                    ),
                ),
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                /////////////////////////////////////////////////////////////////////////////
                // Files        
                'files' => array(
                    'type' => 'Segment',
                    'options' => array(
                        'route' => '/files[/][:action][/id/:id]',
                        'constraints' => array(
                            'action' => 'show|crop|upload-avatar|upload-logo',
                            'id' => '[0-9]+',
                        ),
                        'defaults' => array(
                            'controller' => 'Files',
                            'target' => 'iframe', // Prevent errors when switching language after a request
                        ),
                    ),
                ),
            ),
        ),
    ),
);
