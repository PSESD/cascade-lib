<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

use cascade\models\DataInterface;
use infinite\base\exceptions\Exception;

/**
 * Item [@doctodo write class description for Item].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class Item extends \infinite\base\collector\Item
{
    /**
     */
    public $error;

    /**
     */
    private $_name;
    /**
     */
    private $_module;
    /**
     */
    private $_checked;
    /**
     */
    protected $_interfaceObject;
    /**
     */
    protected $_currentInterfaceAction;

    /**
     *
     */
    public function run($resumeLog = null, $action = null)
    {
        register_shutdown_function([$this, 'saveLog']);
        if ($action === null) {
            $action = new NonInteractiveAction();
        }

        $action->interface = $this;
        if (!empty($resumeLog)) {
            $action->resumeLog($resumeLog);
        }

        $this->_currentInterfaceAction = $action;
        if (!$this->_currentInterfaceAction->start()) {
            $this->error = 'Could not start interface action!';

            return false;
        }
        try {
            $this->object->run($this->_currentInterfaceAction);
        } catch (Exception $e) {
            $this->_currentInterfaceAction->status->addError('Exception raised: ' . $e->getMessage());
            $this->_currentInterfaceAction->end(true);
            $this->error = 'Exception raised while running action (' . $e->getMessage() . ').';

            return false;
        }
        $this->_currentInterfaceAction->end();

        return !$this->_currentInterfaceAction->status->hasError;
    }

    /**
     *
     */
    public function saveLog()
    {
        if (isset($this->_currentInterfaceAction)) {
            $this->_currentInterfaceAction->end(true);
        }

        return true;
    }

    /**
     * Get interface object.
     */
    public function getInterfaceObject()
    {
        if (is_null($this->_interfaceObject)) {
            $this->_interfaceObject = DataInterface::find()->where(['system_id' => $this->object->systemId])->one();
            if (empty($this->_interfaceObject)) {
                $this->_interfaceObject = new DataInterface();
                $this->_interfaceObject->name = $this->object->name;
                $this->_interfaceObject->system_id = $this->object->systemId;
                if (!$this->_interfaceObject->save()) {
                    var_dump($this->_interfaceObject->errors);
                    throw new Exception("Unable to save interface object!");
                }
            }
        }

        return $this->_interfaceObject;
    }
}
