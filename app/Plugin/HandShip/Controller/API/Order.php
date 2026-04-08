<?php
declare (strict_types=1);

namespace App\Plugin\HandShip\Controller\API;

use App\Entity\Query\Get;
use App\Interceptor\PostDecrypt;
use App\Model\OrderItem;
use App\Model\RepertoryOrder;
use App\Plugin\HandShip\Model\RepertoryOrderHand as Model;
use App\Service\Common\Query;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Relations\Relation;
use Kernel\Annotation\Inject;
use Kernel\Annotation\Interceptor;
use Kernel\Annotation\Validator;
use Kernel\Context\Interface\Response;
use Kernel\Exception\JSONException;
use Kernel\Exception\RuntimeException;
use Kernel\Plugin\Abstract\Controller;
use Kernel\Util\Date;
use Kernel\Waf\Filter;

#[Interceptor(class: [PostDecrypt::class, \App\Interceptor\Plugin::class], type: Interceptor::API)]
class Order extends Controller
{


    #[Inject]
    private Query $query;

    /**
     * @return Response
     * @throws RuntimeException
     * @throws \ReflectionException
     */
    public function get(): Response
    {
        $tableName = Model::inst()->getTable();

        $map = $this->request->post();
        $get = new Get(Model::class);
        $get->setWhere($map);
        $get->setPaginate((int)$this->request->post("page"), (int)$this->request->post("limit"));
        $get->setOrderBy("{$tableName}.id", "desc");

        $raw = [];


        $data = $this->query->get($get, function (Builder $builder) use ($tableName, $map, &$raw) {

            $raw['order_count'] = (clone $builder)->count();
            $raw['order_unshipped_count'] = (clone $builder)->where("status", 0)->count();
            $raw['order_shipped_count'] = (clone $builder)->where("status", 1)->count();

            if (isset($map['trade_no']) && $map['trade_no']) {
                $builder = $builder->leftJoin("repertory_order", "repertory_order.id", "=", "{$tableName}.order_id")->where(function (Builder $builder) use ($map) {
                    $builder
                        ->where("repertory_order.trade_no", $map['trade_no'])
                        ->orWhere("repertory_order.item_trade_no", $map['trade_no'])
                        ->orWhere("repertory_order.main_trade_no", $map['trade_no']);
                });
            }

            return $builder->with(["order" => function (Relation $relation) {
                $relation->with(["item", "sku"]);
            }]);
        });

        return $this->json(data: $data, ext: $raw);
    }


    /**
     * @throws JSONException
     * @throws RuntimeException
     */
    #[Validator([
        ['key' => 'id', 'rule' => 'require', 'message' => ['require' => 'ID是必须的']],
        ['key' => 'contents', 'rule' => 'require', 'message' => ['require' => '发货内容是必须的']],
    ])]
    public function shipment(): Response
    {
        $id = $this->request->post("id");
        $contents = $this->request->post(key: "contents", flags: Filter::NORMAL);

        /**
         * @var Model $hand
         */
        $hand = Model::query()->find($id);

        if (!$hand) {
            throw new JSONException("要发货的订单不存在");
        }

        /**
         * @var RepertoryOrder $order
         */
        $order = RepertoryOrder::query()->find($hand->order_id);

        if (!$order) {
            throw new JSONException("主订单不存在");
        }

        $order->contents = $contents;
        $order->status = 1;
        $order->save();

        $hand->status = 1;
        $hand->save();

        /**
         * @var OrderItem $orderItem
         */
        $orderItem = OrderItem::query()->where("trade_no", $order->item_trade_no)->first();

        if ($orderItem) {
            if ($orderItem->status == 0) {
                $orderItem->status = 1;
            }
            $orderItem->treasure = $contents;
            $orderItem->update_time = Date::current();
            $orderItem->save();
        }

        return $this->json();
    }
}