<?php
declare (strict_types=1);

use Kernel\Util\Route;

return [
    [
        "name" => "虚拟卡密",
        "type" => Route::TYPE_PAGE,
        "icon" => 'icon-yinhangqia',
        "route" => \App\View\Helper::inst()->getPluginBackstageRoute("VirtualCardShip", "card")
    ]
];