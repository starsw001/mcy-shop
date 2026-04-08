<?php
declare(strict_types=1);

namespace App\Plugin\Alipay\Handle;

use App\Plugin\Alipay\Service\Alipay;
use Kernel\Annotation\Inject;
use Kernel\Context\Interface\Response;
use Kernel\Exception\HandleException;
use Kernel\Waf\Filter;

class Pay extends \Kernel\Plugin\Abstract\Pay
{

    #[Inject]
    private Alipay $alipay;

    /**
     * @return \Kernel\Plugin\Entity\Pay
     * @throws HandleException
     */
    public function create(): \Kernel\Plugin\Entity\Pay
    {
        $pay = new \Kernel\Plugin\Entity\Pay();
        switch ($this->code) {
            case "face":
                $url = $this->alipay->face($this->plugin, $this->config, $this->asyncUrl, $this->order->trade_no, $this->amount);
                $pay->setRenderMode(\Kernel\Plugin\Const\Pay::RENDER_COMMON_ALIPAY_VIEW);
                break;
            case "pc";
                $url = $this->alipay->pc($this->plugin, $this->config, $this->asyncUrl, $this->syncUrl, $this->order->trade_no, $this->amount);
                $pay->setRenderMode(\Kernel\Plugin\Const\Pay::RENDER_JUMP);
                break;
            case "h5";
                $url = $this->alipay->h5($this->plugin, $this->config, $this->asyncUrl, $this->syncUrl, $this->order->trade_no, $this->amount);
                $pay->setRenderMode(\Kernel\Plugin\Const\Pay::RENDER_JUMP);
                break;
            default:
                throw new HandleException("暂不支持该支付方式");
        }

        $pay->setPayUrl($url);
        return $pay;
    }

    /**
     * @return Response
     * @throws HandleException
     * @throws \Exception
     */
    public function async(): Response
    {
        $data = $this->request->post(flags: Filter::NORMAL);
        $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
        $params->appPrivateKey = $this->config['private_key'];
        $params->appPublicKey = $this->config['alipay_public_key'];
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
        $this->plugin->log($data);

        if (!$pay->verifyCallback($data)) {
            $this->plugin->log("签名验证失败", true);
            throw new HandleException("验证失败");
        }

        if ($data['total_amount'] != $this->amount) {
            $this->plugin->log("金额错误", true);
            throw new HandleException("金额错误");
        }

        if ($data['trade_status'] != "TRADE_SUCCESS") {
            $this->plugin->log("状态验证失败", true);
            throw new HandleException("状态验证失败");
        }

        $this->successful();

        return $this->response->raw("success");
    }
}