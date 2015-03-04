<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\decorator;

use infinite\helpers\Html;

/**
 * Decorator [@doctodo write class description for Decorator].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
abstract class Decorator extends \yii\base\Behavior implements DecoratorInterface
{
    /**
     *
     */
    public function generateStart()
    {
        $parts = [];
        foreach ($this->owner->widgetClasses as $class) {
            Html::addCssClass($this->owner->htmlOptions, $class);
        }
        $parts[] = Html::beginTag('div', $this->owner->htmlOptions);

        return implode("", $parts);
    }

    /**
     *
     */
    public function generateEnd()
    {
        $parts = [];
        $parts[] = Html::endTag('div'); // panel

        return implode("", $parts);
    }

    /**
     * Get widget classes.
     */
    public function getWidgetClasses()
    {
        return [];
    }
}
