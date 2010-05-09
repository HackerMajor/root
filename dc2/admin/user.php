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

dcPage::checkSuper();

$page_title = __('new user');

$user_id = '';
$user_super = '';
$user_pwd = '';
$user_name = '';
$user_firstname = '';
$user_displayname = '';
$user_email = '';
$user_url = '';
$user_lang = $core->auth->getInfo('user_lang');
$user_tz = $core->auth->getInfo('user_tz');
$user_post_status = '';

$user_options = $core->userDefaults();

foreach ($core->getFormaters() as $v) {
	$formaters_combo[$v] = $v;
}

foreach ($core->blog->getAllPostStatus() as $k => $v) {
	$status_combo[$v] = $k;
}

# Language codes
$langs = l10n::getISOcodes(1,1);
foreach ($langs as $k => $v) {
	$lang_avail = $v == 'en' || is_dir(DC_L10N_ROOT.'/'.$v);
	$lang_combo[] = new formSelectOption($k,$v,$lang_avail ? 'avail10n' : '');
}

# Get user if we have an ID
if (!empty($_REQUEST['id']))
{
	try {
		$rs = $core->getUser($_REQUEST['id']);
		
		$user_id = $rs->user_id;
		$user_super = $rs->user_super;
		$user_pwd = $rs->user_pwd;
		$user_name = $rs->user_name;
		$user_firstname = $rs->user_firstname;
		$user_displayname = $rs->user_displayname;
		$user_email = $rs->user_email;
		$user_url = $rs->user_url;
		$user_lang = $rs->user_lang;
		$user_tz = $rs->user_tz;
		$user_post_status = $rs->user_post_status;
		
		$user_options = array_merge($user_options,$rs->options());
		
		$page_title = $user_id;
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

# Add or update user
if (isset($_POST['user_name']))
{
	try
	{
		if (empty($_POST['your_pwd']) || !$core->auth->checkPassword(crypt::hmac(DC_MASTER_KEY,$_POST['your_pwd']))) {
			throw new Exception(__('Password verification failed'));
		}
		
		$cur = $core->con->openCursor($core->prefix.'user');
		
		$cur->user_id = $_POST['user_id'];
		$cur->user_super = $user_super = !empty($_POST['user_super']) ? 1 : 0;
		$cur->user_name = $user_name = $_POST['user_name'];
		$cur->user_firstname = $user_firstname = $_POST['user_firstname'];
		$cur->user_displayname = $user_displayname = $_POST['user_displayname'];
		$cur->user_email = $user_email = $_POST['user_email'];
		$cur->user_url = $user_url = $_POST['user_url'];
		$cur->user_lang = $user_lang = $_POST['user_lang'];
		$cur->user_tz = $user_tz = $_POST['user_tz'];
		$cur->user_post_status = $user_post_status = $_POST['user_post_status'];
		
		if (!empty($_POST['new_pwd'])) {
			if ($_POST['new_pwd'] != $_POST['new_pwd_c']) {
				throw new Exception(__("Passwords don't match"));
			} else {
				$cur->user_pwd = $_POST['new_pwd'];
			}
		}
		
		$user_options['post_format'] = $_POST['user_post_format'];
		$user_options['edit_size'] = (integer) $_POST['user_edit_size'];
		
		if ($user_options['edit_size'] < 1) {
			$user_options['edit_size'] = 10;
		}
		
		$cur->user_options = new ArrayObject($user_options);
		
		# Udate user
		if ($user_id)
		{
			# --BEHAVIOR-- adminBeforeUserUpdate
			$core->callBehavior('adminBeforeUserUpdate',$cur,$user_id);
			
			$new_id = $core->updUser($user_id,$cur);
			
			# --BEHAVIOR-- adminAfterUserUpdate
			$core->callBehavior('adminAfterUserUpdate',$cur,$new_id);
			
			if ($user_id == $core->auth->userID() &&
			$user_id != $new_id) {
				$core->session->destroy();
			}
			
			http::redirect('user.php?id='.$new_id.'&upd=1');
		}
		# Add user
		else
		{
			if ($core->getUsers(array('user_id' => $cur->user_id),true)->f(0) > 0) {
				throw new Exception(sprintf(__('User "%s" already exists.'),html::escapeHTML($cur->user_id)));
			}
			
			# --BEHAVIOR-- adminBeforeUserCreate
			$core->callBehavior('adminBeforeUserCreate',$cur);
			
			$new_id = $core->addUser($cur);
			
			# --BEHAVIOR-- adminAfterUserCreate
			$core->callBehavior('adminAfterUserCreate',$cur,$new_id);
			
			http::redirect('user.php?id='.$new_id.'&add=1');
		}
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}


/* DISPLAY
-------------------------------------------------------- */
dcPage::open($page_title,
	dcPage::jsConfirmClose('user-form').
	
	# --BEHAVIOR-- adminUserHeaders
	$core->callBehavior('adminUserHeaders')
);

if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('User has been successfully updated.').'</p>';
}

if (!empty($_GET['add'])) {
		echo '<p class="message">'.__('User has been successfully created.').'</p>';
}

echo '<h2><a href="users.php">'.__('Users').'</a> &rsaquo; '.$page_title.'</h2>';

if ($user_id == $core->auth->userID()) {
	echo
	'<p class="warning">'.__('Warning:').' '.
	__('If you change your username, you will have to log in again.').'</p>';
}

echo
'<form action="user.php" method="post"id="user-form">'.
'<fieldset><legend>'.__('User information').'</legend>'.
'<div class="two-cols">'.
'<div class="col">'.
'<p><label class="required" title="'.__('Required field').'">'.__('Username:').' '.
form::field('user_id',20,255,html::escapeHTML($user_id),'',2).
'</label></p>'.
'<p class="form-note">'.__('At least 2 characters using letters, numbers or symbols.').'</p>'.

'<p><label>'.($user_id!='' ? __('New password:') : __('Password:')).' '.
form::password('new_pwd',20,255,'','',3).
'</label></p>'.

'<p><label>'.__('Confirm password:').' '.
form::password('new_pwd_c',20,255,'','',4).
'</label></p>'.

'<p><label>'.__('Last Name:').' '.
form::field('user_name',20,255,html::escapeHTML($user_name),'',5).
'</label></p>'.

'<p><label>'.__('First Name:').' '.
form::field('user_firstname',20,255,html::escapeHTML($user_firstname),'',6).
'</label></p>'.

'<p><label>'.__('Display name:').' '.
form::field('user_displayname',20,255,html::escapeHTML($user_displayname),'',7).
'</label></p>'.

'<p><label>'.__('Email:').' '.
form::field('user_email',20,255,html::escapeHTML($user_email),'',8).
'</label></p>'.
'</div>'.

'<div class="col">'.
'<p><label>'.__('URL:').' '.
form::field('user_url',30,255,html::escapeHTML($user_url),'',8).
'</label></p>'.
'<p><label>'.__('Preferred format:').' '.
form::combo('user_post_format',$formaters_combo,$user_options['post_format'],'',9).
'</label></p>'.

'<p><label>'.__('Default entry status:').' '.
form::combo('user_post_status',$status_combo,$user_post_status,'',10).
'</label></p>'.

'<p><label>'.__('Entry edit field height:').' '.
form::field('user_edit_size',5,4,(integer) $user_options['edit_size'],'',11).
'</label></p>'.

'<p><label>'.__('User language:').' '.
form::combo('user_lang',$lang_combo,$user_lang,'l10n',12).
'</label></p>'.

'<p><label>'.__('User timezone:').' '.
form::combo('user_tz',dt::getZones(true,true),$user_tz,'',13).
'</label></p>'.

'<p><label class="classic">'.form::checkbox('user_super','1',$user_super,'',14).' '.
__('Super administrator').'</label></p>'.
'</div>'.
'</div>'.
'</fieldset>';

# --BEHAVIOR-- adminUserForm
$core->callBehavior('adminUserForm',isset($rs) ? $rs : null);

echo
'<fieldset>'.
'<p><label>'.__('Your password:').
form::password('your_pwd',20,255,'','',15).'</label></p>'.
'</fieldset>'.
'<p class="clear"><input type="submit" accesskey="s" value="'.__('Save').'" tabindex="16" />'.
($user_id != '' ? form::hidden('id',$user_id) : '').
$core->formNonce().
'</p>'.

'</form>';

if ($user_id)
{
	echo '<fieldset class="clear"><legend>'.__('Permissions').'</legend>';
	
	$permissions = $core->getUserPermissions($user_id);
	$perm_types = $core->auth->getPermissionsTypes();
	
	if (count($permissions) == 0)
	{
		echo '<p>'.__('No permissions.').'</p>';
	}
	else
	{
		foreach ($permissions as $k => $v)
		{
			if (count($v['p']) > 0)
			{
				echo '<h4><a href="blog.php?id='.html::escapeHTML($k).'">'.
				html::escapeHTML($v['name']).'</a> ('.html::escapeHTML($k).') - '.
				'<a href="permissions.php?blog_id[]='.$k.'&amp;user_id[]='.$user_id.'">'
				.__('change permissions').'</a></h4>';
				
				echo '<ul>';
				foreach ($v['p'] as $p => $V) {
					if (isset($perm_types[$p])) {
						echo '<li>'.__($perm_types[$p]).'</li>';
					}
				}
				echo '</ul>';
			}
		}
	}
	
	echo
	'<p><a href="permissions_blog.php?user_id[]='.$user_id.'">'.
	__('Add new permissions').'</a></p>'.
	'</fieldset>';
}

dcPage::close();
?>