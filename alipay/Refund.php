<?php

namespace alipay;

use think\Loader;

Loader::import('alipay.pagepay.service.AlipayTradeService');
loader::import('alipay.pagepay.buildermodel.AlipayTradeRefundContentBuilder');

/**
* 统一收单交易退款接口
*
* 用法:
* 调用 \alipay\Refund::exec($params) 即可
*/
class Refund
{
    /**
     * 主入口
     * @param  array $params 退款参数, 具体如下
     * @param string $params['trade_no']/$params['out_trade_no'] 商户订单号或支付宝单号其中之一
     * @param string $params['out_request_no'] 商户退款号(可选, 如同一笔交易多次退款, 则必填)
     * @param float  $params['refund_amount'] 退款金额
     */
    public static function exec($params)
    {
        // 1.校检参数
        self::checkParams($params);

        // 2.构造请求参数
        $RequestBuilder = self::builderParams($params);

        // 3.获取配置
        $config = config('alipay');
        $aop    = new \AlipayTradeService($config);

        // 4.发送请求
        $response = $aop->Refund($RequestBuilder);

        // 5.转为数组格式返回
        return json_decode(json_encode($response), true);
    }

    /**
     * 校检参数
     */
    private static function checkParams($params)
    {
        if (empty($params['trade_no']) && empty($params['out_trade_no'])) {
            self::processError('商户订单号(out_trade_no)和支付宝单号(trade_no)不得通知为空');
        }

        if (floatval(trim($params['refund_amount'])) <= 0) {
            self::processError('退款金额(refund_amount)为大于0的数');
        }
    }

    /**
     * 构造请求参数
     */
    private static function builderParams($params)
    {
        $RequestBuilder = new \AlipayTradeRefundContentBuilder();

        // 1.判断单号类型
        if (isset($params['trade_no'])) {
            $RequestBuilder->setTradeNo($params['trade_no']);
        } else {
            $RequestBuilder->setOutTradeNo($params['trade_no']);
        }

        // 2.判断是否部分退款
        if (!empty($params['out_request_no'])) {
            $RequestBuilder->setOutRequestNo($params['out_request_no']);
        }

        $RequestBuilder->setRefundAmount($params['refund_amount']);
        return $RequestBuilder;
    }

    /**
     * 统一错误处理接口
     * @param  string $msg 错误描述
     */
    private static function processError($msg)
    {
        throw new \think\Exception($msg);
    }
}