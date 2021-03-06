<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\tools;

/**
 * Module [[@doctodo class_description:cascade\components\tools\Module]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends \cascade\components\base\CollectorModule
{
    /**
     * @var [[@doctodo var_type:name]] [[@doctodo var_description:name]]
     */
    public $name;
    /**
     * @var [[@doctodo var_type:version]] [[@doctodo var_description:version]]
     */
    public $version = 1;

    /**
     * @var [[@doctodo var_type:icon]] [[@doctodo var_description:icon]]
     */
    public $icon = 'fa fa-wrench';

    /**
     * @inheritdoc
     */
    public function getCollectorName()
    {
        return 'tools';
    }

    /**
     * @inheritdoc
     */
    public function getModuleType()
    {
        return 'Tool';
    }
}
