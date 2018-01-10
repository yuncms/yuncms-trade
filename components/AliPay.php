<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\trade\components;

use Yii;
use yii\web\Request;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\httpclient\Client;
use yii\httpclient\RequestEvent;
use yuncms\trade\BaseClient;
use yuncms\trade\models\Trade;
use yuncms\trade\PaymentException;

/**
 * Class Alipay
 * @package yuncms\trade\components
 */
class AliPay extends BaseClient
{
    const SIGNATURE_METHOD_RSA = 'RSA';
    const SIGNATURE_METHOD_RSA2 = 'RSA2';

    /**
     * @var integer
     */
    public $appId;

    /**
     * @var string 私钥
     */
    public $privateKey;

    /**
     * @var string 公钥
     */
    public $publicKey;

    /**
     * @var string 签名方法
     */
    public $signType = self::SIGNATURE_METHOD_RSA2;

    /**
     * @var string 网关地址
     */
    public $baseUrl = 'https://openapi.alipay.com';

    /**
     * @var string 跳转方法
     */
    public $redirectMethod = 'QRCODE';

    /**
     * @var array 交易类型和Trade映射
     */
    public $tradeTypeMap = [
        Trade::TYPE_NATIVE => 'alipay.trade.page.pay',//WEB 原生扫码支付
        Trade::TYPE_JS_API => 'alipay.trade.create',//应用内JS API,如微信
        Trade::TYPE_APP => 'alipay.trade.app.pay',//app支付
        Trade::TYPE_H5 => 'alipay.trade.wap.pay',//H5支付
        Trade::TYPE_MICROPAY => 'alipay.trade.precreate',//刷卡支付
    ];

    /**
     * 初始化
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (!in_array('sha256', openssl_get_md_methods(), true)) {
            trigger_error('need openssl support sha256', E_USER_ERROR);
        }
        if (empty ($this->appId)) {
            throw new InvalidConfigException ('The "appId" property must be set.');
        }
        if (empty ($this->privateKey)) {
            throw new InvalidConfigException ('The "privateKey" property must be set.');
        }
        if (empty ($this->publicKey)) {
            throw new InvalidConfigException ('The "publicKey" property must be set.');
        }
        $privateKey = "file://" . Yii::getAlias($this->privateKey);
        $this->privateKey = openssl_pkey_get_private($privateKey);
        if ($this->privateKey === false) {
            throw new InvalidConfigException(openssl_error_string());
        }
        $publicKey = "file://" . Yii::getAlias($this->publicKey);
        $this->publicKey = openssl_pkey_get_public($publicKey);
        if ($this->publicKey === false) {
            throw new InvalidConfigException(openssl_error_string());
        }

        $this->responseConfig['format'] = Client::FORMAT_JSON;
        $this->on(Client::EVENT_BEFORE_SEND, [$this, 'RequestEvent']);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return Yii::t('trade', 'Alipay');
    }

    /**
     * 请求事件
     * @param RequestEvent $event
     * @return void
     */
    public function RequestEvent(RequestEvent $event)
    {
        $params = $event->request->getData();
        $params = ArrayHelper::merge([
            'app_id' => $this->appId,
            'format' => 'JSON',
            'charset' => 'utf-8',
            'sign_type' => $this->signType,
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0',
        ], $params);
        $params['biz_content'] = Json::encode($params['biz_content']);
        //签名
        if ($this->signType == self::SIGNATURE_METHOD_RSA2) {
            $params['sign'] = openssl_sign($this->getSignContent($params), $sign, $this->privateKey, OPENSSL_ALGO_SHA256) ? base64_encode($sign) : null;
        } elseif ($this->signType == self::SIGNATURE_METHOD_RSA) {
            $params['sign'] = openssl_sign($this->getSignContent($params), $sign, $this->privateKey, OPENSSL_ALGO_SHA1) ? base64_encode($sign) : null;
        }
        $event->request->setData($params);
    }

    /**
     * 数据签名处理
     * @param array $toBeSigned
     * @param bool $verify
     * @return bool|string
     */
    protected function getSignContent(array $toBeSigned, $verify = false)
    {
        ksort($toBeSigned);
        $stringToBeSigned = '';
        foreach ($toBeSigned as $k => $v) {
            if ($verify && $k != 'sign' && $k != 'sign_type') {
                $stringToBeSigned .= $k . '=' . $v . '&';
            }
            if (!$verify && $v !== '' && !is_null($v) && $k != 'sign' && '@' != substr($v, 0, 1)) {
                $stringToBeSigned .= $k . '=' . $v . '&';
            }
        }
        $stringToBeSigned = substr($stringToBeSigned, 0, -1);
        unset($k, $v);
        return $stringToBeSigned;
    }

