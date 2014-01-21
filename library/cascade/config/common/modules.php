<?php
/**
 * ./app/config/environments/common/modules.php
 *
 * @author Jacob Morrison <jacob@infinitecascade.com>
 * @package cascade
 */


return [
	'WidgetWatching' => [
	    'class' => 'cascade\modules\WidgetWatching\Module',
	],
    'TypeUser' => [
        'class' => 'cascade\modules\TypeUser\Module',
    ],
    'TypeGroup' => [
        'class' => 'cascade\modules\TypeGroup\Module',
    ]
];
?>