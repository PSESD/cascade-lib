<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\web\widgets\base;

use Yii;

/**
 * CellBehavior [@doctodo write class description for CellBehavior].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class CellBehavior extends \yii\base\Behavior
{
    /**
     */
    public $gridCellClass = 'infinite\web\grid\Cell';
    /**
     */
    protected $_gridCell;

    /**
     * Get grid cell settings.
     */
    public function getGridCellSettings()
    {
        return [
            'columns' => 12,
            'maxColumns' => 12,
            'tabletSize' => false,
        ];
    }

    /**
     * Get cell.
     */
    public function getCell()
    {
        if (is_null($this->_gridCell)) {
            $gridCellClass = $this->owner->gridCellClass;
            $objectSettings = $this->owner->gridCellSettings;
            $objectSettings['class'] = $gridCellClass;
            $objectSettings['content'] = $this->owner->cellContent;
            $this->_gridCell = Yii::createObject($objectSettings);
        }

        return $this->_gridCell;
    }

    /**
     * Get cell content.
     */
    public function getCellContent()
    {
        return $this->owner;
    }
}
