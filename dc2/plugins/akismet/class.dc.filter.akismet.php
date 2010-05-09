<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Antispam, a plugin for Dotclear 2.
#
# Copyright (c) 2003-2009 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_RC_PATH')) { return; }

class dcFilterAkismet extends dcSpamFilter
{
	public $name = 'Akismet';
	public $has_gui = true;
	public $active = false;
	
	public function __construct(&$core)
	{
		parent::__construct($core);
		
		if (defined('DC_AKISMET_SUPER') && DC_AKISMET_SUPER && !$core->auth->isSuperAdmin()) {
			$this->has_gui = false;
		}
	}
	
	protected function setInfo()
	{
		$this->description = __('Akismet spam filter');
	}
	
	public function getStatusMessage($status,$comment_id)
	{
		return sprintf(__('Filtered by %s.'),$this->guiLink());
	}
	
	private function akInit()
	{
		$blog =& $this->core->blog;
		
		if (!$blog->settings->ak_key) {
			return false;
		}
		
		return new akismet($blog->url,$blog->settings->ak_key);
	}
	
	public function isSpam($type,$author,$email,$site,$ip,$content,$post_id,&$status)
	{
		if (($ak = $this->akInit()) === false) {
			return;
		}
		
		$blog =& $this->core->blog;
		
		try
		{
			if ($ak->verify())
			{
				$post = $blog->getPosts(array('post_id' => $post_id));
				
				$c = $ak->comment_check(
					$post->getURL(),
					$type,
					$author,
					$email,
					$site,
					$content
				);
				
				if ($c) {
					$status = 'Filtered by Akismet';
					return true;
				}
			}
		} catch (Exception $e) {} # If http or akismet is dead, we don't need to know it
	}
	
	public function trainFilter($status,$filter,$type,$author,$email,$site,$ip,$content,$rs)
	{
		# We handle only false positive from akismet
		if ($status == 'spam' && $filter != 'dcFilterAkismet')
		{
			return;
		}
		
		$f = $status == 'spam' ? 'submit_spam' : 'submit_ham';
		
		if (($ak = $this->akInit()) === false) {
			return;
		}
		
		try
		{
			if ($ak->verify()) {
				$ak->{$f}($rs->getPostURL(),$type,$author,$email,$site,$content);
			}
		} catch (Exception $e) {} # If http or akismet is dead, we don't need to know it
	}
	
	public function gui($url)
	{
		$blog =& $this->core->blog;
		
		$ak_key = $blog->settings->ak_key;
		$ak_verified = null;
		
		if (isset($_POST['ak_key']))
		{
			try
			{
				$ak_key = $_POST['ak_key'];
				
				$blog->settings->setNameSpace('akismet');
				$blog->settings->put('ak_key',$ak_key,'string');
				
				http::redirect($url.'&up=1');
			}
			catch (Exception $e)
			{
				$this->core->error->add($e->getMessage());
			}
		}
		
		if ($blog->settings->ak_key)
		{
			try {
				$ak = new akismet($blog->url,$blog->settings->ak_key);
				$ak_verified = $ak->verify();
			} catch (Exception $e) {
				$this->core->error->add($e->getMessage());
			}
		}
		
		$res =
		'<form action="'.html::escapeURL($url).'" method="post">'.
		'<p><label class="classic">'.__('Akismet API key:').' '.
		form::field('ak_key',12,128,$ak_key).'</label>';
		
		if ($ak_verified !== null) {
			if ($ak_verified) {
				$res .= ' <img src="images/check-on.png" alt="" /> '.__('API key verified');
			} else {
				$res .= ' <img src="images/check-off.png" alt="" /> '.__('API key not verified');
			}
		}
		
		$res .= '</p>';
		
		$res .=
		'<p><a href="http://wordpress.com/api-keys/">'.__('Get your own API key').'</a></p>'.
		'<p><input type="submit" value="'.__('save').'" />'.
		$this->core->formNonce().'</p>'.
		'</form>';
		
		return $res;
	}
}

class akismet extends netHttp
{
	protected $base_host = 'rest.akismet.com';
	protected $ak_host = '';
	protected $ak_version = '1.1';
	protected $ak_path = '/%s/%s';
	
	protected $ak_key = null;
	protected $blog_url;
	
	protected $timeout = 3;
	
	public function __construct($blog_url,$api_key)
	{
		$this->blog_url = $blog_url;
		$this->ak_key = $api_key;
		
		$this->ak_path = sprintf($this->ak_path,$this->ak_version,'%s');
		$this->ak_host = $this->ak_key.'.'.$this->base_host;
		
		parent::__construct($this->ak_host,80);
	}
	
	public function verify()
	{
		$this->host = $this->base_host;
		$path = sprintf($this->ak_path,'verify-key');
		
		$data = array(
			'key' => $this->ak_key,
			'blog' => $this->blog_url
		);
		
		if ($this->post($path,$data,'UTF-8'))
		{
			return $this->getContent() == 'valid';
		}
		
		return false;
	}
	
	public function comment_check($permalink,$type,$author,$email,$url,$content)
	{
		$info_ignore = array('HTTP_COOKIE');
		$info = array();
		
		foreach ($_SERVER as $k => $v) {
			if (strpos($k,'HTTP_') === 0 && !in_array($k,$info_ignore)) {
				$info[$k] = $v;
			}
		}
		
		return $this->callFunc('comment-check',$permalink,$type,$author,$email,$url,$content,$info);
	}
	
	public function submit_spam($permalink,$type,$author,$email,$url,$content)
	{
		$this->callFunc('submit-spam',$permalink,$type,$author,$email,$url,$content);
		return true;
	}
	
	public function submit_ham($permalink,$type,$author,$email,$url,$content)
	{
		$this->callFunc('submit-ham',$permalink,$type,$author,$email,$url,$content);
		return true;
	}
	
	protected function callFunc($function,$permalink,$type,$author,$email,$url,$content,$info=array())
	{
		$ua = isset($info['HTTP_USER_AGENT']) ? $info['HTTP_USER_AGENT'] : '';
		$referer = isset($info['HTTP_REFERER']) ? $info['HTTP_REFERER'] : '';
		
		# Prepare comment data
		$data = array(
			'blog' => $this->blog_url,
			'user_ip' => http::realIP(),
			'user_agent' => $ua,
			'referrer' => $referer,
			'permalink' => $permalink,
			'comment_type' => $type,
			'comment_author' => $author,
			'comment_author_email' => $email,
			'comment_author_url' => $url,
			'comment_content' => $content
		);
		
		$data = array_merge($data,$info);
		
		$this->host = $this->ak_host;
		$path = sprintf($this->ak_path,$function);
		
		if (!$this->post($path,$data,'UTF-8')) {
			throw new Exception('HTTP error: '.$this->getError());
		}
		
		return $this->getContent() == 'true';
	}
}
?>