    /**
     * 统一下单(生活号，扫码，APP，刷卡等支付均走这个方法)
     * @param Trade $trade
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function unifiedOrder(Trade $trade)
    {
        $tradeType = $this->getTradeType($trade->type);
        $bizContent = [
            'out_trade_no' => $trade->outTradeNo,//商户订单号
            'total_amount' => $trade->total_amount,//订单总金额
            'subject' => $trade->subject,//订单标题
            'discountable_amount' => $trade->discountable_amount,//可打折金额
            'return_url' => $this->getReturnUrl(),
        ];
        if ($tradeType == $this->tradeTypeMap[Trade::TYPE_NATIVE] || $tradeType == $this->tradeTypeMap[Trade::TYPE_H5]) {//H5或电脑支付需要回跳地址
            $bizContent['notify_url'] = $this->getNoticeUrl();
        }
        return $this->sendRequest(['method' => $tradeType, 'biz_content' => $bizContent,]);
    }

    /**
     * 去支付
     * @param Trade $trade
     * @param array $paymentParams
     * @throws \yii\base\Exception
     */
    public function payment(Trade $trade, &$paymentParams)
    {
        $response = $this->unifiedOrder($trade);
        $paymentParams['qr_code'] = $response['qr_code'];
    }

    /**
     * @param Trade $trade
     * @return array|bool
     * @throws PaymentException
     */
    public function create(Trade $trade)
    {
        $data = [
            'method' => 'alipay.trade.create',
            'biz_content' => [
                'out_trade_no' => $trade->outTradeNo,
                'total_amount' => $trade->total_amount,
                'subject' => $trade->subject
            ],
        ];
        return $this->sendRequest($data);
    }


    /**
     * 关闭支付
     * @param Trade $trade
     * @return bool|void
     */
    public function close(Trade $trade)
    {

    }

    public function cancel()
    {

    }

    /**
     * 统一收单退款接口
     * @return mixed|void
     */
    public function refund()
    {
        $params['method'] = 'alipay.trade.fastpay.refund.query';
        $params['biz_content'] = '';
    }

    public function refundQuery()
    {

    }

    public function orderSettle()
    {

    }

    /**
     * 网关请求参数
     * @param array $params
     * @return array|bool
     * @throws PaymentException
     */
    public function sendRequest(array $params)
    {
        $response = $this->post('gateway.do', $params)->send();
        if ($response->isOk) {
            $responseNode = str_replace('.', '_', $params['method']) . '_response';
            if (isset($response->data[$responseNode]) && isset($response->data['sign'])) {
                return $this->verify($response->data[$responseNode], $response->data['sign'], true);
            } else {
                throw new PaymentException('Http request failed.');
            }
        } else {
            throw new PaymentException('Gateway Exception');
        }
    }

    /**
     * 验证支付宝支付宝通知
     * @param array $data 通知数据
     * @param null $sign 数据签名
     * @param bool $sync
     * @return array|bool
     */
    public function verify($data, $sign = null, $sync = false)
    {
        $sign = is_null($sign) ? $data['sign'] : $sign;
        $toVerify = $sync ? json_encode($data) : $this->getSignContent($data, true);
        return openssl_verify($toVerify, base64_decode($sign), $this->publicKey, OPENSSL_ALGO_SHA256) === 1 ? $data : false;
    }


    /**
     * 支付响应
     * @param Request $request
     * @param $paymentId
     * @param $money
     * @param $message
     * @param $payId
     * @return mixed
     */
    public function callback(Request $request, &$paymentId, &$money, &$message, &$payId)
    {
        // TODO: Implement callback() method.
    }

    /**
     * 服务端通知
     * @param Request $request
     * @param $paymentId
     * @param $money
     * @param $message
     * @param $payId
     * @return mixed
     */
    public function notice(Request $request, &$paymentId, &$money, &$message, &$payId)
    {
        // TODO: Implement notice() method.
    }

    /**
     * 支付查询
     * @param Trade $trade
     * @return array|bool
     * @throws PaymentException
     */
    public function query(Trade $trade)
    {
        $data = [
            'method' => 'alipay.trade.query',
            'biz_content' => [
                'out_trade_no' => $trade->outTradeNo,
            ],
        ];
        return $this->sendRequest($data);
    }

    /**
     * 获取交易类型
     * @param int $tradeType
     * @return mixed|string
     */
    protected function getTradeType($tradeType)
    {
        return isset($this->tradeTypeMap[$tradeType]) ? $this->tradeTypeMap[$tradeType] : 'alipay.trade.precreate';
    }

    /**
     * 查询退款
     * 提交退款申请后，通过调用该接口查询退款状态。退款有一定延时，用零钱支付的退款20分钟内到账，银行卡支付的退款3个工作日后重新查询退款状态。
     * @return mixed
     */
    public function getAppParams($response)
    {
        // TODO: Implement getAppParams() method.
    }
}