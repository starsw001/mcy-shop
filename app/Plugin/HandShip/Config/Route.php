<?php
declare (strict_types=1);

return [
    [
        "name" => "订单查询-后台页面",
        "route" => "/order",
        "class" => \App\Plugin\HandShip\Controller\Order::class,
        "action" => "index",
        "method" => "GET"
    ],
    [
        "name" => "订单查询-API",
        "route" => "/order/get",
        "class" => \App\Plugin\HandShip\Controller\API\Order::class,
        "action" => "get",
        "method" => "POST"
    ],
    [
        "name" => "订单发货-API",
        "route" => "/order/shipment",
        "class" => \App\Plugin\HandShip\Controller\API\Order::class,
        "action" => "shipment",
        "method" => "POST"
    ]
];