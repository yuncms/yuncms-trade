<?php

use yii\helpers\Html;
use xutl\inspinia\ActiveForm;

/* @var \yii\web\View $this */
/* @var yuncms\trade\models\Trade $model */
/* @var ActiveForm $form */
?>
<?php $form = ActiveForm::begin([
    'layout' => 'horizontal',
    'enableAjaxValidation' => true,
    'enableClientValidation' => false,
]); ?>

<?= $form->field($model, 'id')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>
<?= $form->field($model, 'gateway')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>
<?= $form->field($model, 'currency')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>
<?= $form->field($model, 'total_amount')->textInput(['maxlength' => true]) ?>
<div class="hr-line-dashed"></div>
<?= $form->field($model, 'type')->textInput() ?>
<div class="hr-line-dashed"></div>
<?= $form->field($model, 'state')->textInput() ?>
<div class="hr-line-dashed"></div>

<div class="form-group">
    <div class="col-sm-4 col-sm-offset-2">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('trade', 'Create') : Yii::t('trade', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>

    </div>
</div>

<?php ActiveForm::end(); ?>

