<?php
declare(strict_types=1);

namespace App\Plugin\VirtualCardShip\Model;

use App\Model\RepertoryItem;
use App\Model\RepertoryItemSku;
use App\Model\RepertoryOrder;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $item_id
 * @property int $sku_id
 * @property string $remark
 * @property string $card
 * @property string $create_time
 * @property string $purchase_time
 * @property int $order_id
 * @property int $status
 */
class RepertoryVirtualCard extends Model
{
    /**
     * @var string|null
     */
    protected ?string $table = "repertory_virtual_card";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'item_id' => 'integer', 'sku_id' => 'integer', 'order_id' => 'integer', 'status' => 'integer'];


    /**
     * @return HasOne
     */
    public function sku(): HasOne
    {
        return $this->hasOne(RepertoryItemSku::class, "id", "sku_id")->select(["id", "name"]);
    }

    /**
     * @return HasOne
     */
    public function item(): HasOne
    {
        return $this->hasOne(RepertoryItem::class, "id", "item_id")->select(["id", "name"]);
    }

    /**
     * @return HasOne|null
     */
    public function order(): ?HasOne
    {
        return $this->hasOne(RepertoryOrder::class, "id", "order_id")->select(["id", "trade_no"]);
    }
}