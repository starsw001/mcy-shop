<?php
declare (strict_types=1);

namespace App\Plugin\VirtualCardShip\Handle;

use Hyperf\Database\Schema\Blueprint;
use Kernel\Database\Schema;

class Database extends \Kernel\Plugin\Abstract\Database
{
    /**
     * @return void
     */
    public function install(): void
    {
        //创建字段，主站，分站插件都会用这一个字段
        if (!Schema::hasColumn("repertory_item", "virtual_card_sequence")) {
            Schema::table("repertory_item", function (Blueprint $blueprint) {
                $blueprint->tinyInteger("virtual_card_sequence", false, true)->nullable(true);
            });
        }

        //创建USR环境表，主站分站自动隔离数据库
        if (!$this->hasTable("repertory_virtual_card")) {
            $this->create("repertory_virtual_card", function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger("item_id")->nullable(false)->index("item_id");
                $table->unsignedBigInteger("sku_id")->nullable(false)->index("sku_id");
                $table->string("remark", 32)->nullable(true)->index("remark");
                $table->text("card")->nullable(false);
                $table->dateTime("create_time")->nullable(false);
                $table->dateTime("purchase_time")->nullable(true);;
                $table->unsignedBigInteger("order_id")->nullable(true)->index("order_id");
                $table->unsignedTinyInteger("status")->index("status");
                //创建外键
                $table->foreign("sku_id")->references("id")->on("repertory_item_sku")->onDelete("cascade");
            });
        }
    }

    public function uninstall(): void
    {
    }
}