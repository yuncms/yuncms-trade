<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use xutl\inspinia\Box;
use xutl\inspinia\Toolbar;
use xutl\inspinia\Alert;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yuncms\trade\models\Trade;

/* @var $this yii\web\View */
/* @var $searchModel yuncms\trade\backend\models\TradeSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('trade', 'Manage Payment');
$this->params['breadcrumbs'][] = $this->title;
$this->registerJs("jQuery(\"#batch_deletion\").on(\"click\", function () {
    yii.confirm('" . Yii::t('app', 'Are you sure you want to delete this item?') . "',function(){
        var ids = jQuery('#gridview').yiiGridView(\"getSelectedRows\");
        jQuery.post(\"/trade/trade/batch-delete\",{ids:ids});
    });
});", View::POS_LOAD);
?>
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12 payment-index">
            <?= Alert::widget() ?>
            <?php Pjax::begin(); ?>
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
                            'options' => ['id' => 'batch_deletion', 'class' => 'btn btn-sm btn-danger'],
                            'label' => Yii::t('trade', 'Batch Deletion'),
                            'url' => 'javascript:void(0);',
                        ]
                    ]]); ?>
                </div>
                <div class="col-sm-8 m-b-xs">
                    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
                </div>
            </div>
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'options' => ['id' => 'gridview'],
                'layout' => "{items}\n{summary}\n{pager}",
                //'filterModel' => $searchModel,
                'columns' => [
                    [
                        'class' => 'yii\grid\CheckboxColumn',
                        "name" => "id",
                    ],
                    //['class' => 'yii\grid\SerialColumn'],
                    'id',
                    'model_id',
                    'pay_id',
                    'user_id',
                    'user.nickname',
                    'subject',
                    'gateway',
                    'currency',
                    'total_amount',
                    [
                        'header' => Yii::t('trade', 'Trade Type'),
                        'value' => function ($model) {
                            if ($model->type == Trade::TYPE_NATIVE) {
                                return Yii::t('trade', 'Native Payment');
                            } else if ($model->type == Trade::TYPE_MWEB) {
                                return Yii::t('trade', 'Mweb Payment');
                            } else if ($model->type == Trade::TYPE_APP) {
                                return Yii::t('trade', 'App Payment');
                            } else if ($model->type == Trade::TYPE_JS_API) {
                                return Yii::t('trade', 'Jsapi Payment');
                            } else if ($model->type == Trade::TYPE_MICROPAY) {
                                return Yii::t('trade', 'Micro Payment');
                            } else if ($model->type == Trade::TYPE_OFFLINE) {
                                return Yii::t('trade', 'Office Payment');
                            }
                        },
                        'format' => 'raw'
                    ],
                    [
                        'header' => Yii::t('trade', 'Trade State'),
                        'value' => function ($model) {
                            if ($model->state == Trade::STATE_NOT_PAY) {
                                return Yii::t('trade', 'State Not Pay');
                            } else if ($model->state == Trade::STATE_SUCCESS) {
                                return Yii::t('trade', 'State Success');
                            } else if ($model->state == Trade::STATE_FAILED) {
                                return Yii::t('trade', 'State Failed');
                            } else if ($model->state == Trade::STATE_REFUND) {
                                return Yii::t('trade', 'State Refund');
                            } else if ($model->state == Trade::STATE_CLOSED) {
                                return Yii::t('trade', 'State Close');
                            } else if ($model->state == Trade::STATE_REVOKED) {
                                return Yii::t('trade', 'State Revoked');
                            } else if ($model->state == Trade::STATE_ERROR) {
                                return Yii::t('trade', 'State Error');
                            }
                        },
                        'format' => 'raw'
                    ],
                    'ip',
                    'note:ntext',
                    'created_at:datetime',
                    'updated_at:datetime',
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => Yii::t('trade', 'Operation'),
                        'template' => '{view} {update} {delete}',
                        //'buttons' => [
                        //    'update' => function ($url, $model, $key) {
                        //        return $model->status === 'editable' ? Html::a('Update', $url) : '';
                        //    },
                        //],
                    ],
                ],
            ]); ?>
            <?php Box::end(); ?>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
