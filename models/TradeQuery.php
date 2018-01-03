<?php

namespace yuncms\trade\models;

use yii\db\ActiveQuery;

/**
 * This is the ActiveQuery class for [[Trade]].
 *
 * @see Trade
 */
class TradeQuery extends ActiveQuery
{
    /**
     * @var string 模型类型
     */
    public $model_class;

    /**
     * @var string 数据表名称
     */
    public $tableName;

    /**
     * @param \yii\db\QueryBuilder $builder
     * @return $this|\yii\db\Query
     */
    public function prepare($builder)
    {
        if (!empty($this->model_class)) {
            $this->andWhere([$this->tableName . '.model_class' => $this->model_class]);
        }
        return parent::prepare($builder);
    }

    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /*public function active()
    {
        return $this->andWhere(['status' => Trade::STATUS_PUBLISHED]);
    }*/

    /**
     * @inheritdoc
     * @return Trade[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Trade|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
