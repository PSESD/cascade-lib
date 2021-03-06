<?php
$lazyLoad = !(defined('IS_CONSOLE') && IS_CONSOLE);

return [
    'class' => 'canis\base\collector\Component',
    'cacheTime' => 120,
    'collectors' => [
        'roles' => include(CANIS_APP_CONFIG_PATH . DIRECTORY_SEPARATOR . 'roles.php'),
        'identityProviders' => include(CANIS_APP_CONFIG_PATH . DIRECTORY_SEPARATOR . 'identityProviders.php'),
        'types' => [
            'class' => 'cascade\components\types\Collector',
        ],
        'taxonomies' => [
            'class' => 'cascade\components\taxonomy\Collector',
        ],
        'themes' => [
            'class' => 'cascade\components\web\themes\Collector',
        ],
        'widgets' => [
            'class' => 'cascade\components\web\widgets\Collector',
            'lazy' => false,
        ],
        'storageHandlers' => [
            'class' => 'canis\storageHandlers\Collector',
            'initialItems' => [
                'local' => [
                    'object' => [
                        'class' => 'canis\storageHandlers\core\LocalHandler',
                        'bucketFormat' => '{year}.{month}',
                        'baseDir' => CANIS_APP_INSTALL_PATH . DIRECTORY_SEPARATOR . 'storage',
                    ],
                    'publicEngine' => true,
                ],
            ],
        ],
        'sections' => [
            'class' => 'cascade\components\section\Collector',
            'lazyLoad' => $lazyLoad,
        ],
        'dataInterfaces' => [
            'class' => 'cascade\components\dataInterface\Collector',
            'lazyLoad' => $lazyLoad,
        ],
        'tools' => [
            'class' => 'cascade\components\tools\Collector',
            'lazyLoad' => $lazyLoad,
        ],
        'reports' => [
            'class' => 'cascade\components\reports\Collector',
            'lazyLoad' => $lazyLoad,
        ],
    ],
];
