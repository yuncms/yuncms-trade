<?php

use yii\web\View;
use xutl\jquery\qrcode\QRCode;
use yuncms\trade\frontend\assets\TradeAsset;

/* @var \yii\web\View $this */
/* @var \yuncms\trade\models\Trade $trade */
/* @var array $paymentParams */
TradeAsset::register($this);
$js = 'setInterval("yii.trade.getPaymentStatus(' . $trade->id . ')", 3000);';
if (!Yii::$app->request->isAjax && isset($paymentParams['data'])) {
    $this->registerJs($js, View::POS_BEGIN);
} else {
    echo '<script type="text/javascript">' . $js . '</script>';
}
?>
<?= QRCode::widget(['clientOptions' => ['text' => $paymentParams['qr_code']]]); ?>
