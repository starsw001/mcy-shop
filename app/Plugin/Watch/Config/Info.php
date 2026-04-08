<?php
declare(strict_types=1);

use Kernel\Plugin\Const\Plugin;

return [
    Plugin::NAME => 'PHP文件监视(开发工具)',
    Plugin::AUTHOR => '荔枝',
    Plugin::DESCRIPTION => '该插件可以监视项目文件是否被修改，然后自动重启项目，方便开发者无感调试',
    Plugin::VERSION => '1.0.0',
    Plugin::ARCH => Plugin::ARCH_CLI,
    Plugin::HOOK_SCOPE => Plugin::HOOK_SCOPE_GLOBAL,
    Plugin::TYPE => Plugin::TYPE_GENERAL
];