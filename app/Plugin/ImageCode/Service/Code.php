<?php
declare (strict_types=1);

namespace App\Plugin\ImageCode\Service;

use Kernel\Annotation\Inject;
use Kernel\Session\Session;
use Kernel\Task\Task;


class Code
{
    const IMAGE_CODE_SESSION = "image_code_%s";

    #[Inject]
    private Session $session;

    /**
     * @param string $key
     * @param int $width
     * @param int $height
     * @return string
     */
    public function create(string $key, int $width = 100, int $height = 40): string
    {
        $str = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < 4; $i++) {
            $code .= $str[mt_rand(0, 61)];
        }

        $key = sprintf(self::IMAGE_CODE_SESSION, $key);
        $this->session->set($key, [
            "code" => $code,
            "expire" => time() + 300
        ]);
        return Task::instance()->taskGetResults(new \App\Plugin\ImageCode\Task\Code($code, $width, $height));
    }


    /**
     * @param string $key
     * @param string $code
     * @return bool
     */
    public function verify(string $key, string $code): bool
    {
        $key = sprintf(self::IMAGE_CODE_SESSION, $key);
        $data = $this->session->get($key);

        if (!$data || strtolower($data['code']) != strtolower($code)) {
            return false;
        }

        if ($data['expire'] < time()) {
            $this->session->remove($key);
            return false;
        }

        $this->session->remove($key);
        return true;
    }
}