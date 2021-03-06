<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\modules\TypeUser;

use cascade\models\User;
use Yii;

/**
 * Module [[@doctodo class_description:cascade\modules\TypeUser\Module]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Module extends \cascade\components\types\Module
{
    /**
     * @inheritdoc
     */
    protected $_title = 'User';
    /**
     * @inheritdoc
     */
    public $icon = 'fa fa-user';

    /**
     * @inheritdoc
     */
    public $hasDashboard = true;

    /**
     * @inheritdoc
     */
    public $widgetNamespace = 'cascade\modules\TypeUser\widgets';
    /**
     * @inheritdoc
     */
    public $modelNamespace = false;

    /**
     * @inheritdoc
     */
    public $searchWeight = 0;

    /**
     * @inheritdoc
     */
    public $enableApiAccess = false;
    /**
     * @inheritdoc
     */
    public function setup()
    {
        $results = [true];

        $results[] = Yii::$app->gk->allow('read', $this->objectTypeModel);

        return min($results);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'Authority' => [
                'class' => 'cascade\components\security\AuthorityBehavior',
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getPrimaryModel()
    {
        return User::className();
    }

    /**
     * @inheritdoc
     */
    public function widgets()
    {
        return parent::widgets();
    }

    /**
     * @inheritdoc
     */
    public function parents()
    {
        return [
            'Group' => ['handlePrimary' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function children()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function taxonomies()
    {
        return [];
    }
}
