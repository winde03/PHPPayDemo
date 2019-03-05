<?php

/**
 * @Author: meow
 * @Date:   2018-04-07 15:35:05
 * @Last Modified by:   meow
 * @Last Modified time: 2018-04-07 21:13:25
 */

/**
* 创建支付接口基类
*/
class XcxBase{
		
	const KEY = "";
	const APPID = "";
	const SECRET = "";
	const MCH_ID ="";
	/**
	 * 生成签名
	 * @Author meow
	 * @DateTime  2018-04-07T15:38:55+0800
	 * @param     string                   $arr [签名数组]
	 * $arr = [
	 * 		"appid"=>"qiaoyuok",
	 * 		"mch_id"=>"wahaha",
	 * 		"char"=>"123456"
	 * ]
	 * @return    [type]                        [description]
	 */
	
	public function getSign($arr=''){

		// 将签名项去掉
		if (array_key_exists("sign", $arr)) {
			unset($arr['sign']);
		}

		// 去除数组中的空值
		$arr = array_filter($arr);
		// 桉字典进行排序
		ksort($arr);
		// 把数组拼成字符串
		$sign = http_build_query($arr);  //http_build_query()把数组快速拼接为url键值对参数
		// 加上密钥key
		$sign = urldecode($sign."&key=".$this::KEY);  //urldecode()解决http_bulid_query函数使用后的中文转码问题

		// 先进行md5加密，再转为大写 最终生成签名
		$sign =  strtoupper(md5($sign));

		return $sign;
	}

	// 获取带签名的数组
	public function setSign($arr=''){
		
		$sign = $this->getSign($arr);

		$arr["sign"] = $sign;

		return $arr;

	}

	// 校验签名 带有签名的数组
	public function checkSign($arr=''){
		if(empty($arr)){
			return false;
		}
		// 先签名，再比对签名
		$sign = $this->getSign($arr);

		if ($sign == $arr['sign']) {
			return true;
		}else{
			return false;
		}
	}

	public function getOpenId($code=''){
		
		if (empty($code)) {
			echo '{"status":0,"msg":"没有传来code"}';
			exit;
		}

		// 获取用户openid或者unionid(此接口和微信公众号接口不同)
		$url = "https://api.weixin.qq.com/sns/jscode2session?appid=".self::APPID."&secret=".self::SECRET."&js_code=".$code."&grant_type=authorization_code";

		// 采用curl抓取内容
		$ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url); 
        curl_setopt($ch,CURLOPT_HEADER,0); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 ); 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
        $res = curl_exec($ch); 
        curl_close($ch);
        $res = json_decode($res,true);
        return $res['openid'];
	}
	
	// 统一下单api
	public function unifiedOrder($sn='',$code=''){
		
		// 1:构建原始数据
		$nonce_str = md5(time());
		$params = [

			"appid"=>self::APPID,
			"mch_id"=>self::MCH_ID,
			"nonce_str"=>$nonce_str,
			"body"=>"商品描述",
			"out_trade_no"=>$sn,
			"total_fee"=>2000,
			"spbill_create_ip"=>$_SERVER['REMOTE_ADDR'],
			"notify_url"=>"https://wx.gypostmsd.com/wxpay/notify.php",
			"trade_type"=>"JSAPI",
			"product_id"=>"123",
			"openid"=>$code,
		];

		// 2:加入签名
		$sign = $this->setSign($params);
		// var_dump($sign);
		// 3:将数组转化为XML
		$xml = $this->arrToXml($sign);
		$res = $this->psotXML($xml);

		// 返回的数据为XML格式，将其转化为数组
		$arr = $this->xmlToArr($res);
		return $arr;
	}

	// 获取prepayid
	public function getPrepayId($sn='',$code=''){	
		$res = $this->unifiedOrder($sn,$code);
		return $res['prepay_id'];
	}

	// 获取支付所需的json数据
	public function getJson($prepay_id=''){
		
		$params = [
			"appId"=>self::APPID,
			"timeStamp"=>"".time()."",
			"nonceStr"=>md5(time()),
			"package"=>""."prepay_id=".$prepay_id."",
			"signType"=>"MD5"
		];
		$params['paySign'] = $this->getSign($params);
		return json_encode($params);
	}

	// 数组转XML
	public function arrToXml($arr=''){

		if(!is_array($arr) || count($arr) == 0) return '';

		$xml = "<xml>";

		foreach ($arr as $key=>$val)
		{
			if (is_numeric($val)){
				$xml.="<".$key.">".$val."</".$key.">";
			}else{
				$xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
			}
		}

		$xml.="</xml>";
		return $xml; 
	}

	// XML转数组
	public function xmlToArr($xml=''){

		if($xml == '') return '';

		libxml_disable_entity_loader(true);
		$arr = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);		
		return $arr;
	}

	// 发送XML数据
	public function psotXML($data=''){
		$url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
		$ch = curl_init();
		$params[CURLOPT_URL] = $url;    //请求url地址
		$params[CURLOPT_HEADER] = false; //是否返回响应头信息
		$params[CURLOPT_RETURNTRANSFER] = true; //是否将结果返回
		$params[CURLOPT_FOLLOWLOCATION] = true; //是否重定向
		$params[CURLOPT_SSL_VERIFYHOST] = false; //是否重定向
		$params[CURLOPT_SSL_VERIFYPEER] = false; //是否重定向
		$params[CURLOPT_POST] = true;
		$params[CURLOPT_POSTFIELDS] = $data;

		curl_setopt_array($ch, $params); //传入curl参数
		$content = curl_exec($ch); //执行
		curl_close($ch); //关闭连接
		return $content; //输出登录结果

	}

	// 获取POST过来的数据
	public function getPost(){
		return file_get_contents('php://input'); 
	}
}
