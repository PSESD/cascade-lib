<?php
namespace cascade\components\db;

/**
 * ActiveQuery [[@doctodo class_description:cascade\components\db\ActiveQuery]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class ActiveQuery extends \canis\db\ActiveQuery
{
    /**
     * [[@doctodo method_description:andWhereFromQuery]].
     *
     * @param [[@doctodo param_type:instructions]] $instructions [[@doctodo param_description:instructions]]
     *
     * @return [[@doctodo return_type:andWhereFromQuery]] [[@doctodo return_description:andWhereFromQuery]]
     */
    public function andWhereFromQuery($instructions)
    {
        $where = $this->buildWhereFromQuery($instructions);
        if (!$where) {
            return false;
        }
        $this->andWhere($where);

        return true;
    }

    /**
     * [[@doctodo method_description:buildWhereFromQuery]].
     *
     * @param [[@doctodo param_type:instructions]] $instructions [[@doctodo param_description:instructions]]
     *
     * @return [[@doctodo return_type:buildWhereFromQuery]] [[@doctodo return_description:buildWhereFromQuery]]
     */
    public function buildWhereFromQuery($instructions)
    {
        $where = [];
        if (!isset($instructions['condition'])
            || !in_array($instructions['condition'], ['AND', 'OR'])) {
            return false;
        }
        $where[] = $instructions['condition'];
        foreach ($instructions['rules'] as $rule) {
            $parsedRule = $this->parseRule($rule);
            if ($parsedRule) {
                $where[] = $parsedRule;
            }
        }

        return $where;
    }

    /**
     * [[@doctodo method_description:parseRule]].
     *
     * @param [[@doctodo param_type:rule]] $rule [[@doctodo param_description:rule]]
     *
     * @return [[@doctodo return_type:parseRule]] [[@doctodo return_description:parseRule]]
     */
    protected function parseRule($rule)
    {
        if (isset($rule['condition'])) {
            return $this->buildWhereFromQuery($rule);
        }
        if (!isset($rule['field']) || !isset($rule['operator'])) {
            return false;
        }
        if (!isset($rule['value'])) {
            $rule['value'] = null;
        }
        $modelClass = $this->modelClass;
        $dummyModel = new $modelClass();
        if ($dummyModel->hasAttribute($rule['field'])) {
            return $this->buildLogic($rule['field'], $rule['operator'], $rule['value']);
        } else {
            \d($rule);
            exit;
        }
    }

    /**
     * [[@doctodo method_description:buildLogic]].
     *
     * @param [[@doctodo param_type:field]]    $field    [[@doctodo param_description:field]]
     * @param [[@doctodo param_type:operator]] $operator [[@doctodo param_description:operator]]
     * @param [[@doctodo param_type:value]]    $value    [[@doctodo param_description:value]] [optional]
     *
     * @return [[@doctodo return_type:buildLogic]] [[@doctodo return_description:buildLogic]]
     */
    protected function buildLogic($field, $operator, $value = null)
    {
        $operatorMap = [
            'equal' => '=',
            'not_equal' => '!=',
            'less' => '<',
            'less_or_equal' => '<=',
            'greater' => '>',
            'greater_or_equal' => '>=',

        ];
        switch ($operator) {
            case 'equal':
                return ['or like', $field, $value, false];
            break;
            case 'not_equal':
                return ['or not like', $field, $value, false];
            break;
            case 'begins_with':
                return ['like', $field, $value . '%', false];
            break;
            case 'not_begins_with':
                return ['not like', $field, $value . '%', false];
            break;
            case 'contains':
                return ['like', $field, '%' . $value . '%', false];
            case 'not_contains':
                return ['not like', $field, '%' . $value . '%', false];
            break;
            case 'ends_with':
                return ['like', $field, '%' . $value, false];
            break;
            case 'not_ends_with':
                return ['not like', $field, '%' . $value, false];
            break;
            case 'less':
            case 'less_or_equal':
            case 'greater':
            case 'greater_or_equal':
                $paramName = ':' . md5(serialize([microtime(true), mt_rand(), $value]));
                $this->addParams([$paramName => $value]);

                return $field . $operatorMap[$operator] . $paramName;
            break;
            case 'in':
                $value = strtr($value, ', ', ',');

                return $this->buildLogic($field, 'equal', explode(",", $value));
            break;
            case 'not_in':
                $value = strtr($value, ', ', ',');

                return $this->buildLogic($field, 'not_equal', explode(",", $value));
            break;
            case 'is_empty':
                return $field . '=""';
            break;
            case 'is_not_empty':
                return $field . '!=""';
            break;
            case 'is_null':
                return $field . ' IS NULL';
            break;
            case 'is_not_null':
                return $field . ' IS NOT NULL';
            break;
        }

        return false;
    }

    /**
     * [[@doctodo method_description:buildContainsQuery]].
     *
     * @param [[@doctodo param_type:queryString]] $queryString [[@doctodo param_description:queryString]]
     *
     * @return [[@doctodo return_type:buildContainsQuery]] [[@doctodo return_description:buildContainsQuery]]
     */
    public function buildContainsQuery($queryString)
    {
        $queryString = trim($queryString);
        if (empty($queryString)) {
            return [];
        }
        $modelClass = $this->modelClass;
        $queryTerms = $modelClass::prepareSearchTerms($queryString);
        $searchFields = $modelClass::parseSearchFields($modelClass::searchFields());
        $localSearchFieldsRaw = $searchFields['local'];
        $localSearchFields = [];
        foreach ($localSearchFieldsRaw as $fieldGroup) {
            $localSearchFields = array_merge($localSearchFields, $fieldGroup);
        }
        $method = 'single';
        if ($method === 'group') {
            $query = ['condition' => 'AND', 'rules' => []];
            foreach ($queryTerms as $queryTerm) {
                $subquery = ['condition' => 'OR', 'rules' => []];
                foreach ($localSearchFields as $searchField) {
                    $subquery['rules'][] = [
                        'field' => $searchField,
                        'operator' => 'contains',
                        'value' => $queryTerm,
                    ];
                }
                $query['rules'][] = $subquery;
            }
        } else {
            $query = ['condition' => 'OR', 'rules' => []];
            foreach ($queryTerms as $queryTerm) {
                foreach ($localSearchFields as $searchField) {
                    $query['rules'][] = [
                        'field' => $searchField,
                        'operator' => 'contains',
                        'value' => $queryTerm,
                    ];
                }
            }
        }

        return $query;
    }
}
