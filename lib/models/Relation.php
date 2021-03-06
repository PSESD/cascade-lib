<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\models;

use cascade\components\db\ActiveRecordTrait;
use cascade\components\types\Module as TypeModule;
use cascade\components\types\Relationship;
use cascade\components\types\RelationshipEvent;
use canis\caching\Cacher;
use Yii;

/**
 * Relation is the model class for table "relation".
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Relation extends \canis\db\models\Relation
{
    use ActiveRecordTrait;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'Taxonomy' => [
                'class' => 'cascade\components\db\behaviors\ActiveTaxonomy',
                'viaModelClass' => 'RelationTaxonomy',
                'relationKey' => 'relation_id',
            ],
            'PrimaryRelation' => [
                'class' => 'cascade\components\db\behaviors\PrimaryRelation',
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public static function queryBehaviors()
    {
        return array_merge(parent::queryBehaviors(),
            [
                'Taxonomy' => [
                    'class' => 'cascade\components\db\behaviors\QueryTaxonomy',
                    'viaModelClass' => 'RelationTaxonomy',
                    'relationKey' => 'relation_id',
                ],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function afterSaveRelation($event)
    {
        parent::afterSaveRelation($event);
        if ($this->wasDirty) {
            Cacher::invalidateGroup(['Object', 'relations', $this->parent_object_id]);
            Cacher::invalidateGroup(['Object', 'relations', $this->child_object_id]);
            $parentObject = $this->getParentObject(false);
            $childObject =  $this->getChildObject(false);
            $relationship = $this->relationship;
            $relationshipEvent = new RelationshipEvent(['parentEvent' => $event, 'parentObject' => $parentObject, 'childObject' => $childObject, 'relationship' => $relationship]);
            if (!empty($parentObject) && !empty($parentObject->objectType)) {
                $parentObject->objectType->trigger(TypeModule::EVENT_RELATION_CHANGE, $relationshipEvent);
            }
            if (!empty($childObject) && !empty($childObject->objectType)) {
                $childObject->objectType->trigger(TypeModule::EVENT_RELATION_CHANGE, $relationshipEvent);
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function afterDeleteRelation($event)
    {
        parent::afterDeleteRelation($event);
        Cacher::invalidateGroup(['Object', 'relations', $this->parent_object_id]);
        Cacher::invalidateGroup(['Object', 'relations', $this->child_object_id]);

        $parentObject = $this->getParentObject(false);
        $childObject =  $this->getChildObject(false);
        $relationshipEvent = new RelationshipEvent(['parentEvent' => $event, 'parentObject' => $parentObject, 'childObject' => $childObject, 'relationship' => $this->relationship]);
        if ($parentObject) {
            $parentObject->objectType->trigger(TypeModule::EVENT_RELATION_DELETE, $relationshipEvent);
        }
        if ($childObject) {
            $childObject->objectType->trigger(TypeModule::EVENT_RELATION_DELETE, $relationshipEvent);
        }
    }

    /**
     * [[@doctodo method_description:addFields]].
     *
     * @param [[@doctodo param_type:caller]]       $caller       [[@doctodo param_description:caller]]
     * @param [[@doctodo param_type:fields]]       $fields       [[@doctodo param_description:fields]]
     * @param [[@doctodo param_type:relationship]] $relationship [[@doctodo param_description:relationship]]
     * @param [[@doctodo param_type:owner]]        $owner        [[@doctodo param_description:owner]]
     */
    public function addFields($caller, &$fields, $relationship, $owner)
    {
        $baseField = ['model' => $this];
        if (isset($this->id)) {
            $fields['relation:id'] = $caller->createField('id', $owner, $baseField);
        }
        if (!empty($relationship->taxonomy)
                && ($taxonomyItem = Yii::$app->collectors['taxonomies']->getOne($relationship->taxonomy))
                && ($taxonomy = $taxonomyItem->object)
                && $taxonomy) {
            $fieldName = 'relation:taxonomy_id';
            $fieldSchema = $caller->createColumnSchema('taxonomy_id', ['type' => 'taxonomy', 'phpType' => 'object', 'dbType' => 'taxonomy', 'allowNull' => true]);

            $fields[$fieldName] = $caller->createTaxonomyField($fieldSchema, $taxonomyItem, $owner, $baseField);
        }
    }

    /**
     * Get relationship.
     *
     * @return [[@doctodo return_type:getRelationship]] [[@doctodo return_description:getRelationship]]
     */
    public function getRelationship()
    {
        if (!isset($this->parentObject) || empty($this->parentObject->objectTypeItem)) {
            return false;
        }
        if (!isset($this->childObject) || empty($this->childObject->objectTypeItem)) {
            return false;
        }

        return Relationship::getOne($this->parentObject->objectTypeItem, $this->childObject->objectTypeItem);
    }
}
