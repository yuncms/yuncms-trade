<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\trade\components;

use Yii;
use yii\base\Exception;
use yii\web\Request;
use yii\httpclient\Client;
use yii\httpclient\RequestEvent;
use yii\base\InvalidConfigException;
use yuncms\trade\BaseClient;
use yuncms\trade\models\Trade;
use yuncms\trade\PaymentException;

/**
 * Class Wechat
 * @package xutl\payment\components
 */
class Wechat extends BaseClient
{
    const SIGNATURE_METHOD_MD5 = 'MD5';
    const SIGNATURE_METHOD_SHA256 = 'HMAC-SHA256';

    /**
     * @var string 网关地址
     */
    public $baseUrl = 'https://api.mch.weixin.qq.com';

    /**
     * @var string 绑定支付的开放平台 APPID
     */
    public $appId;

    /**
     * @var string 商户支付密钥
     * @see https://pay.weixin.qq.com/index.php/core/cert/api_cert
     */
    public $apiKey;

    /**
     * @var string 商户号
     * @see https://pay.weixin.qq.com/index.php/core/account/info
     */
    public $mchId;

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
    public $signType = self::SIGNATURE_METHOD_SHA256;

    /**
     * @var array 交易类型和Trade映射
     */
    public $tradeTypeMap = [
        Trade::TYPE_NATIVE => 'NATIVE',//WEB 原生扫码支付
        Trade::TYPE_JS_API => 'JSAPI',//应用内JS API,如微信
        Trade::TYPE_APP => 'APP',//app支付
        Trade::TYPE_H5 => 'MWEB',//H5支付
        Trade::TYPE_MICROPAY => 'MICROPAY',//刷卡支付
    ];

    /**
     * 初始化
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (empty ($this->appId)) {
            throw new InvalidConfigException ('The "appId" property must be set.');
        }
        if (empty ($this->apiKey)) {
            throw new InvalidConfigException ('The "apiKey" property must be set.');
        }
        if (empty ($this->mchId)) {
            throw new InvalidConfigException ('The "mchId" property must be set.');
        }
        if (!empty ($this->privateKey)) {
            $privateKey = Yii::getAlias($this->privateKey);
            $this->privateKey = openssl_pkey_get_private("file://" . $privateKey);
            if ($this->privateKey === false) {
                throw new InvalidConfigException(openssl_error_string());
            }
        }
        if (!empty ($this->publicKey)) {
            $publicKey = Yii::getAlias($this->publicKey);
            $this->publicKey = openssl_pkey_get_public("file://" . $publicKey);
            if ($this->publicKey === false) {
                throw new InvalidConfigException(openssl_error_string());
            }
        }
        $this->requestConfig['format'] = Client::FORMAT_XML;
        $this->responseConfig['format'] = Client::FORMAT_XML;
        $this->on(Client::EVENT_BEFORE_SEND, [$this, 'RequestEvent']);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return Yii::t('trade', 'Wechat');
    }

    /**
     * 请求事件
     * @param RequestEvent $event
     * @return void
     * @throws \yii\base\Exception
     */
    public function RequestEvent(RequestEvent $event)
    {
        $params = $event->request->getData();
        $params['appid'] = $this->appId;
        $params['mch_id'] = $this->mchId;
        $params['nonce_str'] = $this->generateRandomString(32);
        $params['sign_type'] = $this->signType;
        $params['sign'] = $this->generateSignature($params);
        $event->request->setData($params);
    }

