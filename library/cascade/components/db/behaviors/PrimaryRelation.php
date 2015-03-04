<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\db\behaviors;

use cascade\components\types\Relationship;
use Yii;

/**
 * PrimaryRelation [@doctodo write class description for PrimaryRelation].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class PrimaryRelation extends \infinite\db\behaviors\PrimaryRelation
{
    /**
     */
    protected $_relationship;

    /**
     * @inheritdoc
     */
    public function handlePrimary($role)
    {
        if (!parent::handlePrimary($role)) {
            return false;
        }
        if (!isset(Yii::$app->collectors['types'])) {
            return false;
        }
        if (empty($this->relationship)) {
            return false;
        }

        return $this->relationship->handlePrimary !== false;
    }

    /**
     * Get relationship.
     */
    public function getRelationship()
    {
        if (is_null($this->_relationship)) {
            $parentObject = $this->owner->getParentObject(false);
            $childObject = $this->owner->getChildObject(false);
            if ($parentObject && $childObject) {
                $this->_relationship = Relationship::getOne($parentObject->objectTypeItem, $childObject->objectTypeItem);
            }
        }

        return $this->_relationship;
    }

    /**
     * Set relationship.
     */
    public function setRelationship(Relationship $value)
    {
        $this->_relationship = $value;
    }
}
