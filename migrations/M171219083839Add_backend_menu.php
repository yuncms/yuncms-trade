<?php

namespace yuncms\trade\migrations;

use yii\db\Migration;
use yii\db\Query;

/**
 * Class M171219083839Add_backend_menu
 * @package yuncms\trade\migrations
 */
class M171219083839Add_backend_menu extends Migration
{

    public function safeUp()
    {
        $this->insert('{{%admin_menu}}', [
            'name' => '支付管理',
            'parent' => 7,
            'route' => '/trade/trade/index',
            'icon' => 'fa-rmb',
            'sort' => NULL,
            'data' => NULL
        ]);

        $id = (new Query())->select(['id'])->from('{{%admin_menu}}')->where(['name' => '支付管理', 'parent' => 7])->scalar($this->getDb());
        $this->batchInsert('{{%admin_menu}}', ['name', 'parent', 'route', 'visible', 'sort'], [

            ['支付查看', $id, '/trade/trade/view', 0, NULL],
            ['更新支付', $id, '/trade/trade/update', 0, NULL],
        ]);
    }

    public function safeDown()
    {
        $id = (new Query())->select(['id'])->from('{{%admin_menu}}')->where(['name' => '支付管理', 'parent' => 7])->scalar($this->getDb());
        $this->delete('{{%admin_menu}}', ['parent' => $id]);
        $this->delete('{{%admin_menu}}', ['id' => $id]);
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171219083839Add_backend_menu cannot be reverted.\n";

        return false;
    }
    */
}
