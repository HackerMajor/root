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
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$_menu['Blog']->addItem(__('Tags'),'plugin.php?p=metadata&amp;m=tags','index.php?pf=metadata/tags.png',
		preg_match('/plugin.php\?p=metadata&m=tag(s|_posts)?(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->check('usage,contentadmin',$core->blog->id));

require dirname(__FILE__).'/_widgets.php';

$core->addBehavior('adminPostFormSidebar',array('metaBehaviors','tagsField'));

$core->addBehavior('adminAfterPostCreate',array('metaBehaviors','setTags'));
$core->addBehavior('adminAfterPostUpdate',array('metaBehaviors','setTags'));

$core->addBehavior('adminPostHeaders',array('metaBehaviors','postHeaders'));

$core->addBehavior('adminPostsActionsCombo',array('metaBehaviors','adminPostsActionsCombo'));
$core->addBehavior('adminPostsActions',array('metaBehaviors','adminPostsActions'));
$core->addBehavior('adminPostsActionsContent',array('metaBehaviors','adminPostsActionsContent'));

$core->addBehavior('coreInitWikiPost',array('metaBehaviors','coreInitWikiPost'));

$core->addBehavior('exportFull',array('metaBehaviors','exportFull'));
$core->addBehavior('exportSingle',array('metaBehaviors','exportSingle'));
$core->addBehavior('importInit',array('metaBehaviors','importInit'));
$core->addBehavior('importSingle',array('metaBehaviors','importSingle'));
$core->addBehavior('importFull',array('metaBehaviors','importFull'));
$core->addBehavior('importPrepareDC12',array('metaBehaviors','importPrepareDC12'));

$core->rest->addFunction('getMeta',array('metaRest','getMeta'));
$core->rest->addFunction('delMeta',array('metaRest','delMeta'));
$core->rest->addFunction('setPostMeta',array('metaRest','setPostMeta'));

# BEHAVIORS
class metaBehaviors
{
	public static function coreInitWikiPost(&$wiki2xhtml)
	{
		$wiki2xhtml->registerFunction('url:tag',array('metaBehaviors','wiki2xhtmlTag'));
	}
	
	public static function wiki2xhtmlTag($url,$content)
	{
		$url = substr($url,4);
		if (strpos($content,'tag:') === 0) {
			$content = substr($content,4);
		}
		
		
		$tag_url = html::stripHostURL($GLOBALS['core']->blog->url.$GLOBALS['core']->url->getBase('tag'));
		$res['url'] = $tag_url.'/'.rawurlencode(dcMeta::sanitizeMetaID($url));
		$res['content'] = $content;
		
		return $res;
	}
	
	public static function tagsField(&$post)
	{
		$meta = new dcMeta($GLOBALS['core']);
		
		if (!empty($_POST['post_tags'])) {
			$value = $_POST['post_tags'];
		} else {
			$value = ($post) ? $meta->getMetaStr($post->post_meta,'tag') : '';
		}
		
		echo
		'<h3><label for="post_tags">'.__('Tags:').'</label></h3>'.
		'<div class="p" id="meta-edit-tags">'.form::textarea('post_tags',20,3,$value,'maximal',3).'</div>';
	}
	
	public static function setTags(&$cur,&$post_id)
	{
		$post_id = (integer) $post_id;
		
		if (isset($_POST['post_tags'])) {
			$tags = $_POST['post_tags'];
			
			$meta = new dcMeta($GLOBALS['core']);
			
			$meta->delPostMeta($post_id,'tag');
			
			foreach ($meta->splitMetaValues($tags) as $tag) {
				$meta->setPostMeta($post_id,'tag',$tag);
			}
		}
	}
	
	public static function postHeaders()
	{
		$tag_url = $GLOBALS['core']->blog->url.$GLOBALS['core']->url->getBase('tag');
		
		return 
		'<script type="text/javascript" src="index.php?pf=metadata/post.js"></script>'.
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		"metaEditor.prototype.meta_url = 'plugin.php?p=metadata&m=tag_posts&amp;tag=';\n".
		"metaEditor.prototype.text_confirm_remove = '".html::escapeJS(__('Are you sure you want to remove this %s?'))."';\n".
		"metaEditor.prototype.text_add_meta = '".html::escapeJS(__('Add a %s to this entry'))."';\n".
		"metaEditor.prototype.text_choose = '".html::escapeJS(__('Choose from list'))."';\n".
		"metaEditor.prototype.text_all = '".html::escapeJS(__('all'))."';\n".
		"jsToolBar.prototype.elements.tag.title = '".html::escapeJS(__('Tag'))."';\n".
		"jsToolBar.prototype.elements.tag.url = '".html::escapeJS($tag_url)."';\n".
		"\n//]]>\n".
		"</script>\n".
		'<link rel="stylesheet" type="text/css" href="index.php?pf=metadata/style.css" />';
	}
	
	public static function adminPostsActionsCombo(&$args)
	{
		$args[0][__('add tags')] = 'tags';
		
		if ($GLOBALS['core']->auth->check('delete,contentadmin',$GLOBALS['core']->blog->id)) {
			$args[0][__('remove tags')] = 'tags_remove';
		}
	}
	
	public static function adminPostsActions(&$core,$posts,$action,$redir)
	{
		if ($action == 'tags' && !empty($_POST['new_tags']))
		{
			try
			{
				$meta = new dcMeta($core);
				$tags = $meta->splitMetaValues($_POST['new_tags']);
				
				while ($posts->fetch())
				{
					# Get tags for post
					$post_meta = $meta->getMeta('tag',null,null,$posts->post_id);
					$pm = array();
					while ($post_meta->fetch()) {
						$pm[] = $post_meta->meta_id;
					}
					
					foreach ($tags as $t) {
						if (!in_array($t,$pm)) {
							$meta->setPostMeta($posts->post_id,'tag',$t);
						}
					}
				}
				
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
		elseif ($action == 'tags_remove' && !empty($_POST['meta_id']) && $core->auth->check('delete,contentadmin',$core->blog->id))
		{
			try
			{
				$meta = new dcMeta($core);
				while ($posts->fetch())
				{
					foreach ($_POST['meta_id'] as $v)
					{
						$meta->delPostMeta($posts->post_id,'tag',$v);
					}
				}
				
				http::redirect($redir);
			}
			catch (Exception $e)
			{
				$core->error->add($e->getMessage());
			}
		}
	}
	
	public static function adminPostsActionsContent($core,$action,$hidden_fields)
	{
		if ($action == 'tags')
		{
			echo
			'<h2>'.__('Add tags to entries').'</h2>'.
			'<form action="posts_actions.php" method="post">'.
			'<p><label class="area">'.__('Tags to add:').' '.
			form::textarea('new_tags',60,3).
			'</label> '.
			
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'tags').
			'<input type="submit" value="'.__('save').'" /></p>'.
			'</form>';
		}
		elseif ($action == 'tags_remove')
		{
			$meta = new dcMeta($core);
			$tags = array();
			
			foreach ($_POST['entries'] as $id) {
				$post_tags = $meta->getMeta('tag',null,null,(integer) $id)->rows();
				foreach ($post_tags as $v) {
					if (isset($tags[$v['meta_id']])) {
						$tags[$v['meta_id']]++;
					} else {
						$tags[$v['meta_id']] = 1;
					}
				}
			}
			
			echo '<h2>'.__('Remove selected tags from entries').'</h2>';
			
			if (empty($tags)) {
				echo '<p>'.__('No tags for selected entries').'</p>';
				return;
			}
			
			$posts_count = count($_POST['entries']);
			
			echo
			'<form action="posts_actions.php" method="post">'.
			'<fieldset><legend>'.__('Following tags have been found in selected entries:').'</legend>';
			
			foreach ($tags as $k => $n) {
				$label = '<label class="classic">%s %s</label>';
				if ($posts_count == $n) {
					$label = sprintf($label,'%s','<strong>%s</strong>');
				}
				echo '<p>'.sprintf($label,
						form::checkbox(array('meta_id[]'),html::escapeHTML($k)),
						html::escapeHTML($k)).
					'</p>';
			}
			
			echo
			'<p><input type="submit" value="'.__('ok').'" /></p>'.
			$hidden_fields.
			$core->formNonce().
			form::hidden(array('action'),'tags_remove').
			'</fieldset></form>';
		}
	}
	
	public static function exportFull(&$core,&$exp)
	{
		$exp->exportTable('meta');
	}
	
	public static function exportSingle(&$core,&$exp,$blog_id)
	{
		$exp->export('meta',
			'SELECT meta_id, meta_type, M.post_id '.
			'FROM '.$core->prefix.'meta M, '.$core->prefix.'post P '.
			'WHERE P.post_id = M.post_id '.
			"AND P.blog_id = '".$blog_id."'"
		);
	}
	
	public static function importInit(&$bk,&$core)
	{
		$bk->cur_meta = $core->con->openCursor($core->prefix.'meta');
		$bk->meta = new dcMeta($core);
	}
	
	public static function importFull(&$line,&$bk,&$core)
	{
		if ($line->__name == 'meta')
		{
			$bk->cur_meta->clean();
			
			$bk->cur_meta->meta_id   = (string) $line->meta_id;
			$bk->cur_meta->meta_type = (string) $line->meta_type;
			$bk->cur_meta->post_id   = (integer) $line->post_id;
			
			$bk->cur_meta->insert();
		}
	}
	
	public static function importSingle(&$line,&$bk,&$core)
	{
		if ($line->__name == 'meta' && isset($bk->old_ids['post'][(integer) $line->post_id]))
		{
			$line->post_id = $bk->old_ids['post'][(integer) $line->post_id];
			$bk->meta->setPostMeta($line->post_id,$line->meta_type,$line->meta_id);
		}
	}
	
	public static function importPrepareDC12(&$line,&$bk,&$core)
	{
		if ($line->__name == 'post_meta')
		{
			$line->drop('meta_id');
			$line->substitute('meta_key','meta_type');
			$line->substitute('meta_value','meta_id');
			$line->__name = 'meta';
			$line->blog_id = 'default';
		}
	}
}

# REST
class metaRest
{
	public static function getMeta(&$core,$get)
	{
		$meta = new dcMeta($core);
		
		$postid = !empty($get['postId']) ? $get['postId'] : null;
		$limit = !empty($get['limit']) ? $get['limit'] : null;
		$metaId = !empty($get['metaId']) ? $get['metaId'] : null;
		$metaType = !empty($get['metaType']) ? $get['metaType'] : null;
		
		$sortby = !empty($get['sortby']) ? $get['sortby'] : 'meta_type,asc';
		
		$rs = $meta->getMeta($metaType,$limit,$metaId,$postid);
		
		$sortby = explode(',',$sortby);
		$sort = $sortby[0];
		$order = isset($sortby[1]) ? $sortby[1] : 'asc';
		
		switch ($sort) {
			case 'metaId':
				$sort = 'meta_id_lower';
				break;
			case 'count':
				$sort = 'count';
				break;
			case 'metaType':
				$sort = 'meta_type';
				break;
			default:
				$sort = 'meta_type';
		}
		
		$rs->sort($sort,$order);
		
		$rsp = new xmlTag();
		
		while ($rs->fetch())
		{
			$metaTag = new xmlTag('meta');
			$metaTag->type = $rs->meta_type;
			$metaTag->uri = rawurlencode($rs->meta_id);
			$metaTag->count = $rs->count;
			$metaTag->percent = $rs->percent;
			$metaTag->roundpercent = $rs->roundpercent;
			$metaTag->CDATA($rs->meta_id);
			
			$rsp->insertNode($metaTag);
		}
		
		return $rsp;
	}
	
	public static function setPostMeta(&$core,$get,$post)
	{
		if (empty($post['postId'])) {
			throw new Exception('No post ID');
		}
		
		if (empty($post['meta'])) {
			throw new Exception('No meta');
		}
		
		if (empty($post['metaType'])) {
			throw new Exception('No meta type');
		}
		
		$meta = new dcMeta($core);
		
		# Get previous meta for post
		$post_meta = $meta->getMeta($post['metaType'],null,null,$post['postId']);
		$pm = array();
		while ($post_meta->fetch()) {
			$pm[] = $post_meta->meta_id;
		}
		
		foreach ($meta->splitMetaValues($post['meta']) as $m)
		{
			if (!in_array($m,$pm)) {
				$meta->setPostMeta($post['postId'],$post['metaType'],$m);
			}
		}
		
		return true;
	}
	
	public static function delMeta(&$core,$get,$post)
	{
		if (empty($post['postId'])) {
			throw new Exception('No post ID');
		}
		
		if (empty($post['metaId'])) {
			throw new Exception('No meta ID');
		}
		
		if (empty($post['metaType'])) {
			throw new Exception('No meta type');
		}
		
		$meta = new dcMeta($core);
		
		$meta->delPostMeta($post['postId'],$post['metaType'],$post['metaId']);
		
		return true;
	}
}
?>