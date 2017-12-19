<?php

namespace yuncms\trade\backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yuncms\trade\models\Trade;

/**
 * PaymentSearch represents the model behind the search form about `yuncms\payment\models\Payment`.
 */
class TradeSearch extends Trade
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'model_id', 'pay_id', 'gateway', 'currency', 'ip', 'note'], 'safe'],
            [['user_id', 'subject', 'type', 'state'], 'integer'],
            [['total_amount'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Trade::find();

        $query->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'user_id' => $this->user_id,
            'subject' => $this->subject,
            'total_amount' => $this->total_amount,
            'type' => $this->type,
            'state' => $this->state,
        ]);

        $query->andFilterWhere(['like', 'id', $this->id])
            ->andFilterWhere(['like', 'model_id', $this->model_id])
            ->andFilterWhere(['like', 'pay_id', $this->pay_id])
            ->andFilterWhere(['like', 'gateway', $this->gateway])
            ->andFilterWhere(['like', 'currency', $this->currency])
            ->andFilterWhere(['like', 'ip', $this->ip]);

        return $dataProvider;
    }
}
