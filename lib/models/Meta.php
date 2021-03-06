<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\models;

/**
 * Meta is the model class for table "meta".
 *
 * @property string $id
 * @property string $registry_id
 * @property string $meta_key_id
 * @property string $value_text
 * @property integer $value_int
 * @property double $value_float
 * @property string $value_datetime
 * @property string $created
 * @property string $modified
 * @property MetaKey $metaKey
 * @property Registry $registry
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Meta extends \cascade\components\db\ActiveRecord
{
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
    public static function tableName()
    {
        return 'meta';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['registry_id', 'meta_key_id'], 'required'],
            [['value_text'], 'string'],
            [['value_int'], 'integer'],
            [['value_float'], 'number'],
            [['value_datetime', 'created', 'modified'], 'safe'],
            [['registry_id', 'meta_key_id'], 'string', 'max' => 36],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'registry_id' => 'Registry ID',
            'meta_key_id' => 'Meta Key ID',
            'value_text' => 'Value Text',
            'value_int' => 'Value Int',
            'value_float' => 'Value Float',
            'value_datetime' => 'Value Datetime',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * Get meta key.
     *
     * @return \yii\db\ActiveRelation
     */
    public function getMetaKey()
    {
        return $this->hasOne(MetaKey::className(), ['id' => 'meta_key_id']);
    }

    /**
     * Get registry.
     *
     * @return \yii\db\ActiveRelation
     */
    public function getRegistry()
    {
        return $this->hasOne(Registry::className(), ['id' => 'registry_id']);
    }
}
