<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\form\fields;

use cascade\components\db\fields\Base as DbBaseField;

/**
 * FieldTypeDetector [@doctodo write class description for FieldTypeDetector].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class FieldTypeDetector extends \infinite\base\object
{
    /*
     */
    public static function detect(DbBaseField $field)
    {
        if (!$field->human) {
            return 'hidden';
        } else {
            $fieldType = $type = 'text';
            $dbMap = ['date' => 'date'];

            $fieldSchema = $field->fieldSchema;
            if ($fieldSchema->dbType === 'tinyint(1)') {
                return 'boolean';
            }
            if ($field->multiline) {
                return 'textarea';
            } elseif ($fieldSchema->type === 'boolean') {
                return 'boolean';
            } elseif (isset($dbMap[$fieldSchema->dbType])) {
                return $dbMap[$fieldSchema->dbType];
            }

            return 'text';
        }
    }
}
