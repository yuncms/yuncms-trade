<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use yii\helpers\ArrayHelper;
use yuncms\trade\models\Trade;

$gateways = [];
foreach (Yii::$app->payment->components as $id => $component) {
    $component = Yii::$app->payment->get($id);

    $gateways[$component->id] = $component->title;
}

?>
<div class="row">
    <div class="col-md-2">
        <?= $this->render('@yuncms/user/frontend/views/_profile_menu') ?>
    </div>
    <div class="col-md-10">
        <h2 class="h3 profile-title"><?= Yii::t('trade', 'Payment') ?></h2>
        <div class="row">
            <div class="col-md-12">
                <?php $form = ActiveForm::begin([
                    'layout' => 'horizontal',
                    'enableClientValidation' => true
                ]); ?>
                <?= $form->field($model, 'subject'); ?>
                <?= $form->field($model, 'currency')->inline(true)->radioList(['CNY' => '人民币', 'USD' => '美元']); ?>
                <?= $form->field($model, 'total_amount'); ?>
                <?= $form->field($model, 'type')->inline(true)->radioList([
                    Trade::TYPE_NATIVE => '原生扫码支付',
                    Trade::TYPE_JS_API => '应用内JS API,如微信',
                    Trade::TYPE_APP => 'app支付',
                    Trade::TYPE_H5 => 'H5支付',
                ]); ?>
                <?= $form->field($model, 'gateway')->inline(true)->radioList($gateways); ?>
                <div class="form-group">
                    <div class="col-sm-offset-2 col-sm-9">
                        <?= Html::submitButton(Yii::t('trade', 'Payment'), ['class' => 'btn btn-success']) ?>
                        <br>
                    </div>
                </div>
                <?php ActiveForm::end(); ?>

            </div>
        </div>
    </div>
</div>

