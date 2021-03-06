<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\web\form;

use Yii;

trait FormObjectTrait
{
    public $owner;
    public $isValid = true;
    public $generatorClass = 'cascade\components\web\form\Generator';

    public function output()
    {
        echo $this->generate();
    }

    public function getGenerator()
    {
        if ($this->owner instanceof Generator) {
            return $this->owner;
        }
        if (isset($this->owner->generator)) {
            return $this->owner->getGenerator();
        }

        return Yii::createObject(['class' => $this->generatorClass]);
    }

    public function getSegment()
    {
        if (is_null($this->owner)) {
            return false;
        }
        if ($this->owner instanceof Segment) {
            return $this->owner;
        }

        return $this->owner->segment;
    }
}
