<?php
declare (strict_types=1);

namespace App\Plugin\ImageCode\Hook;

use Kernel\Annotation\Hook;
use Kernel\Exception\HandleException;
use Kernel\Exception\NotFoundException;
use Kernel\Plugin\Abstract\Plugin;
use Kernel\Plugin\Const\Point;
use Kernel\Util\Arr;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Code extends Plugin
{

    /**
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws NotFoundException
     * @throws \ReflectionException
     */

    #[Hook(point: Point::USER_AUTH_REGISTER_FORM)]
    public function register(): string
    {
        if (!\Kernel\Util\Verify::equals(Arr::get($this->plugin->getConfig(), "register_status"), 1)) {
            return '';
        }
        return $this->plugin->view("Form.html", ["type" => "register"]);
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws NotFoundException
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \ReflectionException
     */
    #[Hook(point: Point::USER_AUTH_RESET_FORM)]
    public function reset(): string
    {
        if (!\Kernel\Util\Verify::equals(Arr::get($this->plugin->getConfig(), "reset_status"), 1)) {
            return '';
        }
        return $this->plugin->view("Form.html", ["type" => "reset"]);
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws NotFoundException
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \ReflectionException
     */
    #[Hook(point: Point::USER_AUTH_LOGIN_FORM)]
    public function login(): string
    {
        if (!\Kernel\Util\Verify::equals(Arr::get($this->plugin->getConfig(), "login_status"), 1)) {
            return '';
        }
        return $this->plugin->view("Form.html", ["type" => "login"]);
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws NotFoundException
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \ReflectionException
     */
    #[Hook(point: Point::USER_AUTH_REGISTER_BODY)]
    public function registerBody(): string
    {
        if (!\Kernel\Util\Verify::equals(Arr::get($this->plugin->getConfig(), "register_status"), 1)) {
            return '';
        }
        return $this->plugin->view("Footer.html", ["type" => "register", "email" => Arr::get($this->plugin->getConfig(), "send_email")]);
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws NotFoundException
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \ReflectionException
     */
    #[Hook(point: Point::USER_AUTH_RESET_BODY)]
    public function resetBody(): string
    {
        if (!\Kernel\Util\Verify::equals(Arr::get($this->plugin->getConfig(), "reset_status"), 1)) {
            return '';
        }
        return $this->plugin->view("Footer.html", ["type" => "reset", "email" => Arr::get($this->plugin->getConfig(), "send_email")]);
    }

    /**
     * @return string
     * @throws LoaderError
     * @throws NotFoundException
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws \ReflectionException
     */
    #[Hook(point: Point::USER_AUTH_LOGIN_BODY)]
    public function loginBody(): string
    {
        if (!\Kernel\Util\Verify::equals(Arr::get($this->plugin->getConfig(), "login_status"), 1)) {
            return '';
        }
        return $this->plugin->view("Footer.html", ["type" => "login", "email" => Arr::get($this->plugin->getConfig(), "send_email")]);
    }
}