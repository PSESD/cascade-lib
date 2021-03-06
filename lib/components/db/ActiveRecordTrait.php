<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\db;

use cascade\models\Registry;
use cascade\models\Relation;

trait ActiveRecordTrait
{
    public $_moduleHandler;

    public static function getRegistryClass()
    {
        return Registry::className();
    }

    public static function getRelationClass()
    {
        return Relation::className();
    }

    public function badFields()
    {
        $badFields = parent::badFields();
        $badFields[] = 'archived';
        foreach ($this->getBehaviors() as $behavior) {
            if (method_exists($behavior, 'badFields')) {
                $badFields = array_merge($badFields, $behavior->badFields());
            }
        }
        foreach ($this->attributes() as $attr) {
            if (preg_match('/\_user\_id$/', $attr) === 1) {
                $badFields[] = $attr;
            }
        }

        return array_unique($badFields);
    }

    public function getTabularId()
    {
        if (is_null($this->_tabularId)) {
            if (is_null($this->_moduleHandler) || $this->_moduleHandler === self::FORM_PRIMARY_MODEL) {
                //return false;
                //$this->_moduleHandler = self::FORM_PRIMARY_MODEL;
                $this->_tabularId = self::getPrimaryTabularId();
            } else {
                $this->_tabularId = self::generateTabularId($this->_moduleHandler);
            }
        }

        return $this->_tabularId;
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return array_merge($behaviors, []);
    }
}
