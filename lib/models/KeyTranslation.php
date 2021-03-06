<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\models;

use Yii;

/**
 * KeyTranslation is the model class for table "key_translation".
 *
 * @property string $id
 * @property string $data_interface_id
 * @property string $registry_id
 * @property string $key
 * @property string $created
 * @property string $modified
 * @property Registry $registry
 * @property DataInterface $dataInterface
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class KeyTranslation extends \cascade\components\db\ActiveRecord
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
        return 'key_translation';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key'], 'required'],
            [['created', 'modified'], 'safe'],
            [['data_interface_id', 'registry_id'], 'string', 'max' => 36],
            [['key'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'data_interface_id' => 'Data Interface ID',
            'registry_id' => 'Registry ID',
            'key' => 'Key',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * Get object.
     *
     * @param boolean $checkAccess [[@doctodo param_description:checkAccess]] [optional]
     *
     * @return [[@doctodo return_type:getObject]] [[@doctodo return_description:getObject]]
     */
    public function getObject($checkAccess = true)
    {
        $registryClass = Yii::$app->classes['Registry'];

        $return = $registryClass::getObject($this->registry_id, $checkAccess);

        if (get_class($return) === 'cascade\models\Registry') {
            \d($this->registry_id);
            //throw new \Exception("TRANSLATION WHATTTT AGAIN?!");
            exit;
        }

        return $return;
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

    /**
     * Get data interface.
     *
     * @return \yii\db\ActiveRelation
     */
    public function getDataInterface()
    {
        return $this->hasOne(DataInterface::className(), ['id' => 'data_interface_id']);
    }
}
