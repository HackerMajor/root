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

if (!empty($_GET['pf'])) {
	require dirname(__FILE__).'/../inc/load_plugin_file.php';
	exit;
}

require dirname(__FILE__).'/../inc/admin/prepend.php';

if (!empty($_GET['default_blog'])) {
	try {
		$core->setUserDefaultBlog($core->auth->userID(),$core->blog->id);
		http::redirect('index.php');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

dcPage::check('usage,contentadmin');

# Logout
if (!empty($_GET['logout'])) {
	$core->session->destroy();
	if (isset($_COOKIE['dc_admin'])) {
		unset($_COOKIE['dc_admin']);
		setcookie('dc_admin',false,-600,'','',DC_ADMIN_SSL);
	}
	http::redirect('auth.php');
	exit;
}

# Plugin install
$plugins_install = $core->plugins->installModules();

# Dashboard icons
$__dashboard_icons = new ArrayObject();

$post_count = $core->blog->getPosts(array(),true)->f(0);
$str_entries = ($post_count > 1) ? __('%d entries') : __('%d entry');

$comment_count = $core->blog->getComments(array(),true)->f(0);
$str_comments = ($comment_count > 1) ? __('%d comments') : __('%d comment');

$__dashboard_icons['new_post'] = new ArrayObject(array(__('New entry'),'post.php','images/menu/edit-b.png'));
$__dashboard_icons['posts'] = new ArrayObject(array(sprintf($str_entries,$post_count),'posts.php','images/menu/entries-b.png'));
$__dashboard_icons['comments'] = new ArrayObject(array(sprintf($str_comments,$comment_count),'comments.php','images/menu/comments-b.png'));
$__dashboard_icons['prefs'] = new ArrayObject(array(__('User preferences'),'preferences.php','images/menu/user-pref-b.png'));

if ($core->auth->check('admin',$core->blog->id))
{
	$__dashboard_icons['blog_pref'] = new ArrayObject(array(__('Blog settings'),'blog_pref.php','images/menu/blog-pref-b.png'));
	$__dashboard_icons['blog_theme'] = new ArrayObject(array(__('Blog aspect'),'blog_theme.php','images/menu/blog-theme-b.png'));
}

$core->callBehavior('adminDashboardIcons', $core, $__dashboard_icons);


# Latest news for dashboard
$__dashboard_items = new ArrayObject(array(new ArrayObject,new ArrayObject));

# Documentation links
if (!empty($__resources['doc']))
{
	$doc_links = '<h3>'.__('Documentation').'</h3><ul>';
	
	foreach ($__resources['doc'] as $k => $v) {
		$doc_links .= '<li><a href="'.$v.'">'.$k.'</a></li>';
	}
	
	$doc_links .= '</ul>';
	$__dashboard_items[0][] = $doc_links;
}

try
{
	if (empty($__resources['rss_news'])) {
		throw new Exception();
	}
	
	$feed_reader = new feedReader;
	$feed_reader->setCacheDir(DC_TPL_CACHE);
	$feed_reader->setTimeout(2);
	$feed_reader->setUserAgent('Dotclear - http://www.dotclear.org/');
	$feed = $feed_reader->parse($__resources['rss_news']);
	if ($feed)
	{
		$latest_news = '<h3>'.__('Latest news').'</h3><dl id="news">';
		$i = 1;
		foreach ($feed->items as $item)
		{
			$dt = isset($item->link) ? '<a href="'.$item->link.'">'.$item->title.'</a>' : $item->title;

			if ($i < 3) {
				$latest_news .=
				'<dt>'.$dt.'</dt>'.
				'<dd><p><strong>'.dt::dt2str('%d %B %Y',$item->pubdate,'Europe/Paris').'</strong>: '.
				'<em>'.text::cutString(html::clean($item->content),120).'...</em></p></dd>';
			} else {
				$latest_news .=
				'<dt>'.$dt.'</dt>'.
				'<dd>'.dt::dt2str('%d %B %Y',$item->pubdate,'Europe/Paris').'</dd>';
			}
			$i++;
			if ($i > 7) { break; }
		}
		$latest_news .= '</dl>';
		$__dashboard_items[1][] = $latest_news;
	}
}
catch (Exception $e) {}

$core->callBehavior('adminDashboardItems', $core, $__dashboard_items);

/* DISPLAY
-------------------------------------------------------- */
dcPage::open(__('Dashboard'),
	dcPage::jsToolBar().
	dcPage::jsLoad('js/_index.js').
	# --BEHAVIOR-- adminDashboardHeaders
	$core->callBehavior('adminDashboardHeaders')
);

echo '<h2>'.html::escapeHTML($core->blog->name).' &rsaquo; '.__('Dashboard');

if ($core->auth->getInfo('user_default_blog') != $core->blog->id && $core->auth->blog_count > 1) {
	echo
	' - <a href="index.php?default_blog=1" class="button">'.__('Make this blog my default blog').'</a>';
}

echo '</h2>';

if ($core->blog->status == 0) {
	echo '<p class="static-msg">'.__('This blog is offline').'</p>';
} elseif ($core->blog->status == -1) {
	echo '<p class="static-msg">'.__('This blog is removed').'</p>';
}

if (!DC_ADMIN_URL) {
	echo
	'<p class="static-msg">'.
	__('DC_ADMIN_URL is not defined, you should edit your configuration file.').
	'</p>';
}

# Plugins install messages
if (!empty($plugins_install['success']))
{
	echo '<div class="static-msg">'.__('Following plugins have been installed:').'<ul>';
	foreach ($plugins_install['success'] as $k => $v) {
		echo '<li>'.$k.'</li>';
	}
	echo '</ul></div>';
}
if (!empty($plugins_install['failure']))
{
	echo '<div class="error">'.__('Following plugins have not been installed:').'<ul>';
	foreach ($plugins_install['failure'] as $k => $v) {
		echo '<li>'.$k.' ('.$v.')</li>';
	}
	echo '</ul></div>';
}

# Dashboard icons
echo '<div id="dashboard-main"><div id="icons">';
foreach ($__dashboard_icons as $i)
{
	echo
	'<p><a href="'.$i[1].'"><img src="'.$i[2].'" alt="" /></a>'.
	'<span><a href="'.$i[1].'">'.$i[0].'</a></span></p>';
}
echo '</div>';

if ($core->auth->check('usage,contentadmin',$core->blog->id))
{
	$categories_combo = array('&nbsp;' => '');
	try {
		$categories = $core->blog->getCategories(array('post_type'=>'post'));
		while ($categories->fetch()) {
			$categories_combo[] = new formSelectOption(
				str_repeat('&nbsp;&nbsp;',$categories->level-1).'&bull; '.html::escapeHTML($categories->cat_title),
				$categories->cat_id
			);
		}
	} catch (Exception $e) { }
	
	echo
	'<div id="quick">'.
	'<h3>'.__('Quick entry').'</h3>'.
	'<form id="quick-entry" action="post.php" method="post">'.
	'<fieldset>'.
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Title:').
	form::field('post_title',20,255,'','maximal',2).
	'</label></p>'.
	'<p class="area"><label class="required" title="'.__('Required field').'" '.
	'for="post_content">'.__('Content:').'</label> '.
	form::textarea('post_content',50,7,'','',2).
	'</p>'.
	'<p><label class="classic">'.__('Category:').' '.
	form::combo('cat_id',$categories_combo,'','',2).'</label></p>'.
	'<p><input type="submit" value="'.__('save').'" name="save" tabindex="3" /> '.
	($core->auth->check('publish',$core->blog->id)
		? '<input type="hidden" value="'.__('save and publish').'" name="save-publish" />'
		: '').
	$core->formNonce().
	form::hidden('post_status',-2).
	form::hidden('post_format',$core->auth->getOption('post_format')).
	form::hidden('post_excerpt','').
	form::hidden('post_lang',$core->auth->getInfo('user_lang')).
	form::hidden('post_notes','').
	'</p>'.
	'</fieldset>'.
	'</form>'.
	'</div>';
}

echo '</div>';

# Dashboard columns
echo '<div id="dashboard-items">';

# Dotclear updates notifications
if ($core->auth->isSuperAdmin() && is_readable(DC_DIGESTS))
{
	$updater = new dcUpdate(DC_UPDATE_URL,'dotclear',DC_UPDATE_VERSION,DC_TPL_CACHE.'/versions');
	$new_v = $updater->check(DC_VERSION);
	
	if ($updater->getNotify() && $new_v) {
		echo
		'<div id="upg-notify" class="static-msg"><p>'.sprintf(__('Dotclear %s is available!'),$new_v).'</p> '.
		'<ul><li><strong><a href="update.php">'.sprintf(__('Upgrade now'),$new_v).'</a></strong>'.
		'</li><li><a href="update.php?hide_msg=1">'.__('Remind me later').'</a>'.
		'</li></ul></div>';
	}
}

foreach ($__dashboard_items as $i)
{
	echo '<div>';
	foreach ($i as $v) {
		echo $v;
	}
	echo '</div>';
}
echo '</div>';

dcPage::close();
?>