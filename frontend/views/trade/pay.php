<?php

use xutl\jquery\qrcode\QRCodeAsset;

QRCodeAsset::register($this);

/* @var \yii\web\View $this */


$this->registerJs('$(\'#code\').qrcode("'.$paymentParams['qr_code'].'");')
?>

<div id="code"></div>
