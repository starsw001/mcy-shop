<?php
declare(strict_types=1);

use Kernel\Plugin\Const\Plugin;

return [
    Plugin::NAME => '爱上微云付',
    Plugin::AUTHOR => '爱上微',
    Plugin::DESCRIPTION => '使用本插件，可以轻松接入"爱上微云付"协议的所有支付平台。',
    Plugin::VERSION => '1.0.0',
    Plugin::ARCH => Plugin::ARCH_CLI | Plugin::ARCH_FPM,
    Plugin::TYPE => Plugin::TYPE_PAY
];