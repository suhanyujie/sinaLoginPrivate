<?php
define( 'SINA_LOGIN_ROOT', dirname(__FILE__).'/' );
require('Libraries/function/helpers.php');

require('Libraries/Classes/CurlHandler.php');
require('Libraries/Classes/WeiboLogin.php');

require('Libraries/Contracts/GetWeiboContent.php');

require('GetWeiboCookie.php');

require('Main.php');


// 访问地址 http://localhost/2017/sinaLoginPrivate/
// 分为 CLI环境 和browser环境
if ( php_sapi_name() == 'cli') {
	foreach( $argv as $k=>$v){
		if($k == 1){
			list($k,$v) = explode('=',$v);break;
		}
	}
	$str = $v;
}else{
	$str = $_SERVER['REQUEST_URI'];
}


$pathInfo = pathinfo($str);
$methodName = isset($_REQUEST['a']) ? addslashes($_REQUEST['a']) : '';

switch ( $methodName ) {
	case 'browserLogin':
		$auth = new Main();
		$auth->browserLogin();
		break;
	case 'setTestUrl':
		$auth = new Main();
		$url = 'http://d.weibo.com/p/aj/v6/mblog/mbloglist?ajwvr=6&domain=102803_ctg1_2088_-_ctg1_2088&from=faxian_hot&mod=fenlei&pagebar=0&tab=home&current_page=1&pre_page=1&page=1&pl_name=Pl_Core_NewMixFeed__3&id=102803_ctg1_2088_-_ctg1_2088&script_uri=/102803_ctg1_2088_-_ctg1_2088&feed_type=1&domain_op=102803_ctg1_2088_-_ctg1_2088&__rnd=1490174458161';// 在这里 填写 你要抓的url
		// var_dump($url);exit();
		$auth->getTestContent($url);
		exit();
	default:
		
		break;
}


$auth = new Main();
$auth->setConfig();
$auth->getPreParam();
$auth->getRsaPwd();

exit(PHP_EOL.'#退出final#'.PHP_EOL);


