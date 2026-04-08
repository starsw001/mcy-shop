<?php
declare (strict_types=1);

use Kernel\Util\Route;

return [
    [
        "name" => "图片验证码-RENDER",
        "route" => "/code",
        "type" => Route::TYPE_ROUTE,
        "rank" => 0,
        "class" => \App\Plugin\ImageCode\Controller\Code::class,
        "action" => "create",
        "method" => "GET"
    ]
]; 