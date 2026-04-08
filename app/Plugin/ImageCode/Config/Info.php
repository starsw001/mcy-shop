<?php
declare(strict_types=1);

use Kernel\Plugin\Const\Plugin;

return [
    Plugin::NAME => '图片验证码',
    Plugin::AUTHOR => '荔枝',
    Plugin::DESCRIPTION => '支持场景：登录、注册、重置密码、下单等',
    Plugin::VERSION => '1.0.1',
    Plugin::ARCH => Plugin::ARCH_CLI | Plugin::ARCH_FPM,
    Plugin::HOOK_SCOPE => Plugin::HOOK_SCOPE_GLOBAL,
    Plugin::TYPE => Plugin::TYPE_GENERAL
];