<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets;

use infinite\base\exceptions\Exception;
use Yii;

/**
 * Module [@doctodo write class description for Module].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Module extends \cascade\components\base\CollectorModule
{
    /**
     */
    public $title;
    /**
     */
    public $icon = 'ic-icon-info';
    /**
     */
    public $priority = 1000; //lower is better

    public $locations = []; //lower is better

    /**
     */
    public $widgetNamespace;

    public function getCollectorName()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getModuleType()
    {
        return 'Widget';
    }

    /**
     *
     */
    public function onAfterInit($event)
    {
        if (isset(Yii::$app->collectors['widgets']) and !Yii::$app->collectors['widgets']->registerMultiple($this, $this->widgets())) {
            throw new Exception('Could not register widgets for ' . $this->systemId . '!');
        }

        return parent::onAfterInit($event);
    }

    /**
     *
     */
    public function widgets()
    {
        $widgets = [];
        $className = $this->widgetNamespace . '\\' . 'Content';
        @class_exists($className);
        if (class_exists($className, false)) {
            $summaryWidget = [];
            $id = $this->systemId . 'Content';
            $summaryWidget['widget'] = [
                'class' => $className,
                'icon' => $this->icon,
                'owner' => $this,
            ];
            $summaryWidget['locations'] = $this->locations;
            $summaryWidget['priority'] = $this->priority;
            $widgets[$id] = $summaryWidget;
        }
        //\d($widgets);exit;
        return $widgets;
    }

    /**
     * Get short name.
     */
    public function getShortName()
    {
        preg_match('/Widget([A-Za-z]+)\\\Module/', get_class($this), $matches);
        if (!isset($matches[1])) {
            throw new Exception(get_class($this) . " is not set up correctly!");
        }

        return $matches[1];
    }
}
