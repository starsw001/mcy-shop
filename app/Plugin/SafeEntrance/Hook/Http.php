<?php

namespace App\Plugin\SafeEntrance\Hook;

use Kernel\Annotation\Hook;
use Kernel\Annotation\Inject;
use Kernel\Constant\Exception;
use Kernel\Context\Interface\Request;
use Kernel\Context\Interface\Response;
use Kernel\Exception\NotFoundException;
use Kernel\Plugin\Abstract\Plugin;
use Kernel\Plugin\Const\Point;
use Kernel\Session\Session;
use Kernel\Util\Arr;

class Http extends Plugin
{

    #[Inject]
    protected Session $session;

    /**
     * @throws NotFoundException
     */
    #[Hook(point: Point::HTTP_REQUEST_START)]
    public function safeEntrance(Request $request, Response $response): Response
    {
        $url = $this->plugin->getConfig("url");
        $whitelist = Arr::strToList((string)$this->plugin->getConfig("whitelist"), "\n");
        $clientIp = $request->clientIp();
        $clientIp1 = preg_replace('/(\d+\.\d+\.\d+)\.\d+/', '$1.*', $clientIp);
        $clientIp2 = preg_replace('/(\d+\.\d+)\.\d+\.\d+/', '$1.*.*', $clientIp);

        if ($url && trim($request->uri(), "/") == $url) {
            $this->session->set("_safe_entrance_state", true);
            return $response->redirect("/admin")->end();
        }

        if (str_starts_with($request->uri(), "/admin")) {
            if ($url && $this->session->get("_safe_entrance_state") !== true) {
                throw new NotFoundException(Exception::NOT_FOUND);
            }
            if (count($whitelist) > 0 && (!in_array($clientIp, $whitelist) && !in_array($clientIp1, $whitelist) && !in_array($clientIp2, $whitelist))) {
               // throw new NotFoundException(Exception::NOT_FOUND);
            }
        }

        return $response;
    }
}