<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\trade\frontend\assets;


use yii\web\AssetBundle;
/**
 * Class PaymentAsset
 * @package yuncms\trade\assets
 */
class TradeAsset extends AssetBundle
{
    public $sourcePath = '@yuncms/trade/frontend/views/assets';

    /**
     * @var array
     */
    public $js = [
        'js/trade.js'
    ];

    /**
     * @var array
     */
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}