<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\trade\jobs;

use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;
use yuncms\trade\models\Trade;

/**
 * Class NoticeJob
 * @package yuncms\trade\jobs
 */
class NoticeJob extends BaseObject implements RetryableJobInterface
{
    public $tradeId;
    public $status;
    public $params = [];

    /**
     * @param \yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        Trade::setPayStatus($this->tradeId, $this->status, $this->params);
    }

    /**
     * @inheritdoc
     */
    public function getTtr()
    {
        return 60;
    }

    /**
     * @inheritdoc
     */
    public function canRetry($attempt, $error)
    {
        return $attempt < 3;
    }
}