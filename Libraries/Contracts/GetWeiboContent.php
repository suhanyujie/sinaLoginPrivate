<?php

/**
 *  GetWeiboHtml.php 抓取微博内容封装
 *
 * @copyright			(C) daweilang
 * @license				https://github.com/daweilang/
 *
 */


class GetWeiboContent
{
	private $cookieWeibo;
	private $cookieGet;
	
	
	public function __construct()
	{
		//微博cookie
		$this->cookieWeibo = SINA_LOGIN_ROOT."wbcookie/cookie_weibo.txt";
		$this->cookieGet =  SINA_LOGIN_ROOT."wbcookie/cookie_curl.txt";
	}
	
	public function getWeiboHtml($url)
	{
		$wbLogin = new WeiboLogin();
		//测试抓取
		$content = $wbLogin->getWBHtml($url, $this->cookieWeibo, $this->cookieGet);
		file_put_contents(SINA_LOGIN_ROOT."wbcookie/weibo_explain", $content);
		return true;
	}
	
}
