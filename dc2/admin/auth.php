<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Dotclear 2.
#
# Copyright (c) 2003-2009 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------

require dirname(__FILE__).'/../inc/admin/prepend.php';

# If we have a session cookie, go to index.php
if (isset($_SESSION['sess_user_id']))
{
	http::redirect('index.php');
}

# Loading locales for detected language
# That's a tricky hack but it works ;)
$dlang = http::getAcceptLanguage();
if ($dlang != 'en')
{
	l10n::set(dirname(__FILE__).'/../locales/'.$dlang.'/main');
}

$page_url = http::getHost().$_SERVER['REQUEST_URI'];

$recover = $core->auth->allowPassChange() && !empty($_REQUEST['recover']);
$akey = $core->auth->allowPassChange() && !empty($_GET['akey']) ? $_GET['akey'] : null;
$user_id = $user_pwd = $user_key = $user_email = null;
$err = $msg = null;

# Auto upgrade
if (empty($_GET) && empty($_POST)) {
	require dirname(__FILE__).'/../inc/dbschema/upgrade.php';
	try {
		if (($changes = dotclearUpgrade($core)) !== false) {
			$msg = __('Dotclear has been upgraded.').'<!-- '.$changes.' -->';
		}
	} catch (Exception $e) {
		$err = $e->getMessage();
	}
}

# If we have POST login informations, go throug auth process
if (!empty($_POST['user_id']) && !empty($_POST['user_pwd']))
{
	$user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;
	$user_pwd = !empty($_POST['user_pwd']) ? $_POST['user_pwd'] : null;
}
# If we have POST login informations, go throug auth process
elseif (isset($_COOKIE['dc_admin']) && strlen($_COOKIE['dc_admin']) == 104)
{
	# If we have a remember cookie, go through auth process with user_key
	$user_id = substr($_COOKIE['dc_admin'],40);
	$user_id = @unpack('a32',@pack('H*',$user_id));
	if (is_array($user_id))
	{
		$user_id = $user_id[1];
		$user_key = substr($_COOKIE['dc_admin'],0,40);
		$user_pwd = null;
	}
	else
	{
		$user_id = null;
	}
}

# Recover password
if ($recover && !empty($_POST['user_id']) && !empty($_POST['user_email']))
{
	$user_id = !empty($_POST['user_id']) ? $_POST['user_id'] : null;
	$user_email = !empty($_POST['user_email']) ? $_POST['user_email'] : '';
	try
	{
		$recover_key = $core->auth->setRecoverKey($user_id,$user_email);
		
		$subject = mail::B64Header('DotClear '.__('Password reset'));
		$message =
		__('Someone has requested to reset the password for the following site and username.')."\n\n".
		$page_url."\n".__('Username:').' '.$user_id."\n\n".
		__('To reset your password visit the following address, otherwise just ignore this email and nothing will happen.')."\n".
		$page_url.'?akey='.$recover_key;
		
		$headers[] = 'From: dotclear@'.$_SERVER['HTTP_HOST'];
		$headers[] = 'Content-Type: text/plain; charset=UTF-8;';
		
		mail::sendMail($user_email,$subject,$message,$headers);
		$msg = sprintf(__('The e-mail was sent successfully to %s.'),$user_email);
	}
	catch (Exception $e)
	{
		$err = $e->getMessage();
	}
}
# Send new password
elseif ($akey)
{
	try
	{
		$recover_res = $core->auth->recoverUserPassword($akey);
		
		$subject = mb_encode_mimeheader('DotClear '.__('Your new password'),'UTF-8','B');
		$message =
		__('Username:').' '.$recover_res['user_id']."\n".
		__('Password:').' '.$recover_res['new_pass']."\n\n".
		preg_replace('/\?(.*)$/','',$page_url);
		
		$headers[] = 'From: dotclear@'.$_SERVER['HTTP_HOST'];
		$headers[] = 'Content-Type: text/plain; charset=UTF-8;';
		
		mail::sendMail($recover_res['user_email'],$subject,$message,$headers);
		$msg = __('Your new password is in your mailbox.');
	}
	catch (Exception $e)
	{
		$err = $e->getMessage();
	}
}
# Try to log
elseif ($user_id !== null && ($user_pwd !== null || $user_key !== null))
{
	# We check the user
	if ($core->auth->checkUser($user_id,$user_pwd,$user_key) === true)
	{
		$core->session->start();
		$_SESSION['sess_user_id'] = $user_id;
		$_SESSION['sess_browser_uid'] = http::browserUID(DC_MASTER_KEY);
		
		if (!empty($_POST['blog'])) {
			$_SESSION['sess_blog_id'] = $_POST['blog'];
		}
		
		if (!empty($_POST['user_remember']))
		{
			$cookie_admin =
				http::browserUID(DC_MASTER_KEY.$user_id.crypt::hmac(DC_MASTER_KEY,$user_pwd)).
				bin2hex(pack('a32',$user_id));
				
			setcookie('dc_admin',$cookie_admin,strtotime('+15 days'),'','',DC_ADMIN_SSL);
		}
		
		http::redirect('index.php');
	}
	else
	{
		if (isset($_COOKIE['dc_admin'])) {
			unset($_COOKIE['dc_admin']);
			setcookie('dc_admin',false,-600,'','',DC_ADMIN_SSL);
		}
		$err = __('Wrong username or password');
	}
}

