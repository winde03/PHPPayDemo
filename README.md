# 小程序微信支付Demo

![版本 0.1](https://img.shields.io/badge/版本-0.1-red.svg) 

> 简单的小程序微信支付Demo

## 当前功能如下

- 微信支付获取repayid
- 获取回调并且记录

### 需要修改的参数

XcxBase.php中的
	`const KEY = "";` 
	`const APPID = "";`
	`const SECRET = "";`
	`const MCH_ID ="";`
117行的回调地址.如https://你的域名/notify.php
	