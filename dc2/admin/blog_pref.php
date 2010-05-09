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

$standalone = !isset($edit_blog_mode);

$blog_id = false;

if ($standalone)
{
	require dirname(__FILE__).'/../inc/admin/prepend.php';
	dcPage::check('admin');
	$blog_id = $core->blog->id;
	$blog_url = $core->blog->url;
	$blog_status = $core->blog->status;
	$blog_name = $core->blog->name;
	$blog_desc = $core->blog->desc;
	$blog_settings = $core->blog->settings;
	
	$action = 'blog_pref.php';
	$redir = 'blog_pref.php?upd=1';
}
else
{
	dcPage::checkSuper();
	try
	{
		if (empty($_REQUEST['id'])) {
			throw new Exception(__('No given blog id.'));
		}
		$rs = $core->getBlog($_REQUEST['id']);
		
		if (!$rs) {
			throw new Exception(__('No such blog.'));
		}
		
		$blog_id = $rs->blog_id;
		$blog_url = $rs->blog_url;
		$blog_status = $rs->blog_status;
		$blog_name = $rs->blog_name;
		$blog_desc = $rs->blog_desc;
		$blog_settings = new dcSettings($core,$blog_id);
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
	
	$action = 'blog.php';
	$redir = 'blog.php?id=%s&upd=1';
}

# Language codes
$langs = l10n::getISOcodes(1,1);
foreach ($langs as $k => $v) {
	$lang_avail = $v == 'en' || is_dir(DC_L10N_ROOT.'/'.$v);
	$lang_combo[] = new formSelectOption($k,$v,$lang_avail ? 'avail10n' : '');
}

# Status combo
foreach ($core->getAllBlogStatus() as $k => $v) {
	$status_combo[$v] = (string) $k;
}

# URL scan modes
$url_scan_combo = array(
	'PATH_INFO' => 'path_info',
	'QUERY_STRING' => 'query_string'
);

# Post URL combo
$post_url_combo = array(
	__('year/month/day/title') => '{y}/{m}/{d}/{t}',
	__('year/month/title') => '{y}/{m}/{t}',
	__('year/title') => '{y}/{t}',
	__('title') => '{t}'
);
if (!in_array($blog_settings->post_url_format,$post_url_combo)) {
	$post_url_combo[html::escapeHTML($blog_settings->post_url_format)] = html::escapeHTML($blog_settings->post_url_format);
}

# Image title combo
$img_title_combo = array(
	__('Title') => 'Title ;; separator(, )',
	__('Title, Date') => 'Title ;; Date(%b %Y) ;; separator(, )',
	__('Title, Country, Date') => 'Title ;; Country ;; Date(%b %Y) ;; separator(, )',
	__('Title, City, Country, Date') => 'Title ;; City ;; Country ;; Date(%b %Y) ;; separator(, )',
);
if (!in_array($blog_settings->media_img_title_pattern,$img_title_combo)) {
	$img_title_combo[html::escapeHTML($blog_settings->media_img_title_pattern)] = html::escapeHTML($blog_settings->media_img_title_pattern);
}

# Robots policy options
$robots_policy_options = array(
	'INDEX,FOLLOW' => __("I would like search engines and archivers to index and archive my blog's content."),
	'INDEX,FOLLOW,NOARCHIVE' => __("I would like search engines and archivers to index but not archive my blog's content."),
	'NOINDEX,NOFOLLOW,NOARCHIVE' => __("I would like to prevent search engines and archivers from indexing or archiving my blog's content."),
);

# Update a blog
if ($blog_id && !empty($_POST) && $core->auth->check('admin',$blog_id))
{
	$cur = $core->con->openCursor($core->prefix.'blog');
	if ($core->auth->isSuperAdmin()) {
		$cur->blog_id = $_POST['blog_id'];
		$cur->blog_url = $_POST['blog_url'];
		if (in_array($_POST['blog_status'],$status_combo)) {
			$cur->blog_status = (integer) $_POST['blog_status'];
		}
	}
	$cur->blog_name = $_POST['blog_name'];
	$cur->blog_desc = $_POST['blog_desc'];
	
	$media_img_t_size = abs((integer) $_POST['media_img_t_size']);
	if ($media_img_t_size < 0) { $media_img_t_size = 100; }
	
	$media_img_s_size = abs((integer) $_POST['media_img_s_size']);
	if ($media_img_s_size < 0) { $media_img_s_size = 240; }
	
	$media_img_m_size = abs((integer) $_POST['media_img_m_size']);
	if ($media_img_m_size < 0) { $media_img_m_size = 448; }
	
	$nb_post_per_page = abs((integer) $_POST['nb_post_per_page']);
	if ($nb_post_per_page <= 1) { $nb_post_per_page = 1; }
	
	$nb_post_per_feed = abs((integer) $_POST['nb_post_per_feed']);
	if ($nb_post_per_feed <= 1) { $nb_post_per_feed = 1; }
	
	$nb_comment_per_feed = abs((integer) $_POST['nb_comment_per_feed']);
	if ($nb_comment_per_feed <= 1) { $nb_comment_per_feed = 1; }
	
	try
	{
		# --BEHAVIOR-- adminBeforeBlogUpdate
		$core->callBehavior('adminBeforeBlogUpdate',$cur,$blog_id);
		
		if (!preg_match('/^[a-z]{2}(-[a-z]{2})?$/',$_POST['lang'])) {
			throw new Exception(__('Invalid language code'));
		}
		
		$core->updBlog($blog_id,$cur);
		
		# --BEHAVIOR-- adminAfterBlogUpdate
		$core->callBehavior('adminAfterBlogUpdate',$cur,$blog_id);
		
		if ($blog_id == $core->blog->id && $cur->blog_id != null && $cur->blog_id != $blog_id) {
			$blog_id = $cur->blog_id;
			$core->setBlog($cur->blog_id);
			$_SESSION['sess_blog_id'] = $cur->blog_id;
			$blog_settings = $core->blog->settings;
		}
		
		
		$blog_settings->setNameSpace('system');
		
		$blog_settings->put('editor',$_POST['editor']);
		$blog_settings->put('copyright_notice',$_POST['copyright_notice']);
		$blog_settings->put('post_url_format',$_POST['post_url_format']);
		$blog_settings->put('lang',$_POST['lang']);
		$blog_settings->put('blog_timezone',$_POST['blog_timezone']);
		$blog_settings->put('date_format',$_POST['date_format']);
		$blog_settings->put('time_format',$_POST['time_format']);
		$blog_settings->put('comments_ttl',abs((integer) $_POST['comments_ttl']));
		$blog_settings->put('trackbacks_ttl',abs((integer) $_POST['trackbacks_ttl']));
		$blog_settings->put('allow_comments',!empty($_POST['allow_comments']));
		$blog_settings->put('allow_trackbacks',!empty($_POST['allow_trackbacks']));
		$blog_settings->put('comments_pub',empty($_POST['comments_pub']));
		$blog_settings->put('trackbacks_pub',empty($_POST['trackbacks_pub']));
		$blog_settings->put('comments_nofollow',!empty($_POST['comments_nofollow']));
		$blog_settings->put('wiki_comments',!empty($_POST['wiki_comments']));
		$blog_settings->put('enable_xmlrpc',!empty($_POST['enable_xmlrpc']));
		
		$blog_settings->put('nb_post_per_page',$nb_post_per_page);
		$blog_settings->put('use_smilies',!empty($_POST['use_smilies']));
		$blog_settings->put('media_img_t_size',$media_img_t_size);
		$blog_settings->put('media_img_s_size',$media_img_s_size);
		$blog_settings->put('media_img_m_size',$media_img_m_size);
		$blog_settings->put('media_img_title_pattern',$_POST['media_img_title_pattern']);
		$blog_settings->put('nb_post_per_feed',$nb_post_per_feed);
		$blog_settings->put('nb_comment_per_feed',$nb_comment_per_feed);
		$blog_settings->put('short_feed_items',!empty($_POST['short_feed_items']));
		
		if (isset($_POST['robots_policy'])) {
			$blog_settings->put('robots_policy',$_POST['robots_policy']);
		}
		
		# --BEHAVIOR-- adminBeforeBlogSettingsUpdate
		$core->callBehavior('adminBeforeBlogSettingsUpdate',$blog_settings);
		
		if ($core->auth->isSuperAdmin() && in_array($_POST['url_scan'],$url_scan_combo)) {
			$blog_settings->put('url_scan',$_POST['url_scan']);
		}
		
		http::redirect(sprintf($redir,$blog_id));
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}

dcPage::open(__('Blog settings'),
	dcPage::jsConfirmClose('blog-form').
	
	# --BEHAVIOR-- adminBlogPreferencesHeaders
	$core->callBehavior('adminBlogPreferencesHeaders').
	
	dcPage::jsPageTabs()
);

if ($blog_id)
{
	echo '<h2>'.(!$standalone ? '<a href="blogs.php">'.__('Blogs').'</a> &rsaquo; ' : '').
	html::escapeHTML($blog_name).' &rsaquo; '.
	__('Blog settings').'</h2>';
	
	if (!empty($_GET['add'])) {
		echo '<p class="message">'.__('Blog has been successfully created.').'</p>';
	}
	
	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Blog has been successfully updated.').'</p>';
	}
	
	echo
	'<div class="multi-part" id="params" title="'.__('Parameters').'">'.
	'<h3>'.__('Parameters').'</h3>'.
	'<form action="'.$action.'" method="post" id="blog-form">';

	echo
	'<fieldset><legend>'.__('Blog details').'</legend>'.
	$core->formNonce();
	
	if ($core->auth->isSuperAdmin())
	{
		echo
		'<p><label class="required" title="'.__('Required field').'">'.__('Blog ID:').
		form::field('blog_id',30,32,html::escapeHTML($blog_id)).'</label></p>'.
		'<p class="form-note">'.__('At least 2 characters using letters, numbers or symbols.').' '.
		__('Please note that changing your blog ID may require changes in your public index.php file.').'</p>';
	}
	
	echo
	'<p><label class="required" title="'.__('Required field').'">'.__('Blog name:').
	form::field('blog_name',30,255,html::escapeHTML($blog_name)).'</label></p>';
	
	if ($core->auth->isSuperAdmin())
	{
		echo
		'<p><label class="required" title="'.__('Required field').'">'.__('Blog URL:').
		form::field('blog_url',30,255,html::escapeHTML($blog_url)).'</label></p>'.
		
		'<p><label>'.__('URL scan method:').
		form::combo('url_scan',$url_scan_combo,$blog_settings->url_scan).'</label></p>'.
		
		'<p><label>'.__('Blog status:').
		form::combo('blog_status',$status_combo,$blog_status).'</label></p>';
	}
	
	echo
	'<p class="area"><label for="blog_desc">'.__('Blog description:').'</label>'.
	form::textarea('blog_desc',60,5,html::escapeHTML($blog_desc)).'</p>'.
	'</fieldset>';


	echo
	'<fieldset><legend>'.__('Blog configuration').'</legend>'.
	'<div class="two-cols">'.
	'<div class="col">'.
	'<p><label>'.__('Blog editor name:').
	form::field('editor',30,255,html::escapeHTML($blog_settings->editor)).
	'</label></p>'.
	
	'<p><label>'.__('Default language:').
	form::combo('lang',$lang_combo,$blog_settings->lang,'l10n').
	'</label></p>'.
	
	'<p><label>'.__('Blog timezone:').
	form::combo('blog_timezone',dt::getZones(true,true),html::escapeHTML($blog_settings->blog_timezone)).
	'</label></p>'.
	'</div>'.
	
	'<div class="col">'.
	'<p><label>'.__('Copyright notice:').
	form::field('copyright_notice',30,255,html::escapeHTML($blog_settings->copyright_notice)).
	'</label></p>'.
	
	'<p><label>'.__('New post URL format:').
	form::combo('post_url_format',$post_url_combo,html::escapeHTML($blog_settings->post_url_format)).
	'</label></p>'.
	
	'<p><label class="classic">'.
	form::checkbox('enable_xmlrpc','1',$blog_settings->enable_xmlrpc).
	__('Enable XML/RPC interface').'</label>'.
	' - <a href="#xmlrpc">'.__('more information').'</a></p>'.
	'</div>'.
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>';
	
	echo
	'<fieldset><legend>'.__('Comments and trackbacks').'</legend>'.
	'<div class="two-cols">'.
	'<div class="col">'.
	'<p><label class="classic">'.
	form::checkbox('allow_comments','1',$blog_settings->allow_comments).
	__('Accept comments').'</label></p>'.

	'<p><label class="classic">'.
	form::checkbox('comments_pub','1',!$blog_settings->comments_pub).
	__('Moderate comments').'</label></p>'.

	'<p><label class="classic">'.sprintf(__('Leave comments open for %s days'),
	form::field('comments_ttl',2,3,$blog_settings->comments_ttl)).
	'</label></p>'.
	'<p class="form-note">'.__('Leave blank to disable this feature.').'</p>'.

	'<p><label class="classic">'.
	form::checkbox('wiki_comments','1',$blog_settings->wiki_comments).
	__('Wiki syntax for comments').'</label></p>'.
	'</div>'.
	
	'<div class="col">'.
	'<p><label class="classic">'.
	form::checkbox('allow_trackbacks','1',$blog_settings->allow_trackbacks).
	__('Accept trackbacks').'</label></p>'.

	'<p><label class="classic">'.
	form::checkbox('trackbacks_pub','1',!$blog_settings->trackbacks_pub).
	__('Moderate trackbacks').'</label></p>'.

	'<p><label class="classic">'.sprintf(__('Leave trackbacks open for %s days'),
	form::field('trackbacks_ttl',2,3,$blog_settings->trackbacks_ttl)).'</label></p>'.
	'<p class="form-note">'.__('Leave blank to disable this feature.').'</p>'.

	'<p><label class="classic">'.
	form::checkbox('comments_nofollow','1',$blog_settings->comments_nofollow).
	__('Add "nofollow" relation on comments and trackbacks links').'</label></p>'.
	'</div>'.
	'</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>';
	
	echo
	'<fieldset><legend>'.__('Blog presentation').'</legend>'.
	'<div class="two-cols">'.
	'<div class="col">'.
	'<p><label>'.__('Date format:').
	form::field('date_format',30,255,html::escapeHTML($blog_settings->date_format)).
	'</label></p>'.
	
	'<p><label>'.__('Time format:').
	form::field('time_format',30,255,html::escapeHTML($blog_settings->time_format)).
	'</label></p>'.
	
	'<p><label class="classic">'.
	form::checkbox('use_smilies','1',$blog_settings->use_smilies).
	__('Display smilies on entries and comments').'</label></p>'.
	'</div>'.
	
	'<div class="col">'.
	'<p><label class="classic">'.sprintf(__('Display %s entries per page'),
	form::field('nb_post_per_page',2,3,$blog_settings->nb_post_per_page)).
	'</label></p>'.
	
	'<p><label class="classic">'.sprintf(__('Display %s entries per feed'),
	form::field('nb_post_per_feed',2,3,$blog_settings->nb_post_per_feed)).
	'</label></p>'.
	
	'<p><label class="classic">'.sprintf(__('Display %s comments per feed'),
	form::field('nb_comment_per_feed',2,3,$blog_settings->nb_comment_per_feed)).
	'</label></p>'.
	
	'<p><label class="classic">'.
	form::checkbox('short_feed_items','1',$blog_settings->short_feed_items).
	__('Truncate feeds').'</label></p>'.
	'</div>'.
     '</div>'.
	'<br class="clear" />'. //Opera sucks
	'</fieldset>';
	
	echo
	'<fieldset><legend>'.__('Media and images').'</legend>'.
	'<div class="two-cols">'.
	'<div class="col">'.
	'<h4>'.__('Generated image sizes (in pixels)').'</h4>'.
	'<p class="field"><label>'.__('Thumbnails:').' '.
	form::field('media_img_t_size',3,3,$blog_settings->media_img_t_size).'</label></p>'.
	
	'<p class="field"><label>'.__('Small:').' '.
	form::field('media_img_s_size',3,3,$blog_settings->media_img_s_size).'</label></p>'.
	
	'<p class="field"><label>'.__('Medium:').' '.
	form::field('media_img_m_size',3,3,$blog_settings->media_img_m_size).'</label></p>'.
	'</div>'.
	
	'<div class="col">'.
	'<h4><label for="media_img_title_pattern">'.__('Inserted image title').'</label></h4>'.
	'<p>'.__('This defines image tag title when you insert it in a post from the media manager. It is retrieved from the picture\'s metadata.').'</p>'.
	'<p>'.form::combo('media_img_title_pattern',$img_title_combo,html::escapeHTML($blog_settings->media_img_title_pattern)).'</p>'.
	'</div>'.
	'</div>'.
	'</fieldset>';
	
	echo
	'<fieldset><legend>'.__('Search engines robots policy').'</legend>';
	
	foreach ($robots_policy_options as $k => $v)
	{
		echo '<p><label class="classic">'.
		form::radio(array('robots_policy'),$k,$blog_settings->robots_policy == $k).' '.$v.'</label></p>';
	}
	
	echo '</fieldset>';
	
	
	# --BEHAVIOR-- adminBlogPreferencesForm
	$core->callBehavior('adminBlogPreferencesForm',$core,$blog_settings);
	
	echo
	'<p><input type="submit" accesskey="s" value="'.__('save').'" />'.
	(!$standalone ? form::hidden('id',$blog_id) : '').
	'</p>'.
	'</form>';
	
	if ($core->auth->isSuperAdmin() && $blog_id != $core->blog->id)
	{
		echo
		'<form action="blog_del.php" method="post">'.
		'<p><input type="submit" value="'.__('Delete this blog').'" />'.
		form::hidden(array('blog_id'),$blog_id).
		$core->formNonce().'</p>'.
		'</form>';
	}
	
	# XML/RPC information
	echo '<h3 id="xmlrpc">'.__('XML/RPC interface').'</h3>';
	
	echo '<p>'.__('XML/RPC interface allows you to edit your blog with an external client.').'</p>';
	
	if (!$blog_settings->enable_xmlrpc)
	{
		echo '<p>'.__('XML/RPC interface is not active. Change settings to enable it.').'</p>';
	}
	else
	{
		echo
		'<p>'.__('XML/RPC interface is active. You should set the following parameters on your XML/RPC client:').'</p>'.
		'<ul>'.
		'<li>'.__('Server URL:').' <strong>'.
		sprintf(DC_XMLRPC_URL,$core->blog->url,$core->blog->id).
		'</strong></li>'.
		'<li>'.__('Blogging system:').' <strong>Movable Type</strong></li>'.
		'<li>'.__('User name:').' <strong>'.$core->auth->userID().'</strong></li>'.
		'<li>'.__('Password:').' <strong>'.__('your password').'</strong></li>'.
		'<li>'.__('Blog ID:').' <strong>1</strong></li>'.
		'</ul>';
	}
	
	echo '</div>';
	
	#
	# Users on the blog (with permissions)
	
	$blog_users = $core->getBlogPermissions($blog_id,$core->auth->isSuperAdmin());
	$perm_types = $core->auth->getPermissionsTypes();
	
	echo
	'<div class="multi-part" id="users" title="'.__('Users').'">'.
	'<h3>'.__('Users on this blog').'</h3>';
	
	if (empty($blog_users))
	{
		echo '<p>'.__('No users').'</p>';
	}
	else
	{
		if ($core->auth->isSuperAdmin()) {
			$user_url_p = '<a href="user.php?id=%1$s">%1$s</a>';
		} else {
			$user_url_p = '%1$s';
		}
		
		foreach ($blog_users as $k => $v)
		{
			if (count($v['p']) > 0)
			{
				echo
				'<h4>'.sprintf($user_url_p,html::escapeHTML($k)).
				' ('.html::escapeHTML(dcUtils::getUserCN(
					$k, $v['name'], $v['firstname'], $v['displayname']
				)).')';
				
				if (!$v['super'] && $core->auth->isSuperAdmin()) {
					echo
					' - <a href="permissions.php?blog_id[]='.$blog_id.'&amp;user_id[]='.$k.'">'
					.__('change permissions').'</a>';
				}
				
				echo '</h4>';
				
				echo '<ul>';
				if ($v['super']) {
					echo '<li>'.__('Super administrator').'</li>';
				} else {
					foreach ($v['p'] as $p => $V) {
						echo '<li>'.__($perm_types[$p]).'</li>';
					}
				}
				echo '</ul>';
			}
		}
	}
	
	echo '</div>';
}

dcPage::helpBlock('core_blog_pref');
dcPage::close();
?>