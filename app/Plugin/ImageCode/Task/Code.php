<?php
declare (strict_types=1);

namespace App\Plugin\ImageCode\Task;

use Kernel\Task\Interface\Task;

class Code implements Task
{
    private int $width = 100;
    private int $height = 40;
    private string $code;

    /**
     * @param string $code
     * @param int $width
     * @param int $height
     */
    public function __construct(string $code, int $width = 100, int $height = 40)
    {
        $this->code = $code;
        $this->height = $height;
        $this->width = $width;
    }

    /**
     * @return string
     */
    public function handle(): string
    {
        // 创建画布
        $image = imagecreatetruecolor($this->width, $this->height);

        // 设置透明背景
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $color = imagecolorallocatealpha($image, 0, 0, 0, 127);
        imagefill($image, 0, 0, $color);

        // 在画布上绘制随机干扰点
        for ($i = 0; $i < 200; $i++) {
            $x = mt_rand(0, $this->width - 1);
            $y = mt_rand(0, $this->height - 1);
            $color = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imagesetpixel($image, $x, $y, $color);
        }

        // 在画布上绘制随机干扰线
        for ($i = 0; $i < 3; $i++) {
            $x1 = mt_rand(0, $this->width - 1);
            $y1 = mt_rand(0, $this->height - 1);
            $x2 = mt_rand(0, $this->width - 1);
            $y2 = mt_rand(0, $this->height - 1);
            $color = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
            imageline($image, $x1, $y1, $x2, $y2, $color);
        }

        // 在画布上写入验证码文字
        $font = BASE_PATH . "/app/Plugin/ImageCode/Fonts/Arial.ttf";
        $size = 24;
        //    $x = ($this->width - imagefontwidth($size) * strlen($this->code)) / 4 ;
        //   $y = ($this->height - imagefontheight($size)) + 5;

        $x = ($this->width / 2) - (imagefontwidth($size) * strlen($this->code));
        $y = ($this->height / 2) + (int)(imagefontheight($size) / 1.5);

        $color = imagecolorallocate($image, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255));
        imagettftext($image, $size, 0, $x, $y, $color, $font, $this->code);

        ob_start();
        // 输出图像
        imagepng($image);
        $result = ob_get_contents();
        imagedestroy($image);
        ob_end_clean();
        return $result;
    }
}