if (isset($_GET['user'])) {
	$user_id = $_GET['user'];
}

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"
xml:lang="en" lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="Content-Script-Type" content="text/javascript" />
  <meta http-equiv="Content-Style-Type" content="text/css" />
  <meta http-equiv="Content-Language" content="en" />
  <meta name="MSSmartTagsPreventParsing" content="TRUE" />
  <meta name="ROBOTS" content="NOARCHIVE,NOINDEX,NOFOLLOW" />
  <meta name="GOOGLEBOT" content="NOSNIPPET" />
  <title><?php echo html::escapeHTML(DC_VENDOR_NAME); ?></title>
  
<?php
echo dcPage::jsLoadIE7();
echo dcPage::jsCommon();
?>
  
  <style type="text/css">
  @import url(style/default.css); 
  </style>
  <?php
  # --BEHAVIOR-- loginPageHTMLHead
  $core->callBehavior('loginPageHTMLHead');
  ?>
  
  <script type="text/javascript">
  //<![CDATA[
  $(window).load(function() {
    var uid = $('input[name=user_id]');
    var upw = $('input[name=user_pwd]');
    uid.focus();
    
    if (upw.length == 0) { return; }
    
    if ($.browser.mozilla) {
      uid.keypress(processKey);
    } else {
      uid.keydown(processKey);
    }
    function processKey(evt) {
      if (evt.keyCode == 13 && upw.val() == '') {
         upw.focus();
	    return false;
      }
	 return true;
    };
  });
  //]]>
  </script>
</head>

<body id="dotclear-admin" class="auth">

<form action="auth.php" method="post" id="login-screen">
<h1><?php echo html::escapeHTML(DC_VENDOR_NAME); ?></h1>

<?php
if ($err) {
	echo '<div class="error">'.$err.'</div>';
}
if ($msg) {
	echo '<p class="message">'.$msg.'</p>';
}

if ($akey)
{
	echo '<p><a href="auth.php">'.__('Back to login screen').'</a></p>';
}
elseif ($recover)
{
	echo
	'<fieldset><legend>'.__('Request a new password').'</legend>'.
	'<p><label>'.__('Username:').' '.
	form::field(array('user_id'),20,32,html::escapeHTML($user_id),'',1).'</label></p>'.
	
	'<p><label>'.__('Email:').' '.
	form::field(array('user_email'),20,255,html::escapeHTML($user_email),'',2).'</label></p>'.
	
	'<p><input type="submit" value="'.__('recover').'" tabindex="3" />'.
	form::hidden(array('recover'),1).'</p>'.
	'</fieldset>'.
	
	'<p><a href="auth.php">'.__('Back to login screen').'</a></p>';
}
else
{
	if (is_callable(array($core->auth,'authForm')))
	{
		echo $core->auth->authForm($user_id);
	}
	else
	{
		echo
		'<fieldset>'.
		'<p><label>'.__('Username:').' '.
		form::field(array('user_id'),20,32,html::escapeHTML($user_id),'',1).'</label></p>'.
		
		'<p><label>'.__('Password:').' '.
		form::password(array('user_pwd'),20,255,'','',2).'</label></p>'.
		
		'<p><label class="classic">'.
		form::checkbox(array('user_remember'),1,'','',3).' '.
		__('Remember my ID on this computer').'</label></p>'.
		
		'<p><input type="submit" value="'.__('login').'" tabindex="4" /></p>';
		
		if (!empty($_REQUEST['blog'])) {
			echo form::hidden('blog',html::escapeHTML($_REQUEST['blog']));
		}
		
		echo
		'</fieldset>'.
		
		'<p>'.__('You must accept cookies in order to use the private area.').'</p>';
		
		if ($core->auth->allowPassChange()) {
			echo '<p><a href="auth.php?recover=1">'.__('I forgot my password').'</a></p>';
		}
	}
}
?>
</form>
</body>
</html>