<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db\behaviors\auditable;

/**
 * CreateEvent [[@doctodo class_description:cascade\components\db\behaviors\auditable\CreateEvent]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class CreateEvent extends \canis\db\behaviors\auditable\CreateEvent
{
    /**
     * @inheritdoc
     */
    public function getVerb()
    {
        if (isset($this->directObject->objectType) && !is_object($this->directObject->objectType)) {
            \d($this->directObject->objectType);
            \d(get_class($this->directObject));
            \d($this->model->id);
            exit;
        }
        if (isset($this->directObject->objectType) && $this->directObject->objectType->getInsertVerb($this->directObject) !== null) {
            return $this->directObject->objectType->getInsertVerb($this->directObject);
        }

        return parent::getVerb();
    }
}
