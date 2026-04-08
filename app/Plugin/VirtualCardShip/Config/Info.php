<?php
declare(strict_types=1);

use Kernel\Plugin\Const\Plugin;
use Kernel\Language\Language;

return [
    Plugin::NAME => Language::instance()->output("铏氭嫙鍗″瘑"),
    Plugin::AUTHOR => '鑽旀灊',
    Plugin::DESCRIPTION => '铏氭嫙鍗″瘑鑷姩鍙戣揣鎻掍欢锛氬崱瀵嗗鍒犳敼鏌ャ€佸鍏ュ鍑恒€佽秴澶у崱瀵嗘枃浠跺鍏?鏀寔浜胯绾?',
    Plugin::VERSION => '1.0.9',
    Plugin::ARCH => Plugin::ARCH_CLI | Plugin::ARCH_FPM,
    Plugin::HOOK_SCOPE => Plugin::HOOK_SCOPE_GLOBAL,
    Plugin::TYPE => Plugin::TYPE_SHIP
];