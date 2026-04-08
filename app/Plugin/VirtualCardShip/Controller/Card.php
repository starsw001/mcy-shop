<?php
declare(strict_types=1);

namespace App\Plugin\VirtualCardShip\Controller;

use App\Interceptor\Plugin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Abstract\Controller;

#[Interceptor(class: [Plugin::class], type: Interceptor::VIEW)]
class Card extends Controller
{

    use \Kernel\Component\Plugin;


    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render(template: "Card.html", title: "虚拟卡密", paths: [$this->isUsr() ? BASE_PATH . "/app/View/User/" : BASE_PATH . "/app/View/Admin/"]);
    }
}