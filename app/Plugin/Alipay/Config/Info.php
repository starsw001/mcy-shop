<?php
declare(strict_types=1);

use Kernel\Plugin\Const\Plugin;

return [
    Plugin::NAME => '支付宝',
    Plugin::AUTHOR => '荔枝',
    Plugin::DESCRIPTION => '支付宝官方支付，本插件支持：当面付、PC支付、H5支付',
    Plugin::VERSION => '1.0.3',
    Plugin::ARCH => Plugin::ARCH_CLI | Plugin::ARCH_FPM,
    Plugin::TYPE => Plugin::TYPE_PAY
];