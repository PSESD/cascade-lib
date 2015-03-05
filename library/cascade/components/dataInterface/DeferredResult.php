<?php
/**
 * @link http://www.infinitecascade.com/
 *
 * @copyright Copyright (c) 2014 Infinite Cascade
 * @license http://www.infinitecascade.com/license/
 */

namespace cascade\components\dataInterface;

use yii\helpers\Url;

/**
 * DeferredResult [[@doctodo class_description:cascade\components\dataInterface\DeferredResult]].
 *
 * @author Jacob Morrison <email@ofjacob.com>
 */
class DeferredResult extends \infinite\deferred\components\Result
{
    //public $logModel;

    /**
     * @inheritdoc
     */
    public function package($details = false)
    {
        $package = parent::package($details);
        $logModel = $this->action->logModel;
        if ($logModel->status !== 'queued') {
            $package['actions'][] = ['label' => 'View Log', 'url' => Url::to(['/admin/interface/view-log', 'id' => $logModel->id])];
        }

        return $package;
    }

    /**
     * @inheritdoc
     */
    public function handleException(\Exception $e)
    {
        $message = [$e->getFile() . ':' . $e->getLine() . ' ' . $e->getMessage()];
        $message[] = $e->getTraceAsString();
        $logModel = $this->action->logModel;
        $logModel->getStatusLog(true)->setCommandOutput(implode(PHP_EOL, $message))->save();

        return $e;
    }
}
