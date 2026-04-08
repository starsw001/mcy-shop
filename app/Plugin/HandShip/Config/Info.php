<?php
declare(strict_types=1);

use Kernel\Plugin\Const\Plugin;
use Kernel\Language\Language;

return [
    Plugin::NAME => Language::inst()->output("人工发货"),
    Plugin::AUTHOR => '荔枝',
    Plugin::DESCRIPTION => '手动选择订单发货、固定发货信息，适合一些必须人工完成的商品',
    Plugin::VERSION => '1.0.3',
    Plugin::TYPE => Plugin::TYPE_SHIP,
    Plugin::ARCH => Plugin::ARCH_CLI | Plugin::ARCH_FPM
];