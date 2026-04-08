<?php
declare(strict_types=1);

namespace App\Plugin\Alipay\Service;

use Kernel\Annotation\Bind;
use Kernel\Plugin\Entity\Plugin;

#[Bind(class: \App\Plugin\Alipay\Service\Bind\Alipay::class)]
interface Alipay
{
    /**
     * @param Plugin $plugin
     * @param array $config
     * @param string $notifyUrl
     * @param string $tradeNo
     * @param string $amount
     * @return string
     */
    public function face(Plugin $plugin, array $config, string $notifyUrl, string $tradeNo, string $amount): string;


    /**
     * @param Plugin $plugin
     * @param array $config
     * @param string $notifyUrl
     * @param string $returnUrl
     * @param string $tradeNo
     * @param string $amount
     * @return string
     */
    public function pc(Plugin $plugin, array $config, string $notifyUrl, string $returnUrl, string $tradeNo, string $amount): string;


    /**
     * @param Plugin $plugin
     * @param array $config
     * @param string $notifyUrl
     * @param string $returnUrl
     * @param string $tradeNo
     * @param string $amount
     * @return string
     */
    public function h5(Plugin $plugin, array $config, string $notifyUrl, string $returnUrl, string $tradeNo, string $amount): string;
}