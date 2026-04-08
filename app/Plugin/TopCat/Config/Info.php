<?php
declare(strict_types=1);

use Kernel\Plugin\Const\Plugin;

return [
    Plugin::NAME => 'GoTop',
    Plugin::AUTHOR => 'Nico',
    Plugin::DESCRIPTION => 'Back to top widget plugin.',
    Plugin::VERSION => '1.0.0',
    Plugin::ARCH => Plugin::ARCH_CLI | Plugin::ARCH_FPM,
    Plugin::HOOK_SCOPE => Plugin::HOOK_SCOPE_GLOBAL,
    Plugin::TYPE => Plugin::TYPE_GENERAL,
];
