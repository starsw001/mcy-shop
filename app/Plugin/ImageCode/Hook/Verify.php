<?php
declare (strict_types=1);

namespace App\Plugin\ImageCode\Hook;

use Kernel\Annotation\Hook;
use Kernel\Annotation\Inject;
use Kernel\Exception\JSONException;
use Kernel\Plugin\Abstract\Plugin;
use Kernel\Plugin\Const\Point;
use Kernel\Util\Arr;

class Verify extends Plugin
{

    #[Inject]
    private \App\Plugin\ImageCode\Service\Code $code;

    /**
     * @param array $map
     * @throws JSONException
     * @throws \ReflectionException
     */
    #[Hook(point: Point::SERVICE_AUTH_SEND_EMAIL_BEFORE)]
    public function sendEmail(array $map): void
    {
        if (!\Kernel\Util\Verify::equals(Arr::get($this->plugin->getConfig(), "send_email"), 1)) {
            return;
        }

        $code = $map['image_code'] ?? "";
        if (!$this->code->verify("send_email", $code)) {
            throw new JSONException("图片验证码有误");
        }
    }

    /**
     * @param array $map
     * @throws JSONException
     * @throws \ReflectionException
     */
    #[Hook(point: Point::SERVICE_AUTH_REGISTER_BEFORE)]
    public function register(array $map): void
    {
        if (!\Kernel\Util\Verify::equals(Arr::get($this->plugin->getConfig(), "register_status"), 1)) {
            return;
        }

        $code = $map['image_code'] ?? "";
        if (!$this->code->verify("register", $code)) {
            throw new JSONException("图片验证码有误");
        }
    }

    /**
     * @param array $map
     * @throws JSONException
     * @throws \ReflectionException
     */
    #[Hook(point: Point::SERVICE_AUTH_LOGIN_BEFORE)]
    public function login(array $map): void
    {
        if (!\Kernel\Util\Verify::equals(Arr::get($this->plugin->getConfig(), "login_status"), 1)) {
            return;
        }

        $code = $map['image_code'] ?? "";
        if (!$this->code->verify("login", $code)) {
            throw new JSONException("图片验证码有误");
        }
    }

    /**
     * @param array $map
     * @throws JSONException
     * @throws \ReflectionException
     */
    #[Hook(point: Point::SERVICE_AUTH_RESET_BEFORE)]
    public function reset(array $map): void
    {
        if (!\Kernel\Util\Verify::equals(Arr::get($this->plugin->getConfig(), "reset_status"), 1)) {
            return;
        }

        $code = $map['image_code'] ?? "";
        if (!$this->code->verify("reset", $code)) {
            throw new JSONException("图片验证码有误");
        }
    }
}