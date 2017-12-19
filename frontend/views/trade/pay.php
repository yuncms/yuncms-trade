<?php
use xutl\jquery\qrcode\QRCode;

/* @var \yii\web\View $this */
/* @var array $paymentParams */
?>
<?= QRCode::widget(['clientOptions' => ['text'=>$paymentParams['qr_code']]]);?>
