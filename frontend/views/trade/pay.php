<?php

use yii\web\View;
use xutl\jquery\qrcode\QRCode;
use yuncms\trade\frontend\assets\TradeAsset;

/* @var \yii\web\View $this */
/* @var \yuncms\trade\models\Trade $trade */
/* @var array $paymentParams */
TradeAsset::register($this);
$js = 'yii.trade.getTradeStatus(' . $trade->id . ');';
if (!Yii::$app->request->isAjax && isset($paymentParams['data'])) {
    $this->registerJs($js, View::POS_BEGIN);
} else {
    $this->registerJs($js);
}
?>
<?= QRCode::widget(['clientOptions' => ['text' => $paymentParams['qr_code']]]); ?>
