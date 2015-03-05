<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets;

use infinite\base\collector\CollectedObjectTrait;
use Yii;

/**
 * Item [[@doctodo class_description:cascade\components\web\widgets\Item]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Item extends \infinite\base\collector\Item implements \infinite\base\collector\CollectedObjectInterface
{
    use CollectedObjectTrait;

    /**
     * @var [[@doctodo var_type:name]] [[@doctodo var_description:name]]
     */
    public $name;
    /**
     * @var [[@doctodo var_type:widget]] [[@doctodo var_description:widget]]
     */
    public $widget;
    /**
     * @var [[@doctodo var_type:tab]] [[@doctodo var_description:tab]]
     */
    public $tab;
    /**
     * @var [[@doctodo var_type:_priority]] [[@doctodo var_description:_priority]]
     */
    public $_priority = 0;
    /**
     * @var [[@doctodo var_type:locations]] [[@doctodo var_description:locations]]
     */
    public $locations = [];
    /**
     * @var [[@doctodo var_type:_section]] [[@doctodo var_description:_section]]
     */
    protected $_section;
    /**
     * @var [[@doctodo var_type:settings]] [[@doctodo var_description:settings]]
     */
    public $settings = [];

    /**
     * @inheritdoc
     */
    public function getObject()
    {
        if (is_null($this->widget)) {
            return;
        }
        $object = Yii::createObject($this->widget);
        $object->settings = $this->settings;
        $object->collectorItem = $this;

        return $object;
    }

    /**
     * Get section.
     *
     * @param array $settings [[@doctodo param_description:settings]] [optional]
     *
     * @return [[@doctodo return_type:getSection]] [[@doctodo return_description:getSection]]
     */
    public function getSection($parent = null, $settings = [])
    {
        $settings = array_merge($this->settings, $settings);
        if (is_null($this->_section)) {
            $this->_section = $this->owner->getSection($parent, $settings);
        }
        if (is_callable($this->_section) || (is_array($this->_section) && !empty($this->_section[0]) && is_object($this->_section[0]))) {
            return $this->evaluateExpression($this->_section, ['parent' => $parent, 'settings' => $settings]);
        }

        return $this->_section;
    }

    /**
     * Set section.
     */
    public function setSection($value)
    {
        $this->_section = $value;
    }

    /**
     * Set priority.
     */
    public function setPriority($priority)
    {
        $this->_priority = $priority;
    }

    /**
     * Get priority.
     *
     * @return [[@doctodo return_type:getPriority]] [[@doctodo return_description:getPriority]]
     */
    public function getPriority()
    {
        return $this->_priority;
    }
}
