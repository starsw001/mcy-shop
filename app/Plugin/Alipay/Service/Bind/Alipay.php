<?php
declare(strict_types=1);

namespace App\Plugin\Alipay\Service\Bind;

use Kernel\Exception\ServiceException;
use Kernel\Plugin\Entity\Plugin;

class Alipay implements \App\Plugin\Alipay\Service\Alipay
{

    /**
     * @param Plugin $plugin
     * @param array $config
     * @param string $notifyUrl
     * @param string $tradeNo
     * @param string $amount
     * @return string
     * @throws ServiceException
     */
    public function face(Plugin $plugin, array $config, string $notifyUrl, string $tradeNo, string $amount): string
    {
        $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
        $params->appID = $config['app_id'];
        $params->appPrivateKey = $config['private_key'];
        $params->appPublicKey = $config['alipay_public_key'];
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
        $request = new \Yurun\PaySDK\AlipayApp\FTF\Params\QR\Request;
        $request->notify_url = $notifyUrl;
        $request->businessParams->out_trade_no = $tradeNo; // 商户订单号
        $request->businessParams->total_amount = $amount; // 价格
        $request->businessParams->subject = "商品购买-订单号:" . $tradeNo; //商品标题
        try {
            $data = $pay->execute($request);
            $qrcode = (string)$data['alipay_trade_precreate_response']['qr_code'];
            if ($qrcode == '') {
                throw new ServiceException("下单失败");
            }
            return $qrcode;
        } catch (\Exception $e) {
            $plugin->log("下单失败，原因：{$e->getMessage()}");
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param Plugin $plugin
     * @param array $config
     * @param string $notifyUrl
     * @param string $returnUrl
     * @param string $tradeNo
     * @param string $amount
     * @return string
     * @throws ServiceException
     */
    public function pc(Plugin $plugin, array $config, string $notifyUrl, string $returnUrl, string $tradeNo, string $amount): string
    {
        $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
        $params->appID = $config['app_id'];
        $params->appPrivateKey = $config['private_key'];
        $params->appPublicKey = $config['alipay_public_key'];
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
        // 支付接口
        $request = new \Yurun\PaySDK\AlipayApp\Page\Params\Pay\Request;
        $request->notify_url = $notifyUrl;
        $request->return_url = $returnUrl;
        $request->businessParams->out_trade_no = $tradeNo; // 商户订单号
        $request->businessParams->total_amount = $amount; // 价格
        $request->businessParams->subject = "商品购买-订单号:" . $tradeNo; //商品标题
        try {
            $pay->prepareExecute($request, $url);
            if ($url == '') {
                throw new ServiceException("下单失败");
            }
            return $url;
        } catch (\Exception $e) {
            $plugin->log("下单失败，原因：{$e->getMessage()}");
            throw new ServiceException($e->getMessage());
        }
    }

    /**
     * @param Plugin $plugin
     * @param array $config
     * @param string $notifyUrl
     * @param string $returnUrl
     * @param string $tradeNo
     * @param string $amount
     * @return string
     * @throws ServiceException
     */
    public function h5(Plugin $plugin, array $config, string $notifyUrl, string $returnUrl, string $tradeNo, string $amount): string
    {
        $params = new \Yurun\PaySDK\AlipayApp\Params\PublicParams;
        $params->appID = $config['app_id'];
        $params->appPrivateKey = $config['private_key'];
        $params->appPublicKey = $config['alipay_public_key'];
        $pay = new \Yurun\PaySDK\AlipayApp\SDK($params);
        // 支付接口
        $request = new \Yurun\PaySDK\AlipayApp\Wap\Params\Pay\Request;
        $request->notify_url = $notifyUrl;
        $request->return_url = $returnUrl;
        $request->businessParams->out_trade_no = $tradeNo; // 商户订单号
        $request->businessParams->total_amount = $amount; // 价格
        $request->businessParams->subject = "商品购买-订单号:" . $tradeNo; //商品标题
        try {
            $pay->prepareExecute($request, $url);
            if ($url == '') {
                throw new ServiceException("下单失败");
            }
            return $url;
        } catch (\Exception $e) {
            $plugin->log("下单失败，原因：{$e->getMessage()}");
            throw new ServiceException($e->getMessage());
        }
    }
}