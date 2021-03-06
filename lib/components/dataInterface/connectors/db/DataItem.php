<?php
/**
 * @link http://psesd.org/
 *
 * @copyright Copyright (c) 2015 Puget Sound ESD
 * @license http://psesd.org/license/
 */

namespace cascade\components\dataInterface\connectors\db;

use cascade\components\dataInterface\MissingItemException;
use cascade\components\dataInterface\RecursionException;

/**
 * DataItem data item for db data interface connector.
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DataItem extends \cascade\components\dataInterface\connectors\generic\DataItem
{
    /**
     * Fill the unfilled child|parent_object_id field in the relation config.
     *
     * @param array      $config      relationship configuration
     * @param Model $otherObject the other object to fill in the relation config data
     */
    protected function fillRelationConfig(&$config, $otherObject)
    {
        if (isset($config['parent_object_id'])) {
            $config['child_object_id'] = $otherObject;
        } elseif (isset($config['child_object_id'])) {
            $config['parent_object_id'] = $otherObject;
        }
    }

    /**
     * @inheritdoc
     */
    protected function handleLocal($baseAttributes = [])
    {
        if ($this->ignoreLocalObject) {
            return false;
        }
        // local to foreign

        // find
        return false;
    }

    /**
     * Retrieve the foreign object.
     *
     * @throws RecursionException   when an object load is attempted when it is already being loaded above the call
     * @throws MissingItemException when the item can't be found in the foreign data source
     */
    protected function loadForeignObject()
    {
        if ($this->_isLoadingForeignObject) {
            throw new RecursionException('Ran into recursion while loading foreign object');
        }
        $this->_isLoadingForeignObject = true;
        if (isset($this->foreignPrimaryKey)) {
            $foreignObject = $this->dataSource->getForeignDataModel($this->foreignPrimaryKey);
            if ($foreignObject) {
                $this->foreignObject = $foreignObject;
            }
        }
        if (empty($this->_foreignObject)) {
            \d($this->foreignPrimaryKey);
            \d($this->dataSource->name);
            throw new MissingItemException('Foreign item could not be found: ' . $this->foreignPrimaryKey);
        }
        $this->_isLoadingForeignObject = false;
    }

    /**
     * Retrieve the local object.
     *
     * @throws RecursionException when an object load is attempted when it is already being loaded above the call
     */
    protected function loadLocalObject()
    {
        if ($this->_isLoadingLocalObject) {
            throw new RecursionException('Ran into recursion while loading local object');
        }
        $this->_isLoadingLocalObject = true;
        if (isset($this->foreignObject) && !isset($this->_localObject)) {
            $keyTranslation = $this->dataSource->getKeyTranslation($this->foreignObject);
            if (!empty($keyTranslation) && ($localObject = $keyTranslation->object)) {
                $this->localObject = $localObject;
            }
        }
        $this->_isLoadingLocalObject = false;
    }
}
