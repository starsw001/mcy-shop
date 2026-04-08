<?php
declare(strict_types=1);

namespace App\Plugin\HandShip\Controller;

use App\Interceptor\Plugin;
use Kernel\Annotation\Interceptor;
use Kernel\Context\Interface\Response;
use Kernel\Plugin\Abstract\Controller;

#[Interceptor(class: [Plugin::class], type: Interceptor::VIEW)]
class Order extends Controller
{


    /**
     * @return Response
     */
    public function index(): Response
    {
        return $this->render(template: "Order.html", title: "人工发货", paths: [$this->isUsr() ? BASE_PATH . "/app/View/User/" : BASE_PATH . "/app/View/Admin/"]);
    }
}