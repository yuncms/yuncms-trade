<?php

/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\trade;

/**
 * 交易状态设置接口
 * Interface OrderInterface
 * @package yuncms\trade
 */
interface OrderInterface
{
    /**
     * 设置支付状态
     * @param string $orderId 订单号
     * @param string $tradeId 支付号
     * @param integer $status 状态
     * @param array $params 附加参数
     * @return bool
     */
    public static function setPayStatus($orderId, $tradeId, $status, $params);
}