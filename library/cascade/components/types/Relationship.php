<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\types;

use cascade\components\db\behaviors\Relatable;
use infinite\base\exceptions\Exception;
use Yii;

/**
 * Relationship [@doctodo write class description for Relationship].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Relationship extends \infinite\base\Object
{
    const HAS_MANY = 0x01;
    const HAS_ONE = 0x02;
    const ROLE_PARENT = 0x03;
    const ROLE_CHILD = 0x04;

    /**
     */
    protected $_parent;
    /**
     */
    protected $_child;
    /**
     */
    protected static $_cache = [];
    /**
     */
    protected $_defaultOptions = [
        'required' => false,
        'handlePrimary' => true, // should we look at parent and child primary preferences
        'taxonomy' => null,
        'temporal' => false,
        'activeAble' => false,
        'type' => self::HAS_MANY,
        'parentInherit' => false,
    ];
    /**
     */
    protected $_options = [];
    /**
     */
    protected static $_relationships = [];

    public static function clearCache()
    {
        self::$_cache = [];
        self::$_relationships = [];
    }
    /**
     *
     */
    public function package()
    {
        return [
            'id' => $this->systemId,
            'temporal' => $this->temporal,
            'taxonomy' => $this->taxonomyPackage,
            'activeAble' => $this->activeAble,
            'type' => $this->type,
        ];
    }

    public function doHandlePrimary($role = null)
    {
        if (!$this->handlePrimary) {
            return false;
        }

        if (in_array($role, ['child', self::ROLE_CHILD])
            && $this->handlePrimary === self::ROLE_CHILD) {
            return true;
        }

        if (in_array($role, ['parent', self::ROLE_PARENT])
            && $this->handlePrimary === self::ROLE_PARENT) {
            return true;
        }

        return false;
    }

    /**
     * Get taxonomy package.
     */
    public function getTaxonomyPackage()
    {
        if (empty($this->taxonomy)) {
            return false;
        }
        $taxonomySettings = $this->taxonomy;
        if (!is_array($taxonomySettings)) {
            $taxonomySettings = ['id' => $taxonomySettings];
        }
        $taxonomy = Yii::$app->collectors['taxonomies']->getOne($taxonomySettings['id']);
        if (empty($taxonomy) || empty($taxonomy->object)) {
            return false;
        }

        return $taxonomy->package($taxonomySettings);
    }

    public function getPrimaryObject($primaryObject, $relatedObject, $role)
    {
        if (!$this->handlePrimary) {
            return false;
        }
        if ($role === 'child') {
            $primaryField = 'primary_child';
            if (!$relatedObject->objectType->getPrimaryAsChild($this->parent)) {
                // \d(['bad', $this->systemId, get_class($primaryObject), get_class($relatedObject), $role]);
                return false;
            }
            $primaryParent = $primaryObject;
        } else {
            $primaryField = 'primary_parent';
            if (!$relatedObject->objectType->getPrimaryAsParent($this->child)) {
                // \d(['bad', $this->systemId, get_class($primaryObject), get_class($relatedObject), $role]);
                return false;
            }
            $primaryParent = $relatedObject;
        }

        $key = json_encode([__FUNCTION__, $this->systemId, $primaryObject->primaryKey]);
        if (!isset(self::$_cache[$key])) {
            self::$_cache[$key] = null;
            $relationClass = Yii::$app->classes['Relation'];
            $childClass = $this->child->primaryModel;
            $relation = $relationClass::find();
            $alias = $relationClass::tableName();
            $relation->andWhere(['`' . $alias . '`.`parent_object_id`' => $primaryParent->primaryKey, '`' . $alias . '`.`' . $primaryField . '`' => 1]);
            $relation->andWhere(['or', '`' . $alias . '`.`child_object_id` LIKE :prefix']); //, '`'. $alias.'`.`child_object_id` LIKE \''.$childClass.'\''
            $relation->params[':prefix'] = $childClass::modelPrefix() . '-%';
            $primaryObject->addActiveConditions($relation, $alias);
            // \d([$this->systemId, $relation->createCommand()->rawSql, $primaryField, $role]);
            $relation = $relation->one();
            if (!empty($relation)) {
                self::$_cache[$key] = $relation;
            }
        }

        return self::$_cache[$key];
    }

    /**
     * Get primary child.
     */
    public function getPrimaryChild($parentObject)
    {
        if (!$this->handlePrimary) {
            return false;
        }
        if (!$this->child->getPrimaryAsChild($this->parent)) {
            return false;
        }
        $key = json_encode([__FUNCTION__, $this->systemId, $parentObject->primaryKey]);
        if (!isset(self::$_cache[$key])) {
            self::$_cache[$key] = null;
            $relationClass = Yii::$app->classes['Relation'];
            $childClass = $this->child->primaryModel;
            $relation = $relationClass::find();
            $alias = $relationClass::tableName();
            $relation->andWhere(['`' . $alias . '`.`parent_object_id`' => $parentObject->primaryKey, '`' . $alias . '`.`primary_child`' => 1]);
            $relation->andWhere(['or', '`' . $alias . '`.`child_object_id` LIKE :prefix']); //, '`'. $alias.'`.`child_object_id` LIKE \''.$childClass.'\''
            $relation->params[':prefix'] = $childClass::modelPrefix() . '-%';
            $parentObject->addActiveConditions($relation, $alias);
            $relation = $relation->one();
            if (!empty($relation)) {
                self::$_cache[$key] = $relation;
            }
        }

        return self::$_cache[$key];
    }

    /**
     * Get primary parent.
     */
    public function getPrimaryParent($parentObject)
    {
        if (!$this->handlePrimary) {
            return false;
        }
        if (!$this->parent->getPrimaryAsParent($this->child)) {
            return false;
        }
        $key = json_encode([__FUNCTION__, $this->systemId, $parentObject->primaryKey]);
        if (!isset(self::$_cache[$key])) {
            self::$_cache[$key] = null;
            $relationClass = Yii::$app->classes['Relation'];
            $childClass = $this->child->primaryModel;
            $relation = $relationClass::find();
            $alias = $relationClass::tableName();
            $relation->andWhere(['`' . $alias . '`.`parent_object_id`' => $parentObject->primaryKey, '`' . $alias . '`.`primary_parent`' => 1]);
            $relation->andWhere('`' . $alias . '`.`child_object_id` LIKE :prefix');
            $relation->params[':prefix'] = $childClass::modelPrefix() . '-%';
            $parentObject->addActiveConditions($relation, $alias);
            $relation = $relation->one();
            if (!empty($relation)) {
                self::$_cache[$key] = $relation;
            }
        }

        return self::$_cache[$key];
    }

    /**
     * Constructor.
     *
     * @param object  $parent
     * @param object  $child
     * @param unknown $options (optional)
     */
    public function __construct(Item $parent, Item $child, $options = [])
    {
        $this->_parent = $parent;
        $this->_child = $child;
        $this->mergeOptions($options);
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_options)) {
            return $this->_options[$name];
        } elseif (array_key_exists($name, $this->_defaultOptions)) {
            return $this->_defaultOptions[$name];
        }

        return parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function __isset($name)
    {
        if (array_key_exists($name, $this->_options)) {
            return isset($this->_options[$name]);
        } elseif (array_key_exists($name, $this->_defaultOptions)) {
            return isset($this->_defaultOptions[$name]);
        }

        return parent::__get($name);
    }

    /*
     * Get one
     * @param object  $parent
     * @param object  $child
     * @param unknown $options (optional)
     * @return unknown
     */
    public static function getOne(Item $parent, Item $child, $options = [])
    {
        $key = md5($parent->systemId . "." . $child->systemId);
        if (isset(self::$_relationships[$key])) {
            self::$_relationships[$key]->mergeOptions($options);
        } else {
            self::$_relationships[$key] = new Relationship($parent, $child, $options);
        }

        return self::$_relationships[$key];
    }

    /*
     * Get by
     */
    public static function getById($relationshipId)
    {
        $key = md5($relationshipId);
        if (isset(self::$_relationships[$key])) {
            return self::$_relationships[$key];
        }

        return false;
    }

    /*
     */
    public static function has(Item $parent, Item $child)
    {
        $key = md5($parent->systemId . "." . $child->systemId);

        return isset(self::$_relationships[$key]);
    }

    /**
     * Get has fields.
     */
    public function getHasFields()
    {
        return ($this->temporal || $this->activeAble || $this->taxonomyPackage);
    }

    /**
     *
     */
    public function isHasOne()
    {
        return $this->type === self::HAS_ONE;
    }

    /**
     *
     */
    public function isHasMany()
    {
        return $this->type === self::HAS_MANY;
    }

    /**
     *
     */
    public function companionRole($queryRole)
    {
        if ($queryRole === 'children' || $queryRole === 'child') {
            return 'parent';
        }

        return 'child';
    }

    public function getLabel($role)
    {
        $role = $this->companionRole($role);
        if ($role === 'child') {
            return 'Child ' . $this->child->title->upperSingular;
        } else {
            return 'Parent ' . $this->parent->title->upperSingular;
        }
    }

    public function getNiceId($queryRole)
    {
        $roleType = $this->roleType($queryRole);
        if (empty($roleType)) {
            return false;
        }

        return implode(':', [$this->role($queryRole), $roleType->systemId]);
    }

    public function getCompanionNiceId($queryRole)
    {
        $companionRoleType = $this->companionRoleType($queryRole);
        if (empty($companionRoleType)) {
            return false;
        }

        return implode(':', [$this->companionRole($queryRole), $companionRoleType->systemId]);
    }

    /**
     *
     */
    public function companionRoleType($queryRole)
    {
        if ($queryRole === 'children' || $queryRole === 'child') {
            return $this->parent;
        }

        return $this->child;
    }

    public function role($queryRole)
    {
        if ($queryRole === 'children' || $queryRole === 'child') {
            return 'child';
        }

        return 'parent';
    }

    /**
     *
     */
    public function roleType($queryRole)
    {
        if ($queryRole === 'children' || $queryRole === 'child') {
            return $this->child;
        }

        return $this->parent;
    }

    /**
     *
     */
    public function canLink($relationshipRole, $object)
    {
        $objectModule = $object->objectType;
        if (!$objectModule
            || ($relationshipRole === 'parent' && ($this->child->uniparental || $this->isHasOne()))
        ) {
            return false;
        }

        if (!$object->can('associate:' . $this->companionRoleType($relationshipRole)->systemId)) {
            return false;
        }

        return true;
    }

    /**
     *
     */
    public function canCreate($relationshipRole, $object)
    {
        $objectModule = $object->objectType;
        if ($this->child->hasDashboard && $relationshipRole === 'child') { // && ($this->parent->uniparental || $this->uniqueParent)

            return false;
        }

        return true;
    }

    /**
     * Get model.
     */
    public function getModel($parentObjectId, $childObjectId, $activeOnly = true)
    {
        if (is_object($parentObjectId)) {
            $parentObjectId = $parentObjectId->primaryKey;
        }
        if (is_object($childObjectId)) {
            $childObjectId = $childObjectId->primaryKey;
        }
        $key = json_encode([__FUNCTION__, $this->systemId, $parentObjectId, $activeOnly]);
        if (!isset(self::$_cache[$key])) {
            $relationClass = Yii::$app->classes['Relation'];
            $all = $relationClass::find();
            $all->where(
                ['or', 'parent_object_id=:parentObjectId', 'child_object_id=:childObjectId']
            );
            $all->params[':parentObjectId'] = $parentObjectId;
            $all->params[':childObjectId'] = $childObjectId;
            if ($activeOnly) {
                Relatable::doAddActiveConditions($all, false);
            }
            $all = $all->all();
            foreach ($all as $relation) {
                $subkey = json_encode([__FUNCTION__, $this->systemId, $relation->parent_object_id, $activeOnly]);
                if (!isset(self::$_cache[$subkey])) {
                    self::$_cache[$subkey] = [];
                }
                self::$_cache[$subkey][$relation->child_object_id] = $relation;
            }
        }
        if (isset(self::$_cache[$key]) && isset(self::$_cache[$key][$childObjectId])) {
            return self::$_cache[$key][$childObjectId];
        }

        return false;
    }

    /**
     * @param unknown $newOptions
     */
    public function mergeOptions($newOptions)
    {
        foreach ($newOptions as $k => $v) {
            if (array_key_exists($k, $this->_options)) {
                if ($this->_options[$k] !== $v) {
                    throw new Exception("Conflicting relationship settings between parent: {$this->parent->name} and child: {$this->child->name}!");
                }
            } else {
                $this->_options[$k] = $v;
            }
        }
        $this->_options = array_merge($this->_options, $newOptions);
    }

    /**
     * Set default options.
     */
    public function setDefaultOptions()
    {
        foreach ($this->_defaultOptions as $k => $v) {
            if (!array_key_exists($k, $this->_options)) {
                $this->_options[$k] = $v;
            }
        }

        return true;
    }

    /**
     * Get parent.
     *
     * @return unknown
     */
    public function getParent()
    {
        return $this->_parent->object;
    }

    /**
     * Get child.
     *
     * @return unknown
     */
    public function getChild()
    {
        return $this->_child->object;
    }

    public function getRelatedObject($baseObject, $baseRole, $primaryRelation = null)
    {
        $companionRole = $this->companionRole($baseRole);
        $companionType = $this->companionRoleType($baseRole);
        $companionModel = $companionType->primaryModel;
        if (!isset($primaryRelation) || is_array($primaryRelation)) {
            if (!is_array($primaryRelation)) {
                $primaryRelation = [];
            }
            $primaryRelation = $this->getPrimaryRelation($baseObject, $baseRole, $primaryRelation);
        }
        if (!empty($primaryRelation)) {
            if ($companionRole === 'child') {
                return $primaryRelation->childObject;
            } else {
                return $primaryRelation->parentObject;
            }
        }

        return false;
    }

    public function getPrimaryRelation($baseObject, $baseRole, $relationOptions = [])
    {
        $companionRole = $this->companionRole($baseRole);
        $companionType = $this->companionRoleType($baseRole);
        $companionModel = $companionType->primaryModel;
        if (!isset($relationOptions['order'])) {
            $relationOptions['order'] = [];
        }
        if ($companionRole === 'child') {
            array_unshift($relationOptions['order'], ['primary_child', SORT_DESC]);
            $relation = $baseObject->queryParentRelations($companionModel, $relationOptions)->one();
        } else {
            array_unshift($relationOptions['order'], ['primary_parent', SORT_DESC]);
            $relation = $baseObject->queryParentRelations($companionModel, $relationOptions)->one();
        }
        if (empty($relation)) {
            return false;
        } else {
            return $relation;
        }
    }

    /**
     * Get active.
     *
     * @return unknown
     */
    public function getActive()
    {
        return (isset($this->_child) and $this->_child->active) and (isset($this->_parent) and $this->_parent->active);
    }

    /**
     * Get options.
     */
    public function getOptions()
    {
        return array_merge($this->_defaultOptions, $this->_options);
    }

    /**
     * Get system.
     */
    public function getSystemId()
    {
        return $this->_parent->systemId . '.' . $this->_child->systemId;
    }
}