    /**
     * 生成签名
     * @param array $params
     * @return string
     * @throws InvalidConfigException
     */
    protected function generateSignature(array $params)
    {
        $bizParameters = [];
        foreach ($params as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $bizParameters[$k] = $v;
            }
        }
        ksort($bizParameters);
        $bizString = urldecode(http_build_query($bizParameters) . '&key=' . $this->apiKey);
        if ($this->signType == self::SIGNATURE_METHOD_MD5) {
            $sign = md5($bizString);
        } elseif ($this->signType == self::SIGNATURE_METHOD_SHA256) {
            $sign = hash_hmac('sha256', $bizString, $this->apiKey);
        } else {
            throw new InvalidConfigException ('This encryption is not supported');
        }
        return strtoupper($sign);
    }

    /**
     * 转换XML到数组
     * @param \SimpleXMLElement|string $xml
     * @return array
     */
    protected function convertXmlToArray($xml)
    {
        libxml_disable_entity_loader(true);
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    }

    /**
     * 统一下单(公众号，扫码，APP，刷卡等支付均走这个方法)
     * @param Trade $trade
     * @return mixed
     * @throws \yii\base\Exception
     */
    public function unifiedOrder(Trade $trade)
    {
        $data = [
            'body' => $trade->subject,
            'out_trade_no' => $trade->id,
            'total_fee' => round($trade->total_amount * 100),
            'fee_type' => $trade->currency,
            'trade_type' => $this->getTradeType($trade->type),
            'notify_url' => $this->getNoticeUrl(),
            'spbill_create_ip' => Yii::$app->request->isConsoleRequest ? '127.0.0.1' : Yii::$app->request->userIP,
            'device_info' => 'WEB',
            'attach' => $trade->attach,
        ];
        //print_r($data);exit;
        if ($trade->type == Trade::TYPE_JS_API) {
            if (isset($trade->user->socialAccounts['wechat'])) {
                $weParams = $trade->user->socialAccounts['wechat']->getDecodedData();
                $data['openid'] = $weParams['openid'];
            } else {
                throw new PaymentException ('Non-WeChat authorized login.');
            }
        }
        $response = $this->post('pay/unifiedorder', $data)->send();
        if ($response->isOk) {
            if ($response->data['return_code'] == 'SUCCESS') {
                $trade->updateAttributes(['pay_id' => $response->data['prepay_id']]);
                return $response->data;
            } else {
                throw new PaymentException($response->data['return_msg']);
            }
        } else {
            throw new Exception ('Http request failed.');
        }
    }

    /**
     * 生成支付信息
     * @param Trade $trade
     * @param array $paymentParams 支付参数
     * @return void
     * @throws Exception
     */
    public function payment(Trade $trade, &$paymentParams)
    {
        $r = $this->unifiedOrder($trade);
        print_r($r);
        exit;
    }

    /**
     * 获取手机APP支付参数
     * @param Trade $trade
     * @return array 支付参数
     * @throws InvalidConfigException
     * @throws \yii\base\Exception
     */
    public function getAppPaymentParams(Trade $trade)
    {
        $response = $this->unifiedOrder($trade);
        $tradeParams = [
            'appid' => $this->appId,
            'partnerid' => $this->mchId,
            'prepayid' => $response['prepay_id'],
            'package' => 'Sign=WXPay',
            'noncestr' => $this->generateRandomString(32),
            'timestamp' => time(),
        ];
        $tradeParams['sign'] = $this->generateSignature($tradeParams);
        return $tradeParams;
    }

    /**
     * 关闭订单
     * @param Trade $trade
     * @return bool
     */
    public function close(Trade $trade)
    {
        $response = $this->post('pay/closeorder', [
            'out_trade_no' => $trade->outTradeNo,
        ])->send();
        return $response->data;
    }

    /**
     * 查询订单号
     * @param Trade $trade
     * @return array
     */
    public function query(Trade $trade)
    {
        $response = $this->post('pay/orderquery', [
            'out_trade_no' => $trade->outTradeNo,
        ])->send();
        return $response->data;
    }

    /**
     * 退款
     */
    public function refund()
    {

    }

    /**
     * 服务端通知
     * @param Request $request
     * @param string $tradeId
     * @param float $money
     * @param string $message
     * @param string $payId
     * @return mixed
     */
    public function notice(Request $request, &$tradeId, &$money, &$message, &$payId)
    {
        $xml = $request->getRawBody();
        //如果返回成功则验证签名
        try {
            $params = $this->convertXmlToArray($xml);
            $tradeId = $params['out_trade_no'];
            $money = $params['total_fee'];
            $message = $params['return_code'];
            $payId = $params['transaction_id'];
            if ($params['return_code'] == 'SUCCESS' && $params['sign'] == $this->generateSignature($params)) {
                Trade::setPayStatus($tradeId, true, ['pay_id' => $payId, 'message' => $message]);
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                return true;
            }
        } catch (\Exception $e) {
            Yii::error($e->getMessage(), __CLASS__);
        }
        echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        return false;
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
        return;
    }

    /**
     * 获取交易类型
     * @param int $tradeType
     * @return mixed|string
     */
    protected function getTradeType($tradeType)
    {
        return isset($this->tradeTypeMap[$tradeType]) ? $this->tradeTypeMap[$tradeType] : 'NATIVE';
    }

}