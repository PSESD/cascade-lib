<?php
/**
 * @link http://www.infinitecascade.com/
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\models;

use Yii;

/**
 * DataInterface is the model class for table "data_interface".
 *
 * @property string $id
 * @property string $name
 * @property string $system_id
 * @property string $last_sync
 * @property string $created
 * @property string $modified
 *
 * @property Registry $id
 * @property DataInterfaceLog[] $dataInterfaceLogs
 * @property KeyTranslation[] $keyTranslations
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataInterface extends \cascade\components\db\ActiveRecord
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
        return 'data_interface';
    }

    /**
    * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'Registry' => [
                'class' => 'infinite\db\behaviors\Registry'
            ],
        ]);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['system_id'], 'required'],
            [['last_sync', 'created', 'modified'], 'safe'],
            [['id'], 'string', 'max' => 36],
            [['name', 'system_id'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'system_id' => 'System ID',
            'last_sync' => 'Last Sync',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * Get id
     * @return \yii\db\ActiveRelation
     */
    public function getId()
    {
        return $this->hasOne(Registry::className(), ['id' => 'id']);
    }

    /**
     * Get data interface logs
     * @return \yii\db\ActiveRelation
     */
    public function getDataInterfaceLogs()
    {
        return $this->hasMany(DataInterfaceLog::className(), ['data_interface_id' => 'id']);
    }

    public function getLastDataInterfaceLog()
    {
        return DataInterfaceLog::find()->where(['data_interface_id' => $this->primaryKey])->orderBy(['created' => SORT_DESC])->one();
    }

    /**
     * Get key translations
     * @return \yii\db\ActiveRelation
     */
    public function getKeyTranslations()
    {
        return $this->hasMany(DataInterface::className(), ['data_interface_id' => 'id']);
    }

    public function getPackage($urlAction = 'view')
    {
        $p = parent::getPackage($urlAction);
        $p['type'] = 'Interface';
        if ($this->hasIcon()) {
            $p['icon'] = $this->getIcon();
        }
        return $p;
    }

    public function getDataInterfaceItem()
    {
        return Yii::$app->collectors['dataInterfaces']->getByPk($this->primaryKey);
    }

    public function hasIcon()
    {
        return true;
    }

    public function getIcon()
    {
        return [
            'class' => 'fa fa-arrows-h'
        ];
    }


    public function estimateDuration()
    {
        $durations = [];
        $logs = DataInterfaceLog::find()->where(['data_interface_id' => $this->primaryKey, 'status' => 'success'])->all();
        foreach ($logs as $log) {
            if (empty($log->ended) || empty($log->started)) {
                continue;
            }
            $durations[] = strtotime($log->ended) - strtotime($log->started);
        }
        if (empty($durations)) {
            return false;
        }
        $average = array_sum($durations) / count($durations);
        $max = max($durations);

        return $average;

        return ($average + $max) / 2;
    }



    public function getRelatedLogQuery()
    {
        return DataInterfaceLog::find()->where(['data_interface_id' => $this->primaryKey]);
    }

}
