<?php
/*
Plugin Name: V免签
Version: 1.0.0
Plugin URL:
Description: 支持原版V免签
Author: NKXingXh
Author URL: https://www.nkxingxh.top/
*/


use app\common\controller\Hm;

!defined('ROOT_PATH') && exit('access deined!');


function pay($order, $goods, $params = [])
{
    $plugin_path = ROOT_PATH . "content/plugin/vpay_pay/";

    $info = file_get_contents("{$plugin_path}vpay_pay_setting.json");
    $info = json_decode($info, true);

    $data = array(
        'payId' => $order['order_no'],                       //商户订单号
        'type' => $params['pay_type'] == 'alipay' ? 2 : 1, //支付方式
        'price' => $order['money'],                         //订单金额
        'param' => '',
        'isHtml' => 1,
        'notifyUrl' => $params['notify_url'],               //异步通知地址
        'returnUrl' => $params['return_url']               //同步通知地址
    );

    $data['sign'] = getSign($data, $info['secret_key']);
    $gateway_url = rtrim($info['gateway_url'], '/') . '/createOrder';

    return [
        'code' => 200,
        'data' => $data,
        'gateway_url' => $gateway_url,
        'mode' => 'form'
    ];
}

/**
 * 验签
 */
function checkSign($data = null)
{
    $plugin_path = ROOT_PATH . "content/plugin/vpay_pay/";
    $info = file_get_contents("{$plugin_path}vpay_pay_setting.json");
    $info = json_decode($info, true);
    $data = $data == null ? Hm::getParams('get') : $data;

    $sign = $data['sign'];
    $server_sign = md5($data['payId'] . $data['param'] . $data['type'] . $data['price'] . $data['reallyPrice'] . $info['secret_key']);
    if ($server_sign == $sign) return $data['payId'];
    return false;
}


/**
 * 生成签名结果
 * return 签名结果字符串
 */
function getSign($data, $secret_key)
{
    return md5($data['payId'] . $data['param'] . $data['type'] . $data['price'] . $secret_key);
}
