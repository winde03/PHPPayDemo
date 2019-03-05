<?php
header("Content-type:text/html;charset=utf-8");	
require_once "XcxBase.php";

// 1：获取通知数据  ->转为数组
// 2：验证签名	->签名校验
// 3：验证业务结果	->result_code  和  return_code
// 4：验证订单号和金额  out_trade_no 和 total_fee
// 5：修改订单状态，可以发货

$XcxBase = new XcxBase();

$xmldata = $XcxBase->getPost();

$arr = $XcxBase->xmlToArr($xmldata);
if($XcxBase->checkSign($arr)){
	if($arr['result_code']=="SUCCESS" && $arr['return_code'] == "SUCCESS"){
		// 根据订单号查询数据库中该订单的金额
		if($arr['total_fee'] == 2000){

			file_put_contents("./log.txt", "用户:".$arr['openid']."支付完成"."订单金额".$arr['total_fee']."订单号:".$arr['out_trade_no'].PHP_EOL,FILE_APPEND);
			// 给微信服务器做应答
			$params = [
				"return_code"=>"SUCCESS",
				"return_msg"=>"OK"
			];
			echo $XcxBase->arrToXml($params);
		}else{

			file_put_contents("./log.txt","用户:".$arr['openid']."支付金额有误"."订单金额".$arr['total_fee']."订单号:".$arr['out_trade_no'].PHP_EOL,FILE_APPEND);

		}
	}else{
		file_put_contents("./log.txt", "业务结果错误",FILE_APPEND);
	}
}else{
	file_put_contents("./log.txt", "签名错误！",FILE_APPEND);
}
    function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }
        
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);
        
        return $data;
    }

?>