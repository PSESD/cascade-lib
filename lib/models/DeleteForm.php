<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\models;

use cascade\components\types\Relationship;
use canis\base\exceptions\Exception;
use yii\base\Model;

/**
 * DeleteForm [[@doctodo class_description:cascade\models\DeleteForm]]
 * LoginForm is the model behind the login form.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DeleteForm extends Model
{
    /**
     * @var [[@doctodo var_type:confirm]] [[@doctodo var_description:confirm]]
     */
    public $confirm = false;
    /**
     * @var [[@doctodo var_type:_target]] [[@doctodo var_description:_target]]
     */
    protected $_target;
    /**
     * @var [[@doctodo var_type:relationModel]] [[@doctodo var_description:relationModel]]
     */
    public $relationModel;
    /**
     * @var [[@doctodo var_type:relationshipWith]] [[@doctodo var_description:relationshipWith]]
     */
    public $relationshipWith;
    /**
     * @var [[@doctodo var_type:relationship]] [[@doctodo var_description:relationship]]
     */
    public $relationship;
    /**
     * @var [[@doctodo var_type:object]] [[@doctodo var_description:object]]
     */
    public $object;
    /**
     * @var [[@doctodo var_type:_possibleTargets]] [[@doctodo var_description:_possibleTargets]]
     */
    protected $_possibleTargets;

    /**
     * [[@doctodo method_description:rules]].
     *
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['confirm', 'target'], 'safe'],
        ];
    }

    /**
     * Get labels.
     *
     * @return [[@doctodo return_type:getLabels]] [[@doctodo return_description:getLabels]]
     */
    public function getLabels()
    {
        $labels = [];
        $labels['delete_object'] = [
            'short' => 'Delete ' . $this->object->objectType->title->getSingular(true),
            'long' => 'delete the ' . $this->object->objectType->title->getSingular(false) . ' <em>' . $this->object->descriptor . '</em>',
            'past' => $this->object->objectType->title->getSingular(false) . ' <em>' . $this->object->descriptor . '</em> has been deleted',
            'options' => ['class' => 'btn-danger'],
            'response' => 'home',
        ];
        $labels['archive_object'] = [
            'short' => 'Archive ' . $this->object->objectType->title->getSingular(true),
            'long' => 'archive the ' . $this->object->objectType->title->getSingular(false) . ' <em>' . $this->object->descriptor . '</em>',
            'past' => $this->object->objectType->title->getSingular(false) . ' <em>' . $this->object->descriptor . '</em> has been archived',
            'response' => 'refresh',
        ];
        $labels['unarchive_object'] = [
            'short' => 'Unarchive ' . $this->object->objectType->title->getSingular(true),
            'long' => 'unarchive the ' . $this->object->objectType->title->getSingular(false) . ' <em>' . $this->object->descriptor . '</em>',
            'past' => $this->object->objectType->title->getSingular(false) . ' <em>' . $this->object->descriptor . '</em> has been unarchived',
            'response' => 'refresh',
        ];
        if (isset($this->relationshipWith)) {
            $labels['delete_relationship'] = [
                'short' => 'Delete Relationship',
                'long' => 'delete the relationship between <em>' . $this->object->descriptor . '</em> and <em>' . $this->relationshipWith->descriptor . '</em>',
                'past' => 'the relationship between <em>' . $this->object->descriptor . '</em> and <em>' . $this->relationshipWith->descriptor . '</em> has been deleted',
                'options' => ['class' => 'btn-warning'],
            ];
            $labels['end_relationship'] = [
                'short' => 'End Relationship',
                'long' => 'end the relationship between <em>' . $this->object->descriptor . '</em> and <em>' . $this->relationshipWith->descriptor . '</em>',
                'past' => 'the relationship between <em>' . $this->object->descriptor . '</em> and <em>' . $this->relationshipWith->descriptor . '</em> has been ended',
            ];
        }

        return $labels;
    }

    /**
     * Get target.
     *
     * @return [[@doctodo return_type:getTarget]] [[@doctodo return_description:getTarget]]
     */
    public function getTarget()
    {
        if (is_null($this->_target) && !empty($this->possibleTargets)) {
            $this->_target = $this->possibleTargets[0];
        }

        return $this->_target;
    }

    /**
     * [[@doctodo method_description:canDeleteObject]].
     *
     * @return [[@doctodo return_type:canDeleteObject]] [[@doctodo return_description:canDeleteObject]]
     */
    public function canDeleteObject()
    {
        if ($this->object->objectType->hasDashboard && isset($this->relationship) && !$this->relationship->isHasOne()) {
            return false;
        }

        return $this->object->can('delete');
    }

    /**
     * [[@doctodo method_description:canArchiveObject]].
     *
     * @return [[@doctodo return_type:canArchiveObject]] [[@doctodo return_description:canArchiveObject]]
     */
    public function canArchiveObject()
    {
        if ($this->object->objectType->hasDashboard && isset($this->relationship) && !$this->relationship->isHasOne()) {
            return false;
        }

        return $this->object->can('archive');
    }

    /**
     * [[@doctodo method_description:canDeleteRelation]].
     *
     * @return [[@doctodo return_type:canDeleteRelation]] [[@doctodo return_description:canDeleteRelation]]
     */
    public function canDeleteRelation()
    {
        if (isset($this->relationModel)) {
            if (!$this->object->allowRogue($this->relationModel)) {
                return false;
            }
            if (!$this->object->canDeleteAssociation($this->relationshipWith)) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * [[@doctodo method_description:canEndRelation]].
     *
     * @return [[@doctodo return_type:canEndRelation]] [[@doctodo return_description:canEndRelation]]
     */
    public function canEndRelation()
    {
        if (!isset($this->relationModel) || !isset($this->relationship)) {
            return false;
        }
        if (!$this->relationship->temporal) {
            return false;
        }
        if (!$this->object->canUpdateAssociation($this->relationshipWith)) {
            return false;
        }

        return true;
    }

    /**
     * [[@doctodo method_description:hasObjectTargets]].
     *
     * @return [[@doctodo return_type:hasObjectTargets]] [[@doctodo return_description:hasObjectTargets]]
     */
    public function hasObjectTargets()
    {
        foreach ($this->possibleTargets as $target) {
            if (in_array($target, ['delete_object', 'archive_object'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * [[@doctodo method_description:hasRelationshipTargets]].
     *
     * @return [[@doctodo return_type:hasRelationshipTargets]] [[@doctodo return_description:hasRelationshipTargets]]
     */
    public function hasRelationshipTargets()
    {
        foreach ($this->possibleTargets as $target) {
            if (in_array($target, ['end_relationship', 'delete_relationship'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get possible targets.
     *
     * @return [[@doctodo return_type:getPossibleTargets]] [[@doctodo return_description:getPossibleTargets]]
     */
    public function getPossibleTargets()
    {
        if (is_null($this->_possibleTargets)) {
            $this->_possibleTargets = [];

            if ($this->canEndRelation()) {
                $this->_possibleTargets[] = 'end_relationship';
            }

            if ($this->canDeleteRelation()) {
                $this->_possibleTargets[] = 'delete_relationship';
            }

            if ($this->canArchiveObject()) {
                if ($this->object->archived) {
                    $this->_possibleTargets[] = 'unarchive_object';
                } else {
                    $this->_possibleTargets[] = 'archive_object';
                }
            }

            if ($this->canDeleteObject()) {
                $this->_possibleTargets[] = 'delete_object';
            }
        }

        return $this->_possibleTargets;
    }

    /**
     * Set target.
     *
     * @param [[@doctodo param_type:value]] $value [[@doctodo param_description:value]]
     *
     * @throws Exception [[@doctodo exception_description:Exception]]
     */
    public function setTarget($value)
    {
        if (in_array($value, $this->possibleTargets)) {
            $this->_target = $value;
        } else {
            throw new Exception('Unknown deletion target ' . $value);
        }
    }

    /**
     * Get target label.
     *
     * @return [[@doctodo return_type:getTargetLabel]] [[@doctodo return_description:getTargetLabel]]
     */
    public function getTargetLabel()
    {
        if (!isset($this->labels[$this->target])) {
            return ['long' => 'unknown', 'short' => 'unknown'];
        }

        return $this->labels[$this->target];
    }

    /**
     * Get target descriptor.
     *
     * @return [[@doctodo return_type:getTargetDescriptor]] [[@doctodo return_description:getTargetDescriptor]]
     */
    public function getTargetDescriptor()
    {
        if ($this->target === 'object') {
            return $this->object->descriptor;
        } else {
            return 'relationship';
        }
    }

    /**
     * [[@doctodo method_description:handle]].
     *
     * @return [[@doctodo return_type:handle]] [[@doctodo return_description:handle]]
     */
    public function handle()
    {
        $result = false;
        switch ($this->target) {
            case 'delete_object':
                $result = true;
                if (!is_null($this->relationModel)) {
                    $result = $this->relationModel->suppressAudit()->delete();
                }
                $result = $result && $this->object->delete();
            break;
            case 'archive_object':
                $result = $this->object->archive();
            break;
            case 'unarchive_object':
                $result = $this->object->unarchive();
            break;
            case 'delete_relationship':
                $result = $this->relationModel->delete();
            break;
            case 'end_relationship':
                $result = $this->relationModel->endRelationship();
            break;
        }

        return $result;
    }
}
