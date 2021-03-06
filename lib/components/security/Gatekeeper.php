<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\security;

use cascade\modules\core\TypeAccount\models\ObjectAccount;
use canis\base\exceptions\Exception;
use canis\db\Query;
use Yii;

/**
 * Gatekeeper [[@doctodo class_description:cascade\components\security\Gatekeeper]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Gatekeeper extends \canis\security\Gatekeeper
{
    /**
     * @inheritdoc
     */
    public $objectAccessClass = 'cascade\components\security\ObjectAccess';

    /**
     * Get primary account.
     *
     * @return [[@doctodo return_type:getPrimaryAccount]] [[@doctodo return_description:getPrimaryAccount]]
     */
    public function getPrimaryAccount()
    {
        return ObjectAccount::get(Yii::$app->params['primaryAccount'], false);
    }

    /**
     * @inheritdoc
     */
    public function setAuthority($authority)
    {
        if (!isset($authority['type'])
            || !($authorityTypeItem = Yii::$app->collectors['types']->getOne($authority['type']))
            || !($authorityType = $authorityTypeItem->object)) {
            throw new Exception("Access Control Authority is not set up correctly!");
        }
        unset($authority['type']);
        $authority['handler'] = $authorityType;

        return parent::setAuthority($authority);
    }

    /**
     * @inheritdoc
     */
    public function getAuthority()
    {
        if (is_null($this->_authority)) {
            $this->authority = ['type' => 'User'];
        }

        return $this->_authority;
    }

    /**
     * @inheritdoc
     */
    public function getControlledObject($object, $modelClass = null, $params = [])
    {
        $defaultParams = [
            'followParents' => true,
            'doNotFollow' => [],
        ];
        $params = array_merge($defaultParams, $params);
        $objects = [];
        if (is_null($modelClass) && isset($object) && is_object($object)) {
            $modelClass = get_class($object);
        }
        $parent = parent::getControlledObject($object, $modelClass);
        if ($parent) {
            if (is_array($parent)) {
                $objects = array_merge($objects, $parent);
            } else {
                $objects[] = $parent->primaryKey;
            }
        }
        if (!empty($modelClass)) {
            $dummyModel = new $modelClass();
            if (isset($dummyModel->objectType) && ($objectType = $dummyModel->objectType) && $objectType && isset($objectType->objectTypeModel)) {
                $objects[] = $objectType->objectTypeModel->primaryKey;

                if ($params['followParents'] && is_object($object) && !in_array($object->primaryKey, $params['doNotFollow']) && $objectType->inheritParentAccess) {
                    $params['doNotFollow'][] = $object->primaryKey;
                    $parentIds = $object->queryParentRelations(false, [], ['disableCheckAccess' => true])->select(['parent_object_id'])->column();
                    if (!empty($parentIds)) {
                        $registryClass = Yii::$app->classes['Registry'];
                        foreach ($parentIds as $parentId) {
                            $parent = $registryClass::getObject($parentId, false);
                            if ($parent) {
                                $parentAccessingObjects = $this->getControlledObject($parent, get_class($parent), $params);
                                if (!empty($parentAccessingObjects)) {
                                    //print_r([$objects, $parentAccessingObjects, array_merge($objects, $parentAccessingObjects)]);
                                    $objects = array_merge($objects, $parentAccessingObjects);
                                }
                            }
                        }
                    }
                }
            }
        }
        if (empty($objects)) {
            return false;
        }

        return array_unique($objects);
    }

    /**
     * @inheritdoc
     */
    public function buildInnerRoleCheckConditions(&$innerOnConditions, $innerAlias, $query)
    {
        if ($query instanceof \canis\db\ActiveQuery
            && $query->model->getBehavior('Relatable') !== null
            && isset($query->model->objectType)
            && is_object($query->model->objectType)
            && $query->model->objectType->inheritParentAccess) {
            $superInnerAlias = 'relation_role_check';
            $subquery = $query->model->queryParentRelations(false, ['alias' => $superInnerAlias]);
            if (isset($subquery->where[1])) {
                $firstKey = array_keys($subquery->where[1])[0];
                unset($subquery->where[1][$firstKey]);
                $subquery->where[1] = $firstKey . ' = {{' . $query->primaryAlias . '}}.[[' . $query->primaryTablePk . ']]';
            }
            $subquery->select(['{{' . $superInnerAlias . '}}.[[parent_object_id]]']);
            $innerOnConditions[] = '{{' . $innerAlias . '}}.[[controlled_object_id]] IN (' . $subquery->createCommand()->rawSql . ')';
            //echo $subquery->createCommand()->rawSql;exit;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function getActionMap($controlledObject = null)
    {
        $map = [];
        $map['associate'] = 'update';
        if (isset($controlledObject) && !empty($controlledObject->objectType)) {
            $typeMap = $controlledObject->objectType->getActionMap($controlledObject);
            $map = array_merge($map, $typeMap);
        }

        return $map;
    }
}
