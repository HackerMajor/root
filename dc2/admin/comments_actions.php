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

dcPage::check('usage,contentadmin');

$params = array();
$redir = 'comments.php';

if (!empty($_POST['action']) && !empty($_POST['comments']))
{
	$comments = $_POST['comments'];
	$action = $_POST['action'];
	
	if (isset($_POST['redir']) && strpos($_POST['redir'],'://') === false)
	{
		$redir = $_POST['redir'];
	}
	else
	{
		$redir =
		'comments.php?type='.$_POST['type'].
		'&author='.$_POST['author'].
		'&status='.$_POST['status'].
		'&sortby='.$_POST['sortby'].
		'&ip='.$_POST['ip'].
		'&order='.$_POST['order'].
		'&page='.$_POST['page'].
		'&nb='.(integer) $_POST['nb'];
	}
	
	foreach ($comments as $k => $v) {
		$comments[$k] = (integer) $v;
	}
	
	$params['sql'] = 'AND C.comment_id IN('.implode(',',$comments).') ';
	$params['no_content'] = true;
	
	$co = $core->blog->getComments($params);
	
	if (preg_match('/^(publish|unpublish|pending|junk)$/',$action))
	{
		switch ($action) {
			case 'unpublish' : $status = 0; break;
			case 'pending' : $status = -1; break;
			case 'junk' : $status = -2; break;
			default : $status = 1; break;
		}
		
		while ($co->fetch())
		{
			try {
				$core->blog->updCommentStatus($co->comment_id,$status);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
		
		if (!$core->error->flag()) {
			http::redirect($redir);
		}
	}
	elseif ($action == 'delete')
	{
		while ($co->fetch())
		{
			try {
				$core->blog->delComment($co->comment_id);
			} catch (Exception $e) {
				$core->error->add($e->getMessage());
			}
		}
		
		if (!$core->error->flag()) {
			http::redirect($redir);
		}
	}
}

/* DISPLAY
-------------------------------------------------------- */
dcPage::open(__('Comments'));

echo '<p><a class="back" href="'.str_replace('&','&amp;',$redir).'">'.__('back').'</a></p>';

dcPage::close();
?>