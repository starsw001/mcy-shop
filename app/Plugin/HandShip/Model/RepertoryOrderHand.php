<?php
declare(strict_types=1);

namespace App\Plugin\HandShip\Model;

use App\Model\RepertoryOrder;
use Hyperf\Database\Model\Relations\HasOne;
use Kernel\Component\Singleton;
use Kernel\Database\Model;

/**
 * @property int $id
 * @property int $order_id
 * @property int $status
 */
class RepertoryOrderHand extends Model
{

    use Singleton;

    /**
     * @var string|null
     */
    protected ?string $table = "repertory_order_hand";

    /**
     * @var bool
     */
    public bool $timestamps = false;

    /**
     * @var array
     */
    protected array $casts = ['id' => 'integer', 'order_id' => 'integer', 'status' => 'integer'];


    /**
     * @return HasOne
     */
    public function order(): HasOne
    {
        return $this->hasOne(RepertoryOrder::class, "id", "order_id");
    }
}