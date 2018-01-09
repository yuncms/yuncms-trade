<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */
namespace yuncms\trade\frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yuncms\trade\models\Trade;

/**
 * Class ResponseController
 *
 * @property \yuncms\trade\Module $module
 * @package yuncms\trade
 */
class ResponseController extends Controller
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
     * 支付后跳转
     * @param string $gateway
     * @return \yii\web\Response
     */
    public function actionReturn($gateway)
    {
        $status = Yii::$app->payment->get($gateway)->callback(Yii::$app->request, $this->tradeId, $this->money, $this->message, $this->payId);
        Trade::setPayStatus($this->tradeId, $status, ['money' => $this->money, 'message' => $this->message, 'pay_id' => $this->payId]);
        return $this->redirect(['/payment/default/return', 'id' => $this->tradeId]);
    }

    /**
     * 服务器端通知
     * @param string $gateway
     * @throws \yii\base\ExitException
     */
    public function actionNotice($gateway)
    {
        $status = Yii::$app->payment->get($gateway)->notice(Yii::$app->request, $this->tradeId, $this->money, $this->message, $this->payId);
        //此处应该推送到队列处理
        Trade::setPayStatus($this->tradeId, $status, ['money' => $this->money, 'message' => $this->message, 'pay_id' => $this->payId]);
        Yii::$app->end();
    }
}