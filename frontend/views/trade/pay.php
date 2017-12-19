<?php
/* @var \yii\web\View $this */
//二维码付款
$this->registerJsFile('https://www.helloweba.com/demo/qrcode/jquery.qrcode.min.js',[
    'depends'=>['yii\web\JqueryAsset']
]);




$this->registerJs('$(\'#code\').qrcode("'.$paymentParams['qr_code'].'");')
?>

<div id="code"></div>
