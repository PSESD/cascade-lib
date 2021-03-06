<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db\behaviors;

use cascade\components\types\Relationship;
use Yii;

/**
 * PrimaryRelation [[@doctodo class_description:cascade\components\db\behaviors\PrimaryRelation]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class PrimaryRelation extends \canis\db\behaviors\PrimaryRelation
{
    /**
     * @var [[@doctodo var_type:_relationship]] [[@doctodo var_description:_relationship]]
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
     *
     * @return [[@doctodo return_type:getRelationship]] [[@doctodo return_description:getRelationship]]
     */
    public function getRelationship()
    {
        if (is_null($this->_relationship)) {
            $parentObject = $this->owner->getParentObject(false);
            $childObject = $this->owner->getChildObject(false);
            if ($parentObject && $childObject) {
                if (empty($parentObject->objectTypeItem) || empty($childObject->objectTypeItem)) {
                    return false;
                }
                $this->_relationship = Relationship::getOne($parentObject->objectTypeItem, $childObject->objectTypeItem);
            }
        }

        return $this->_relationship;
    }

    /**
     * Set relationship.
     *
     * @param cascade\components\types\Relationship $value [[@doctodo param_description:value]]
     */
    public function setRelationship(Relationship $value)
    {
        $this->_relationship = $value;
    }
}
