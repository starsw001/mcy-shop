<?php
declare (strict_types=1);

use Kernel\Util\Route;

return [
    [
        "name" => "人工发货",
        "type" => Route::TYPE_PAGE,
        "icon" => 'icon-rengongzhineng',
        "route" => \App\View\Helper::inst()->getPluginBackstageRoute("HandShip", "order")
    ]
];