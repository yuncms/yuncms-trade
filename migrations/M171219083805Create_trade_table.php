<?php

namespace yuncms\trade\migrations;

use yii\db\Migration;

class M171219083805Create_trade_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%trade}}', [
            'id' => $this->string(50)->notNull()->comment('ID'),
            'user_id' => $this->integer()->unsigned()->comment('User ID'),
            'gateway' => $this->string(50)->comment('Gateway'),//交易网关
            'type' => $this->smallInteger()->notNull()->comment('Trade Type'),//交易类别
            'pay_id' => $this->string()->comment('Trade No'),//交易平台的交易号
            'currency' => $this->string(20)->notNull()->comment('Currency'),//币种
            'subject' => $this->string()->notNull()->comment('Subject'),//订单标题
            'total_amount' => $this->decimal(10, 2)->notNull()->defaultValue(0.00)->comment('Total Amount'),//总钱数
            'discountable_amount' => $this->decimal(10, 2)->defaultValue(0.00)->comment('Discountable Amount'),//可打折钱数
            'body' => $this->string(128)->comment('Body'),//对交易或商品的描述
            'model_id' => $this->integer()->comment('Model ID'),//发起模型的ID
            'model_class' => $this->string()->comment('Model Class'),//发起模型的类名
            'state' => $this->smallInteger(2)->notNull()->comment('Trade State'),//交易状态
            'ip' => $this->string()->notNull()->comment('IP'),//用户IP
            'attach' => $this->string()->comment('Attach'),//附加数据
            'data' => $this->text()->comment('data'),//SDK数据
            'return_url' => $this->text()->comment('Return Url'),//回跳URL
            'created_at' => $this->integer()->notNull()->comment('Created At'),//创建时间
            'updated_at' => $this->integer()->notNull()->comment('Updated At'),//更新时间
        ], $tableOptions);

        $this->addPrimaryKey('PK', '{{%trade}}', 'id');
        $this->addForeignKey('payment_fk_1', '{{%trade}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
        $this->createIndex('payment_id_model', '{{%trade}}', ['model_id', 'model_class']);
    }

    public function safeDown()
    {
        $this->dropTable('{{%test}}');
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171219083805Create_trade_table cannot be reverted.\n";

        return false;
    }
    */
}
