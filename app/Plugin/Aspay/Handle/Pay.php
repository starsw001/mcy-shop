<?php
declare (strict_types=1);

namespace App\Plugin\Aspay\Handle;

use GuzzleHttp\Exception\GuzzleException;
use Kernel\Context\Interface\Response;
use Kernel\Exception\HandleException;
use Kernel\Exception\JSONException;
use Kernel\Util\Http;
use Kernel\Util\UserAgent;

class Pay extends \Kernel\Plugin\Abstract\Pay
{

    /**
     * @return \Kernel\Plugin\Entity\Pay
     * @throws HandleException
     * @throws JSONException
     * @throws \ReflectionException
     */
    public function create(): \Kernel\Plugin\Entity\Pay
    {
        if (!$this->config['url'] ||
            !$this->config['pid'] ||
            !$this->config['key'])
        {
            throw new JSONException("重要参数缺失，请检查插件配置文件！");
        }

        $title = isset($this->config['order_title']) ? str_replace('${trade_no}', $this->order->trade_no, $this->config['order_title']) : "商品订单号:{$this->order->trade_no}";

        $param = [
            'pid' => $this->config['pid'],
            'type' => $this->code,
            'out_trade_no' => $this->order->trade_no,
            'notify_url' => $this->asyncUrl,
            'return_url' => $this->syncUrl,
            'name' => $title,
            'money' => $this->amount,
            'sitename' => $this->order->trade_no,
            'clientip' => $this->clientIp,
            'device' => UserAgent::isMobile($this->request->header("UserAgent")) ? 'mobile' : 'pc'
        ];

        $url = trim($this->config['url'], "/");

        $param['sign'] = $this->sign($param, $this->config['key']);
        $param['sign_type'] = "MD5";

        $url .= $this->config['mapi'] == 1 ? "/mapi.php" : "/submit.php";

        $code = 1;

        $pay = new \Kernel\Plugin\Entity\Pay();
        $timeout = isset($this->config['timeout']) && $this->config['timeout'] > 300 ? $this->config['timeout'] : 300;
        $pay->setTimeout((int)$timeout);

        $types = [
            'alipay' => \Kernel\Plugin\Const\Pay::RENDER_COMMON_ALIPAY_VIEW,
            'wxpay' => \Kernel\Plugin\Const\Pay::RENDER_COMMON_WECHAT_VIEW,
            'qqpay' => \Kernel\Plugin\Const\Pay::RENDER_COMMON_QQ_VIEW
        ];

        if ($this->config['mapi'] == 1) {
            try {
                $response = Http::make()->post($url, [
                    "form_params" => $param
                ]);
                $json = json_decode($response->getBody()->getContents(), true);

                if (!isset($json)) {
                    $this->plugin->log("订单号：{$this->order->trade_no}，下单失败；订单返回参数为空！！！");
                    throw new HandleException("下单失败#0");
                }

                if (!isset($json['code']) || $json['code'] != $code) {
                    throw new HandleException($json['msg'] ?? "下单失败#1");
                }

                if (isset($json['qrcode'])) {
                    $pay->setPayUrl($json['qrcode']);
                    $pay->setRenderMode($types[$this->code]);
                    return $pay;
                } elseif (isset($json['payurl'])) {
                    $pay->setPayUrl($json['payurl']);
                    $pay->setRenderMode(\Kernel\Plugin\Const\Pay::RENDER_JUMP);
                    return $pay;
                } elseif (isset($json['urlscheme'])) {
                    $pay->setPayUrl($json['urlscheme']);
                    $pay->setRenderMode(\Kernel\Plugin\Const\Pay::RENDER_JUMP);
                    return $pay;
                }
            } catch (\Throwable $e) {
                $this->plugin->log("商户ID：{$this->config['pid']}，MAPI请求出错：" . $e->getMessage(), true);
                $this->plugin->log($json);
                throw new JSONException("支付接口出错，请查看插件日志");
            }
        }

        $this->plugin->log("订单号：{$this->order->trade_no}，下单成功；下单金额：{$this->amount}");

        $pay->setPayUrl($url);
        $pay->setRenderMode(\Kernel\Plugin\Const\Pay::RENDER_FORM_POST_SUBMIT);
        $pay->setOption($param);
        return $pay;
    }


    /**
     * @return Response
     * @throws JSONException
     * @throws \ReflectionException
     */
    public function async(): Response
    {
        $data = $this->request->post() ?: $this->request->get();

        if (!isset($data['sign']) || !$this->verification($data) ||
            $this->code != $data['type'] ||
            $this->order->trade_no != $data['out_trade_no'] ||
            $this->amount != $data['money'] ||
            $data['trade_status'] != "TRADE_SUCCESS")
        {
            $this->plugin->log($data);
            throw new JSONException("回调参数错误!");
        }

        if (isset($this->config['order_check']) &&
            $this->config['order_check'] == 1 &&
            !$this->query($data['trade_no']))
        {
            throw new JSONException("远程订单检查失败!");
        }
        $this->plugin->log("订单号：{$this->order->trade_no}，支付成功；回调金额：{$this->amount}，第三方金额：{$data['money']}");

        $this->successful();
        return $this->response->raw("SUCCESS");
    }

    /**
     * @param string $tradeNo
     * @return bool
     * @throws \ReflectionException
     */
    private function query(string $tradeNo): bool
    {
        $url = trim($this->config['url'], "/");

        try {
            $queryParams = [
                'act' => 'order',
                'pid' => $this->config['pid'],
                'key' => $this->config['key'],
                'trade_no' => $tradeNo,
                'out_trade_no' => $this->order->trade_no
            ];
            $response = Http::make()->get($url . "/api.php?" . http_build_query($queryParams));
            $json = json_decode($response->getBody()->getContents() , true);
            if (isset($json['code']) && (int)$json['code'] === 1 && (int)$json['status'] === 1) {
                return true;
            }
        } catch (\Throwable $e) {
            $this->plugin->log("商户ID：{$this->config['pid']}，回调时验证订单请求出错：" . $e->getMessage(), true);
            $this->plugin->log($json);
        }
        return false;
    }

    /**
     * @param array $data
     * @return bool
     */
    private function verification(array $data): bool
    {
        $sign = $data['sign'];
        unset($data['sign_type'], $data['sign']);
        return $sign == $this->sign($data, $this->config['key']);
    }


    /**
     * @param array $data
     * @param string $key
     * @return string
     */
    private function sign(array $data, string $key): string
    {
        ksort($data);
        $sign = '';
        foreach ($data as $k => $v) {
            $sign .= $k . '=' . $v . '&';
        }
        $sign = trim($sign, '&');

        return md5($sign . $key);
    }

    /**
     * @param array $param
     * @return string
     */
    private function getStr(array $param): string
    {
        ksort($param);
        $signstr = '';
        foreach ($param as $k => $v) {
            $v = (string)$v;
            if(is_array($v) || $this->isEmpty($v) || $k == 'sign' || $k == 'sign_type') continue;
            $signstr .= '&' . $k . '=' . $v;
        }
        return substr($signstr, 1);
    }

    private function isEmpty($value)
    {
        return $value === null || trim($value) === '';
    }
}