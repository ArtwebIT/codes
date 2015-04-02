<?php

/**
 * This configuration should be put in your module `configs` directory.
 */
return array(
    // Use on production environment
    // 'debug'              => false,
    // 'buildOnRequest'     => false,
    // Use on development environment
    'debug' => true,
    'buildOnRequest' => true,
    // This is optional flag, by default set to `true`.
    // In debug mode allow you to combine all assets to one file.
    // 'combine' => false,
    // this is specific to this project
    'webPath' => realpath(__DIR__ . '/../public'),
    'basePath' => './',
    'default' => array(
        'assets' => array(
            '@base_css',
            '@base_js',
        ),
    ),
    'modules' => array(
        'Certificat' => array(
            'root_path' => __DIR__ . '/../public',
            'collections' => array(
                'base_css' => array(
                    'assets' => array(
                        'styles/bootstrap.less',
                        'styles/main.less',
                        'styles/chosen.css',
                        'styles/custom.less',
                    ),
                    'filters' => array(
                        'LessphpFilter' => array(
                            'name' => 'Assetic\Filter\LessphpFilter'
                        ),
                        'CssMinFilter' => array(
                            'name' => 'Assetic\Filter\CssMinFilter'
                        ),
                    ),
                    'options' => array(
                        'output' => 'cache/application.min.css'
                    ),
                ),
                'base_js' => array(
                    'assets' => array(
                        'scripts/jquery-1.11.1.min.js',
                        'components/bootstrap/dist/js/bootstrap.min.js',
                        'scripts/validation/jquery.validate.js',
                        'scripts/validation/additional-methods.min.js',
                        'scripts/layout.js',
                        'scripts/bootstrap-datepicker.js',
                        'scripts/chosen.jquery.min.js',
                        'scripts/app.frontend.js',
                    ),
                    'filters' => array(
                        'JSMinFilter' => array(
                            'name' => 'Assetic\Filter\JSMinFilter'
                        ),
                    ),
                    'options' => array(
                        'output' => 'cache/application.min.js',
                    )
                ),
            ),
        ),
    ),
);
