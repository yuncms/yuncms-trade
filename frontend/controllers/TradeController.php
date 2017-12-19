<?php
/**
 * @link http://www.tintsoft.com/
 * @copyright Copyright (c) 2012 TintSoft Technology Co. Ltd.
 * @license http://www.tintsoft.com/license/
 */

namespace yuncms\trade\frontend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yuncms\trade\models\Trade;

/**
 * Class TradeController
 * @package yuncms\trade\frontend\controllers
 */
class TradeController extends Controller
{
    /**
     * @var string 默认操作
     */
    public $defaultAction = 'create';

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    //已认证用户
                    [
                        'allow' => true,
                        'actions' => ['create', 'pay', 'query'],
                        'roles' => ['@']
                    ],
                    [
                        'allow' => true,
                        'actions' => ['return'],
                        'roles' => ['@', '?']
                    ],
                ]
            ],
        ];
    }

    /**
     * 支付默认表单
     * @return string
     */
    public function actionCreate()
    {
        $model = new Trade();
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['/trade/trade/pay', 'id' => $model->id]);
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * WEB付款
     * @param int $id
     * @return string
     * @throws \yii\base\InvalidConfigException
     */
    public function actionPay($id)
    {
        try {
            $trade = $this->findModel($id);
            $paymentParams = [];
            Yii::$app->payment->get($trade->gateway)->preCreate($trade, $paymentParams);
            if (Yii::$app->request->isAjax) {
                return $this->renderPartial('pay', ['trade' => $trade, 'paymentParams' => $paymentParams]);
            } else {
                return $this->render('pay', ['trade' => $trade, 'paymentParams' => $paymentParams]);
            }
        } catch (NotFoundHttpException $e) {
            Yii::$app->getSession()->setFlash('error', $e->getMessage());
            return $this->redirect(['/trade/trade/create']);
        }
    }

    /**
     * 交易查询
     * @param string $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionQuery($id)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        $payment = $this->findModel($id);
        if ($payment->state == Trade::STATE_SUCCESS) {
            return ['state' => 'success'];
        } else {
            return ['state' => 'pending'];
        }
    }

    /**
     * 获取支付单号
     * @param int $id
     * @return Trade
     * @throws NotFoundHttpException
     */
    public function findModel($id)
    {
        if (($model = Trade::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('trade', 'The requested trade does not exist.'));
        }
    }
}