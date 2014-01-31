<?php

namespace cascade\models;

use cascade\components\types\ActiveRecordTrait;

class Role extends \infinite\db\models\Role
{
	use ActiveRecordTrait {
		behaviors as baseBehaviors;
	}
	
	/**
	 * @inheritdoc
	 */
	public static function isAccessControlled()
    {
        return false;
    }

	/**
	 * @inheritdoc
	 */
	public function behaviors()
	{
		return array_merge(parent::behaviors(), self::baseBehaviors(), []);
	}
}
