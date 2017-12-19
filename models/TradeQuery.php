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
