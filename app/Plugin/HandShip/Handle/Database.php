<?php
declare (strict_types=1);

namespace App\Plugin\HandShip\Handle;

use Hyperf\Database\Schema\Blueprint;
use Kernel\Database\Schema;

class Database extends \Kernel\Plugin\Abstract\Database
{
    /**
     * @return void
     */
    public function install(): void
    {
        //创建字段
        if (!Schema::hasColumn("repertory_item", "hand_delivery_method")) {
            Schema::table("repertory_item", function (Blueprint $blueprint) {
                $blueprint->tinyInteger("hand_delivery_method", false, true)->nullable(true);
            });
        }

        if (!Schema::hasColumn("repertory_item_sku", "hand_delivery_contents")) {
            Schema::table("repertory_item_sku", function (Blueprint $blueprint) {
                $blueprint->text("hand_delivery_contents")->nullable(true);
            });
        }

        //创建表
        if (!$this->hasTable("repertory_order_hand")) {
            $this->create("repertory_order_hand", function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger("order_id")->unsigned()->nullable(false)->index("order_id");
                $table->unsignedTinyInteger("status")->nullable(false)->index("status")->default(0);
                //创建外键
                $table->foreign("order_id")->references("id")->on("repertory_order")->onDelete("cascade");
            });
        }
    }

    public function uninstall(): void
    {
    }

    public function update(): void
    {
        // TODO: Implement update() method.
    }
}