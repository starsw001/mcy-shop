<?php
declare(strict_types=1);

namespace App\Plugin\VirtualCardShip\Handle;

use App\Plugin\VirtualCardShip\Model\RepertoryVirtualCard;
use Kernel\Cache\Cache;
use Kernel\Context\App;
use Kernel\Util\Arr;

class Ship extends \Kernel\Plugin\Abstract\Ship
{


    protected const STOCK_CACHE_KEY = "vc_sc:%d";

    private array $stockKeep = [
        0 => "售罄",
        5 => "紧张",
        20 => "一般",
        50 => "充足",
        100 => "大量"
    ];

    private array $stockRadical = [
        0 => "空了",
        5 => "告急",
        20 => "略紧",
        50 => "充裕",
        100 => "充沛"
    ];

    /**
     * 注意，当前方法需要在事务内运行
     * @return string
     * @throws \ReflectionException
     */
    public function delivery(): string
    {
        $contents = "很抱歉，有人在你付款之前抢走了商品，请联系客服。";

        $direction = match ((int)$this->item->virtual_card_sequence) {
            0 => "rand()",
            1 => "id asc",
            2 => "id desc"
        };

        $cards = RepertoryVirtualCard::query()->where("sku_id", $this->sku->id)->orderByRaw($direction)->where("status", 0);
        $cards = $cards->limit($this->order->quantity)->get();

        if (count($cards) == $this->order->quantity) {
            $ids = [];
            $_temp = '';
            foreach ($cards as $card) {
                $ids[] = $card->id;
                $_temp .= $card->card . PHP_EOL;
            }

            //将全部卡密置已销售状态
            $rows = RepertoryVirtualCard::query()->whereIn("id", $ids)->update(['purchase_time' => $this->order->trade_time, 'order_id' => $this->order->id, 'status' => 1]);
            if ($rows != 0) {
                $contents = trim($_temp, PHP_EOL);
                $this->order->status = 1;
            }
        }


        if (App::$cli) {
            Cache::instance()->del(sprintf(self::STOCK_CACHE_KEY, $this->sku->id));
        }

        return $contents;
    }

    /**
     * @return int|string
     * @throws \ReflectionException
     */
    public function stock(): int|string
    {
        $stock = RepertoryVirtualCard::query()->where("sku_id", $this->sku->id)->where("status", 0)->count();

        $mode = (int)$this->plugin->getConfig("stock_mode");

        switch ($mode) {
            case 1:
                return $this->stockKeep["100"];
            case 2:
                foreach ($this->stockRadical as $k => $v) {
                    if ($stock <= $k) {
                        return $v;
                    }
                }
                return $this->stockRadical["100"];
            case 3:
                $custom = Arr::strToList((string)$this->plugin->getConfig("custom"));
                foreach ($custom as $item) {
                    $a = explode("-", trim($item));
                    if ($stock <= $a[0]) {
                        return $a[1];
                    }
                }
                return explode("-", trim(end($custom)))[1];
            default:
                return $stock;
        }
    }

    /**
     * @param int $quantity
     * @return bool
     */
    public function hasEnoughStock(int $quantity = 1): bool
    {
        $stock = RepertoryVirtualCard::query()->where("sku_id", $this->sku->id)->where("status", 0)->count();
        if ($stock > 0 && $stock >= $quantity) {
            return true;
        }
        return false;
    }
}