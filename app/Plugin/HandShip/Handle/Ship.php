<?php
declare (strict_types=1);

namespace App\Plugin\HandShip\Handle;

use App\Plugin\HandShip\Model\RepertoryOrderHand;

class Ship extends \Kernel\Plugin\Abstract\Ship
{

    /**
     * @var bool
     */
    protected bool $isCustomRender = true;

    /**
     * @return int|string
     */
    public function stock(): int|string
    {
        return "在线发货";
    }

    /**
     * @param int $quantity
     * @return bool
     */
    public function hasEnoughStock(int $quantity = 1): bool
    {
        return true;
    }

    /**
     * @return string
     */
    public function delivery(): string
    {
        if ($this->item->hand_delivery_method == 0) {
            $this->order->status = 1;
            $this->order->save();
        }

        //提醒发货
        $repertoryOrderHand = new RepertoryOrderHand();
        $repertoryOrderHand->order_id = $this->order->id;
        $repertoryOrderHand->status = 0;
        $repertoryOrderHand->save();

        return (string)$this->sku->hand_delivery_contents;
    }


    /**
     * @return string
     */
    public function render(): string
    {
        if ($this->item->hand_delivery_method == 0) {
            return (string)$this->sku->hand_delivery_contents;
        }

        if ($this->order->status == 0) {
            return (string)$this->sku->hand_delivery_contents;
        }

        return (string)$this->order->contents;
    }
}