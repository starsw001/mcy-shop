<?php
declare(strict_types=1);

use Kernel\Plugin\Const\Plugin;

return [
    Plugin::NAME => '后台安全入口',
    Plugin::AUTHOR => '荔枝',
    Plugin::DESCRIPTION => '通过本插件，你可以自定义后台入口地址以及设置白名单IP，让后台更加安全。',
    Plugin::VERSION => '1.0.2',
    Plugin::ARCH => Plugin::ARCH_CLI | Plugin::ARCH_FPM,
    Plugin::TYPE => Plugin::TYPE_GENERAL,
    Plugin::HOOK_SCOPE => Plugin::HOOK_SCOPE_GLOBAL
];