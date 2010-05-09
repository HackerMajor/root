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

if (isset($_SERVER['DC_RC_PATH'])) {
	$rc_path = $_SERVER['DC_RC_PATH'];
} elseif (isset($_SERVER['REDIRECT_DC_RC_PATH'])) {
	$rc_path = $_SERVER['REDIRECT_DC_RC_PATH'];
} else {
	$rc_path = dirname(__FILE__).'/../../inc/config.php';
}

if (!is_file($rc_path)) {
	$summary = 'No configuration file';
	$message = sprintf('Configuration file does not exist. Please create one first. '.
		'You may use the <a href="%s">wizard</a>.','wizard.php');
	$code = 0;
	include dirname(__FILE__).'/../../inc/core_error.php';
	exit;
}

require dirname(__FILE__).'/../../inc/prepend.php';
require dirname(__FILE__).'/check.php';

$can_install = true;
$err = '';

# Loading locales for detected language
$dlang = http::getAcceptLanguage();
if ($dlang != 'en')
{
	l10n::init();
	l10n::set(dirname(__FILE__).'/../../locales/'.$dlang.'/main');
}

if (!defined('DC_MASTER_KEY') || DC_MASTER_KEY == '') {
	$can_install = false;
	$err = '<p>'.__('Please set a master key (DC_MASTER_KEY) in configuration file.').'</p>';
}

# Check if dotclear is already installed
$schema = dbSchema::init($core->con);
if (in_array($core->prefix.'post',$schema->getTables())) {
	$can_install = false;
	$err = '<p>'.__('Dotclear is already installed.').'</p>';
}

# Check system capabilites
if (!dcSystemCheck($core->con,$_e)) {
	$can_install = false;
	$err = '<p>'.__('Dotclear cannot be installed.').'</p><ul><li>'.implode('</li><li>',$_e).'</li></ul>';
}

# Get information and perform install
$u_email = $u_firstname = $u_name = $u_login = $u_pwd = '';
$mail_sent = false;
if ($can_install && !empty($_POST))
{
	$u_email = !empty($_POST['u_email']) ? $_POST['u_email'] : null;
	$u_firstname = !empty($_POST['u_firstname']) ? $_POST['u_firstname'] : null;
	$u_name = !empty($_POST['u_name']) ? $_POST['u_name'] : null;
	$u_login = !empty($_POST['u_login']) ? $_POST['u_login'] : null;
	$u_pwd = !empty($_POST['u_pwd']) ? $_POST['u_pwd'] : null;
	$u_pwd2 = !empty($_POST['u_pwd2']) ? $_POST['u_pwd2'] : null;
	
	try
	{
		# Check user information
		if (empty($u_login)) {
			throw new Exception(__('No user ID given'));
		}
		if (!preg_match('/^[A-Za-z0-9@._-]{2,}$/',$u_login)) {
			throw new Exception(__('User ID must contain at least 2 characters using letters, numbers or symbols.'));
		}
		if ($u_email && !text::isEmail($u_email)) {
			throw new Exception(__('Invalid email address'));
		}
		
		if (empty($u_pwd)) {
			throw new Exception(__('No password given'));
		}
		if ($u_pwd != $u_pwd2) {
			throw new Exception(__("Passwords don't match"));
		}
		if (strlen($u_pwd) < 6) {
			throw new Exception(__('Password must contain at least 6 characters.'));
		}
		
		# Try to guess timezone
		$default_tz = 'Europe/London';
		if (!empty($_POST['u_date']) && function_exists('timezone_open'))
		{
			if (preg_match('/\((.+)\)$/',$_POST['u_date'],$_tz)) {
				$_tz = $_tz[1];
				$_tz = @timezone_open($_tz);
				if ($_tz instanceof DateTimeZone) {
					$_tz = @timezone_name_get($_tz);
					if ($_tz) {
						$default_tz = $_tz;
					}
				}
				unset($_tz);
			}
		}
		
		# Create schema
		$_s = new dbStruct($core->con,$core->prefix);
		require dirname(__FILE__).'/../../inc/dbschema/db-schema.php';
		
		$si = new dbStruct($core->con,$core->prefix);
		$changes = $si->synchronize($_s);
		
		# Create user
		$cur = $core->con->openCursor($core->prefix.'user');
		$cur->user_id = $u_login;
		$cur->user_super = 1;
		$cur->user_pwd = crypt::hmac(DC_MASTER_KEY,$u_pwd);
		$cur->user_name = (string) $u_name;
		$cur->user_firstname = (string) $u_firstname;
		$cur->user_email = (string) $u_email;
		$cur->user_lang = $dlang;
		$cur->user_tz = $default_tz;
		$cur->user_creadt = array('NOW()');
		$cur->user_upddt = array('NOW()');
		$cur->user_options = serialize($core->userDefaults());
		$cur->insert();
		
		$core->auth->checkUser($u_login);
		
		$admin_url = preg_replace('%install/index.php$%','',$_SERVER['REQUEST_URI']);
		$root_url = preg_replace('%/admin/install/index.php$%','',$_SERVER['REQUEST_URI']);
		
		# Create blog
		$cur = $core->con->openCursor($core->prefix.'blog');
		$cur->blog_id = 'default';
		$cur->blog_url = http::getHost().$root_url.'/index.php?';
		$cur->blog_name = __('My first blog');
		$core->addBlog($cur);
		$core->blogDefaults($cur->blog_id);
		
		$blog_settings = new dcSettings($core,'default');
		$blog_settings->setNameSpace('system');
		$blog_settings->put('blog_timezone',$default_tz);
		$blog_settings->put('lang',$dlang);
		$blog_settings->put('public_url',$root_url.'/public');
		$blog_settings->put('themes_url',$root_url.'/themes');
		
		# Add Dotclear version
		$cur = $core->con->openCursor($core->prefix.'version');
		$cur->module = 'core';
		$cur->version = (string) DC_VERSION;
		$cur->insert();
		
		# Create first post
		$core->setBlog('default');
		
		$cur = $core->con->openCursor($core->prefix.'post');
		$cur->user_id = $u_login;
		$cur->post_format = 'xhtml';
		$cur->post_lang = $dlang;
		$cur->post_title = __('Welcome to Dotclear!');
		$cur->post_content = '<p>'.__('This is your first entry. When you\'re ready '.
			'to blog, log in to edit or delete it.').'</p>';
		$cur->post_content_xhtml = $cur->post_content;
		$cur->post_status = 1;
		$cur->post_open_comment = 1;
		$cur->post_open_tb = 0;
		$post_id = $core->blog->addPost($cur);
		
		# Add a comment to it
		$cur = $core->con->openCursor($core->prefix.'comment');
		$cur->post_id = $post_id;
		$cur->comment_tz = $default_tz;
		$cur->comment_author = __('Dotclear Team');
		$cur->comment_email = 'contact@dotclear.net';
		$cur->comment_site = 'http://www.dotclear.org/';
		$cur->comment_content = __("<p>This is a comment.</p>\n<p>To delete it, log in and ".
			"view your blog's comments. Then you might remove or edit it.</p>");
		$core->blog->addComment($cur);
		
		$step = 1;
	}
	catch (Exception $e)
	{
		$err = $e->getMessage();
	}
}

