<?php

/**
 *  GetWeiboCookie.php 模拟登录获得新浪微博cookie
 *
 * @copyright			(C) daweilang
 * @license				https://github.com/daweilang/
 *
 * 程序说明：
 * 2016年11月之前可以通过新浪通行证登录，跳转获得微博cookie
 * 2016年11月新浪调整，新浪通行证需要经过SSO服务器后，对密码进行SHA1和RSA加密，之后登录
 * getEncodePwd方法使用请求本地页面，使用ssologin.js的核心算法模拟加密过程，获得加密后的密码
 */

class GetWeiboCookie
{
	/**
	 * 初始化名户名和密码
	 */
	//base64加密后用户名
	private $su = '';
	//js加密后的密码
	private $sp = '';
	
	//配置数组
	private $config;
	
	//预登录所需参数
	private $preParam = [];
	
	/**
	 * 初始化curl设置
	 */
	protected $loginData;
	
	public function __construct()
	{
		//加载微博登录配置
		$this->config = Config::get('weibo');

		//如果配置文件不存在，取系统配置
		if(!$this->getConfig()){
			if($this->config['login']['USERNAME'] && $this->config['login']['PASSWORD']){				
				$this->su = base64_encode($this->config['login']['USERNAME']);
				$this->sp = $this->config['login']['PASSWORD'];
			}
			else{
				throw new \Exception("授权信息未填写");	
			}
		}
	}
	
	
	public function getConfig()
	{
		//默认配置储存地址
		$savePath = SINA_LOGIN_ROOT.'wbcookie/config.inc';
		if(!file_exists($savePath)){
			return false;
		}else{
			$loginInfo = file_get_contents($savePath);
		}
		$config = json_decode($loginInfo, true);
		$this->su = $config['USERNAME'];
		$this->sp = $config['PASSWORD'];
		return true;
	}
	
	public function getSp()
	{
		return $this->sp;	
	}

	public function getSu()
	{
		return $this->su;	
	}
	
	
	/**
	 * 微博预登陆，获得登录所需的三个参数
	 *   'servertime'，'nonce'，'pubkey'
	 * 
	 */
	public function getPreUrl()
	{
		$preLoginUrl = sprintf($this->config['PreLoginUrl'], $this->su, dw_microtime());
		
		$preLogin = new WeiboLogin();
		$preInfo = $preLogin->loginPre($preLoginUrl);
		preg_match('/sinaSSOController.preloginCallBack\((.*)\)/', $preInfo, $preArr);
		$jsonArr = json_decode($preArr[1], true);
		
		if(($jsonArr['retcode'] != 0)){
			throw new \Exception("登陆失败");
			return false;
		}else{
// 			$this->preParam = [
// 					'servertime' => $jsonArr['servertime'],
// 					'nonce' => $jsonArr['nonce'],
// 					'pubkey' => $jsonArr['pubkey'],
// 					//登录使用参数
// 					'rsakv' => $jsonArr['rsakv'],
// 					'pcid' => $jsonArr['pcid'],
// 			];
// 			return $this->getEncodePwd();
			//采用临时文件存储方式存储预登陆所需文件
			$savePath = SINA_LOGIN_ROOT.'wbcookie/prelogin.config.inc';
			$bytes = file_put_contents($savePath, json_encode($jsonArr));
			return true;
		}
	}
	
	
	/**
	 * 访问本地地址 /admin/authorize/getRsaPwd 
	 * 获得加密后的sp
	 */
	private function getEncodePwd()
	{
		//同一控制器下函数
		$urlArr = pathinfo($_SERVER['REQUEST_URI']);
		$url = $urlArr['dirname']."/getRsaPwd/?";
		
		foreach ( $this->preParam as $k => $v ) {
			$url .= "$k=" . urlencode ( $v ) . "&";
		}
		
		//微博密码通过js加密，加密返回结果跳转传给登录页
		return $url;
	}
	
	
	public function getCookie($param)
	{
		//加载微博登录配置
		$this->loginData = $this->config['curl']; 
		//设置登陆用配置
		$this->loginData['su'] = $this->su;		
		$this->loginData['sp'] = $param['sp'];
		$this->loginData['servertime'] = $param['servertime'];
		$this->loginData['nonce'] = $param['nonce'];
		$this->loginData['rsakv'] = $param['rsakv'];
		$this->loginData['door'] = $param['door'];
		$this->loginData['pcid'] = $param['pcid'];
		
		$cookieSina =  SINA_LOGIN_ROOT."wbcookie/cookie_sina.txt";
		//微博cookie
		$cookieWeibo = SINA_LOGIN_ROOT."wbcookie/cookie_weibo.txt";
		$cookieGet =  SINA_LOGIN_ROOT."wbcookie/cookie_curl.txt";
		
		$wbLogin = new WeiboLogin();
		
		//登录新浪通行证
		$sinaLoginUrl = sprintf($this->config['SinaLoginUrl'], dw_microtime());
		$login_arr = json_decode($wbLogin->loginSina($sinaLoginUrl, $this->loginData, $cookieSina), true);

		if($login_arr['retcode'] != 0){
			throw new \Exception($login_arr['reason']);
			return false;
		}

		//获取微博cookie
		$data = $wbLogin->loginWeibo($login_arr['crossDomainUrlList'][0], $cookieSina, $cookieWeibo);
		
		if(empty($data)){
			throw new \Exception("未能获得微博cookie");
			return false;
		}
		
		//测试抓取
// 		$url = "http://weibo.com/1563926367/EcN8BcyME?type=comment#_rnd1477219631405";
// 		$content = $wbLogin->getWBHtml($url, $cookieWeibo, $cookieGet);
		return true;
	}
	
}
