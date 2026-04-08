<?php

namespace App\Plugin\TopCat\Hook;

use App\View\Helper;
use Kernel\Annotation\Hook;
use Kernel\Annotation\Inject;
use Kernel\Context\Interface\Request;
use Kernel\Exception\JSONException;
use Kernel\Exception\NotFoundException;
use Kernel\Plugin\Abstract\Plugin;
use Kernel\Plugin\Const\Point;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Main extends Plugin
{
    #[Inject]
    protected Request $request;

    #[Hook(point: Point::INDEX_HEADER)]
    public function header(): string
    {
        return Helper::inst()->loadCss(resource: "/app/Plugin/TopCat/Assets/css/cat.css", cdn: false);
    }

    /**
     * @return string
     */
    #[Hook(point: Point::INDEX_FOOTER)]
    public function body(): string
    {
        $config = $this->plugin->getConfig();
        if (!empty($config['rightWidth'])) {
            return '<div class="back-to-top faa-float animated cd-is-visible" style="top: -900px;right:' . $config['rightWidth'] . 'px;"></div>';
        } else {
            return '<div class="back-to-top faa-float animated cd-is-visible" style="top: -900px;right:100px;"></div>';
        }
    }

    #[Hook(point: Point::INDEX_FOOTER)]
    public function footer(): string
    {
        return Helper::inst()->loadJs(resource: "/app/Plugin/TopCat/Assets/js/cat.js", cdn: false);
    }
}