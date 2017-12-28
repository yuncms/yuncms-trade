<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\trade\components;

use Yii;
use yii\web\Request;
use yii\httpclient\Client;
use yii\httpclient\RequestEvent;
use yii\base\InvalidConfigException;
use yuncms\trade\BaseClient;
use yuncms\trade\models\Trade;

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
     * @var string 绑定支付的APPID
     */
    public $appId;

    /**
     * @var string 商户支付密钥
     */
    public $appKey;

    /**
     * @var string 商户号
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
     * @var array 交易类型
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
        if (empty ($this->appKey)) {
            throw new InvalidConfigException ('The "appKey" property must be set.');
        }
        if (empty ($this->mchId)) {
            throw new InvalidConfigException ('The "mchId" property must be set.');
        }
        if (empty ($this->privateKey)) {
            throw new InvalidConfigException ('The "privateKey" property must be set.');
        }
        if (empty ($this->publicKey)) {
            throw new InvalidConfigException ('The "publicKey" property must be set.');
        }

        $privateKey = Yii::getAlias($this->privateKey);

        $this->privateKey = openssl_pkey_get_private("file://" . $privateKey);
        if ($this->privateKey === false) {
            throw new InvalidConfigException(openssl_error_string());
        }
        $publicKey = Yii::getAlias($this->publicKey);
        $this->publicKey = openssl_pkey_get_public("file://" . $publicKey);
        if ($this->publicKey === false) {
            throw new InvalidConfigException(openssl_error_string());
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
        $bizString = urldecode(http_build_query($bizParameters) . '&key=' . $this->appKey);
        if ($this->signType == self::SIGNATURE_METHOD_MD5) {
            $sign = md5($bizString);
        } elseif ($this->signType == self::SIGNATURE_METHOD_SHA256) {
            $sign = hash_hmac('sha256', $bizString, $this->appKey);
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
     * 统一下单
     * @param array $params
     * @return mixed
     */
    public function preCreate(array $params)
    {
        $data = [
            'body' => $params['subject'],
            'out_trade_no' => $params['id'],
            'total_fee' => round($params['total_amount'] * 100),
            'fee_type' => $params['currency'],
            'trade_type' => 'APP',
            'notify_url' => $this->getNoticeUrl(),
            //'device_info' => 'WEB',
            'spbill_create_ip' => Yii::$app->request->isConsoleRequest ? '127.0.0.1' : Yii::$app->request->userIP,
        ];
        $response = $this->post('pay/unifiedorder', $data)->send();
        if ($response->isOk && $response->data['return_code'] == 'SUCCESS') {
            return $response->data;
        }
        return $response->data;
    }

    /**
     * 去支付
     * @param Trade $trade
     * @param array $paymentParams
     * @throws PaymentException
     */
    public function payment(Trade $trade, &$paymentParams)
    {
        $response = $this->preCreate($trade->toArray());
        print_r($response);exit;
        $paymentParams['qr_code'] = $response['code_url'];
    }

    /**
     * 关闭订单
     * @param string $outTradeNo
     * @return bool
     */
    public function closeOrder($outTradeNo)
    {
        $response = $this->post('pay/closeorder', [
            'out_trade_no' => $outTradeNo,
        ])->send();
        return $response->data;
    }

    /**
     * 查询订单号
     * @param string $outTradeNo
     * @return array
     */
    public function query($outTradeNo)
    {
        $response = $this->post('pay/orderquery', [
            'out_trade_no' => $outTradeNo,
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
     * 服务端通知
     * @param Request $request
     * @param string $paymentId
     * @param float $money
     * @param string $message
     * @param string $payId
     * @return mixed
     */
    public function notice(Request $request, &$paymentId, &$money, &$message, &$payId)
    {
        $xml = $request->getRawBody();
        //如果返回成功则验证签名
        try {
            $params = $this->convertXmlToArray($xml);
            $paymentId = $params['out_trade_no'];
            $money = $params['total_fee'];
            $message = $params['return_code'];
            $payId = $params['transaction_id'];
            if ($params['return_code'] == 'SUCCESS' && $params['sign'] == $this->generateSignature($params)) {
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                return true;
            }
        } catch (\Exception $e) {
        }
        echo '<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[FAIL]]></return_msg></xml>';
        return false;
    }
}