<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\trade\actions;

use Yii;
use yii\base\Action;
use yuncms\trade\ClientInterface;
use yuncms\trade\jobs\NoticeJob;
use yuncms\trade\models\Trade;

/**
 * Class NoticeAction
 *
 * 服务器端通知操作
 * @package yuncms\trade\actions
 */
class NoticeAction extends Action
{
    /**
     * @var boolean whether to enable CSRF validation for the actions in this controller.
     * CSRF validation is enabled only when both this property and [[Request::enableCsrfValidation]] are true.
     */
    public $enableCsrfValidation = false;

    /**
     * @var string|null 支付号
     */
    public $tradeId = null;

    /**
     * @var string|null 平台支付号
     */
    public $payId = null;

    /**
     * @var double|null 钱数
     */
    public $money = null;

    /**
     * @var null 支付状态
     */
    public $status = null;

    /**
     * @var string|null 消息
     */
    public $message = null;

    /**
     * 初始化
     */
    public function init()
    {
        parent::init();
        $this->controller->enableCsrfValidation = $this->enableCsrfValidation;
    }

    /**
     * @param string $gateway
     * @return object
     * @throws \yii\base\InvalidConfigException
     */
    public function getGateway($gateway)
    {
        return Yii::$app->payment->get($gateway, false);
    }

    /**
     * run action
     * @param $gateway
     * @throws \yii\base\ExitException
     * @throws \yii\base\InvalidConfigException
     */
    public function run($gateway)
    {
        if (($gateway = $this->getGateway($gateway)) != null) {
            $status = $gateway->notice(Yii::$app->request, $this->tradeId, $this->money, $this->message, $this->payId);
            Yii::$app->queue->push(new NoticeJob([
                'tradeId' => $this->tradeId,
                'status' => $status,
                'params' => ['money' => $this->money, 'message' => $this->message, 'pay_id' => $this->payId]
            ]));
        }
        Yii::$app->end();
    }
}