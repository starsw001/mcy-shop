<?php
declare (strict_types=1);

namespace App\Plugin\ImageCode\Controller;

use App\Controller\User\Base;
use Kernel\Annotation\Inject;
use Kernel\Context\Interface\Response;

class Code extends Base
{


    #[Inject]
    private \App\Plugin\ImageCode\Service\Code $code;


    /**
     * @param string $key
     * @return Response
     */
    public function create(string $key): Response
    {
        return $this->response->raw($this->code->create($key))->withHeader("Content-Type", "image/png");
    }
}