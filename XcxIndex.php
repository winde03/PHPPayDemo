<?php
header("Content-type:text/html;charset=utf-8");	
require_once "XcxBase.php";
session_start();
/**
 * @Author: meow
 * @Date:   2018-04-07 15:35:13
 * @Last Modified by:   meow
 * @Last Modified time: 2018-04-07 21:02:07
 */

$XcxBase = new XcxBase();

// 获取用户openID
$sn = md5(time());
$code = isset($_GET['openid'])?$_GET['openid']:"";
$detail = isset($_GET['detail'])?$_GET['detail']:"";
if(empty($code)){
  echo $code.'{"status":0,"msg":"code为空"}';
  exit ;
}
file_put_contents("./log2.txt", "订单内容:".$detail."订单号:".$sn.PHP_EOL,FILE_APPEND);
$prepar_id = $XcxBase->getPrepayId($sn,$code);
$json = $XcxBase->getJson($prepar_id);
echo $json;
?>