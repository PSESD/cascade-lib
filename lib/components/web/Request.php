<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web;

use cascade\models\Registry;
use Yii;
use yii\web\Application;

/**
 * Request [[@doctodo class_description:cascade\components\web\Request]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Request extends \canis\web\Request
{
    /**
     * @var [[@doctodo var_type:_object]] [[@doctodo var_description:_object]]
     */
    protected $_object;
    /**
     * @var [[@doctodo var_type:_previousObject]] [[@doctodo var_description:_previousObject]]
     */
    protected $_previousObject;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        Yii::$app->on(Application::EVENT_BEFORE_REQUEST, [$this, 'startRequest']);
    }

    /**
     * [[@doctodo method_description:startRequest]].
     */
    public function startRequest()
    {
        if (isset($_GET['p'])) {
            $this->_previousObject = Registry::getObject($_GET['p']);
        }
    }

    /**
     * Set object.
     *
     * @param [[@doctodo param_type:object]] $object [[@doctodo param_description:object]]
     */
    public function setObject($object)
    {
        $this->_object = $object;
    }

    /**
     * Get object.
     *
     * @return [[@doctodo return_type:getObject]] [[@doctodo return_description:getObject]]
     */
    public function getObject()
    {
        return $this->_object;
    }

    /**
     * Get previous object.
     *
     * @return [[@doctodo return_type:getPreviousObject]] [[@doctodo return_description:getPreviousObject]]
     */
    public function getPreviousObject()
    {
        return $this->_previousObject;
    }
}
