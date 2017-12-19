<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use xutl\inspinia\Box;
use xutl\inspinia\Toolbar;
use xutl\inspinia\Alert;
use yuncms\payment\models\Payment;

/* @var $this yii\web\View */
/* @var $model yuncms\payment\models\Payment */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('trade', 'Manage Payment'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12 payment-view">
            <?= Alert::widget() ?>
            <?php Box::begin([
                'header' => Html::encode($this->title),
            ]); ?>
            <div class="row">
                <div class="col-sm-4 m-b-xs">
                    <?= Toolbar::widget(['items' => [
                        [
                            'label' => Yii::t('trade', 'Manage Payment'),
                            'url' => ['index'],
                        ],
                        [
                            'label' => Yii::t('trade', 'Update Payment'),
                            'url' => ['update', 'id' => $model->id],
                            'options' => ['class' => 'btn btn-primary btn-sm']
                        ],
                        [
                            'label' => Yii::t('trade', 'Delete Payment'),
                            'url' => ['delete', 'id' => $model->id],
                            'options' => [
                                'class' => 'btn btn-danger btn-sm',
                                'data' => [
                                    'confirm' => Yii::t('trade', 'Are you sure you want to delete this item?'),
                                    'method' => 'post',
                                ],
                            ]
                        ],
                    ]]); ?>
                </div>
                <div class="col-sm-8 m-b-xs">

                </div>
            </div>
            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    'id',
                    'model_id',
                    'pay_id',
                    'user_id',
                    'name',
                    'gateway',
                    'currency',
                    'money',
                    [
                        'label' => Yii::t('trade', 'Pay Type'),
                        'value' => function ($model) {
                            if ($model->trade_type == Payment::TYPE_NATIVE) {
                                return Yii::t('trade', 'Native Payment');
                            } else if ($model->trade_type == Payment::TYPE_MWEB) {
                                return Yii::t('trade', 'Mweb Payment');
                            } else if ($model->trade_type == Payment::TYPE_APP) {
                                return Yii::t('trade', 'App Payment');
                            } else if ($model->trade_type == Payment::TYPE_JS_API) {
                                return Yii::t('trade', 'Jsapi Payment');
                            } else if ($model->trade_type == Payment::TYPE_MICROPAY) {
                                return Yii::t('trade', 'Micro Payment');
                            } else if ($model->trade_type == Payment::TYPE_OFFLINE) {
                                return Yii::t('trade', 'Office Payment');
                            }
                        },
                        'format' => 'raw'
                    ],
                    [
                        'label' => Yii::t('trade', 'Pay State'),
                        'value' => function ($model) {
                            if ($model->trade_state == Payment::STATE_NOT_PAY) {
                                return Yii::t('trade', 'State Not Pay');
                            } else if ($model->trade_state == Payment::STATE_SUCCESS) {
                                return Yii::t('trade', 'State Success');
                            } else if ($model->trade_state == Payment::STATE_FAILED) {
                                return Yii::t('trade', 'State Failed');
                            } else if ($model->trade_state == Payment::STATE_REFUND) {
                                return Yii::t('trade', 'State Refund');
                            } else if ($model->trade_state == Payment::STATE_CLOSED) {
                                return Yii::t('trade', 'State Close');
                            } else if ($model->trade_state == Payment::STATE_REVOKED) {
                                return Yii::t('trade', 'State Revoked');
                            } else if ($model->trade_state == Payment::STATE_ERROR) {
                                return Yii::t('trade', 'State Error');
                            }
                        },
                       'format' => 'raw'
                    ],
                    'ip',
                    'note:ntext',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
            <?php Box::end(); ?>
        </div>
    </div>
</div>