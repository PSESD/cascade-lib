<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db\fields;

use canis\helpers\Match;
use yii\db\ColumnSchema;

/**
 * HumanFieldDetector [[@doctodo class_description:cascade\components\db\fields\HumanFieldDetector]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class HumanFieldDetector extends \canis\base\Object
{
    /**
     * @var [[@doctodo var_type:_machineTests]] [[@doctodo var_description:_machineTests]]
     */
    static $_machineTests = [
        'id',
        '/\_id$/',
        '/\_hash$/',
        '_moduleHandler',
        'created',
        'modified',
        'deleted',
        'archived',
    ];

    /**
     * [[@doctodo method_description:test]].
     *
     * @param yii\db\ColumnSchema $column [[@doctodo param_description:column]]
     *
     * @return [[@doctodo return_type:test]] [[@doctodo return_description:test]]
     */
    public static function test(ColumnSchema $column)
    {
        foreach (static::$_machineTests as $test) {
            $t = new Match($test);
            if ($t->test($column->name)) {
                return false;
            }
        }

        return true;
    }

    /**
     * [[@doctodo method_description:registerMachineTest]].
     *
     * @param [[@doctodo param_type:test]] $test [[@doctodo param_description:test]]
     *
     * @return [[@doctodo return_type:registerMachineTest]] [[@doctodo return_description:registerMachineTest]]
     */
    public static function registerMachineTest($test)
    {
        if (is_array($test)) {
            foreach ($test as $t) {
                self::registerMachineTest($t);
            }

            return true;
        }
        self::$_machineTests[] = $test;

        return true;
    }
}
