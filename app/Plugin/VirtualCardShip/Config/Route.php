<?php
declare (strict_types=1);

return [
    [
        "name" => "虚拟卡密-后台页面",
        "route" => "/card",
        "class" => \App\Plugin\VirtualCardShip\Controller\Card::class,
        "action" => "index",
        "method" => "GET"
    ],
    [
        "name" => "虚拟卡密-API",
        "route" => "/card/get",
        "class" => \App\Plugin\VirtualCardShip\Controller\API\Card::class,
        "action" => "get",
        "method" => "POST"
    ],
    [
        "name" => "文件上传-API",
        "route" => "/card/upload",
        "class" => \App\Plugin\VirtualCardShip\Controller\API\Card::class,
        "action" => "upload",
        "method" => "POST"
    ],
    [
        "name" => "卡密上传-API",
        "route" => "/card/add",
        "class" => \App\Plugin\VirtualCardShip\Controller\API\Card::class,
        "action" => "add",
        "method" => "POST"
    ],
    [
        "name" => "更新卡密状态-API",
        "route" => "/card/status",
        "class" => \App\Plugin\VirtualCardShip\Controller\API\Card::class,
        "action" => "status",
        "method" => "POST"
    ],
    [
        "name" => "导出卡密-API",
        "route" => "/card/export",
        "class" => \App\Plugin\VirtualCardShip\Controller\API\Card::class,
        "action" => "export",
        "method" => "GET"
    ]
];