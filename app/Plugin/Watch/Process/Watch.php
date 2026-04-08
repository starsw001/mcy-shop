<?php
declare (strict_types=1);

namespace App\Plugin\Watch\Process;

use Kernel\Annotation\Thread;
use Kernel\Log\Log;
use Kernel\Plugin\Abstract\Process;
use Symfony\Component\Finder\Finder;

#[Thread(name: "watch")]
class Watch extends Process
{

    /**
     * @var array
     */
    protected array $watchTime = [];

    /**
     * @return void
     */
    public function handle(): void
    {
        $finder = Finder::create()
            ->in([BASE_PATH . "app", BASE_PATH . "kernel", BASE_PATH . "config"])
            ->depth("< 10")
            ->ignoreUnreadableDirs(true)
            ->name(['*.php', '*.html'])
            ->files();

        foreach ($finder as $item) {
            $this->watchTime[$item->getPathname()] = $item->getCTime();
        }

        while (true) {
            sleep(1);
            $this->monitor();
        }
    }

    /**
     * @return void
     */
    protected function monitor(): void
    {
        foreach ($this->watchTime as $pathname => $ct) {
            $this->watchTime[$pathname] = filectime($pathname);
            if ($ct != $this->watchTime[$pathname]) {
                $this->plugin->log("[{$pathname}]被修改.");
                $this->plugin->log("[RESTART]:正在进行重启..");
                \Kernel\Service\App::inst()->restart();
            }
        }
    }
}