if (!isset($step)) {
	$step = 0;
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
  <title>Dotclear Install</title>
  
  <style type="text/css">
  @import url(../style/default.css); 
  </style>
  <script type="text/javascript" src="../js/jquery/jquery.js"></script>
  <script type="text/javascript">
  //<![CDATA[
  $(function() {
    var login_re = new RegExp('[^A-Za-z0-9@._-]+','g');
    $('#u_firstname').keyup(function() {
      var login = this.value.toLowerCase().replace(login_re,'').substring(0,32);
	 $('#u_login').val(login);
    });
    $('#u_login').keyup(function() {
      $(this).val(this.value.replace(login_re,''));
    });
    
    $('#u_login').parent().after($('<input type="hidden" name="u_date" value="' + Date().toLocaleString() + '" />'));
  });
  //]]>
  </script>
</head>

<body id="dotclear-admin" class="install">
<div id="content">
<?php
echo
'<h1>'.__('Dotclear installation').'</h1>';

if (!is_writable(DC_TPL_CACHE)) {
	echo '<div class="error"><p>'.sprintf(__('Cache directory %s is not writable.'),DC_TPL_CACHE).'</p></div>';
}

if (!empty($err)) {
	echo '<div class="error"><p><strong>'.__('Errors:').'</strong></p>'.$err.'</div>';
}

if (!empty($_GET['wiz'])) {
	echo '<p class="message">'.__('Configuration file has been successfully created.').'</p>';
}

if ($can_install && $step == 0)
{
	echo
	'<h2>'.__('User information').'</h2>'.
	
	'<p>'.__('Please provide the following information needed to create the first user.').'</p>'.
	
	'<form action="index.php" method="post">'.
	'<fieldset><legend>'.__('User information').'</legend>'.
	'<p><label>'.__('First Name:').' '.
	form::field('u_firstname',30,255,html::escapeHTML($u_firstname)).'</label></p>'.
	'<p><label>'.__('Last Name:').' '.
	form::field('u_name',30,255,html::escapeHTML($u_name)).'</label></p>'.
	'<p><label>'.__('Email:').' '.
	form::field('u_email',30,255,html::escapeHTML($u_email)).'</label></p>'.
	'</fieldset>'.
	
	'<fieldset><legend>'.__('Username and password').'</legend>'.
	'<p><label class="required" title="'.__('Required field').'">'.__('Username:').' '.
	form::field('u_login',30,32,html::escapeHTML($u_login)).'</label></p>'.
	'<p><label class="required" title="'.__('Required field').'">'.__('Password:').' '.
	form::password('u_pwd',30,255).'</label></p>'.
	'<p><label class="required" title="'.__('Required field').'">'.__('Confirm password:').' '.
	form::password('u_pwd2',30,255).'</label></p>'.
	'</fieldset>'.
	
	'<p><input type="submit" value="'.__('save').'" /></p>'.
	'</form>';
}
elseif ($can_install && $step == 1)
{
	echo
	'<h2>'.__('All done!').'</h2>'.
	
	'<p>'.__('Dotclear has been successfully installed. Here is some useful information you should keep.').'</p>'.
	
	'<h3>'.__('Your account').'</h3>'.
	'<ul>'.
	'<li>'.__('Username:').' <strong>'.html::escapeHTML($u_login).'</strong></li>'.
	'<li>'.__('Password:').' <strong>'.html::escapeHTML($u_pwd).'</strong></li>'.
	'</ul>'.
	
	'<h3>'.__('Your blog').'</h3>'.
	'<ul>'.
	'<li>'.__('Blog address:').' <strong>'.html::escapeHTML(http::getHost().$root_url).'/index.php?</strong></li>'.
	'<li>'.__('Administration interface:').' <strong>'.html::escapeHTML(http::getHost().$admin_url).'</strong></li>'.
	'</ul>'.
	
	'<form action="../auth.php" method="post">'.
	'<p><input type="submit" value="'.__('Manage your blog now').'" />'.
	form::hidden(array('user_id'),html::escapeHTML($u_login)).
	form::hidden(array('user_pwd'),html::escapeHTML($u_pwd)).
	'</p>'.
	'</form>';
}
?>
</div>
</body>
</html>