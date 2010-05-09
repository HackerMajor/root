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
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('initWidgets',array('metaWidgets','initWidgets'));
$core->addBehavior('initDefaultWidgets',array('metaWidgets','initDefaultWidgets'));

class metaWidgets
{
	public static function initWidgets(&$w)
	{
		$w->create('tags',__('Tags'),array('tplMetadata','tagsWidget'));
		$w->tags->setting('title',__('Title:'),__('Tags'));
		$w->tags->setting('limit',__('Limit (empty means no limit):'),'20');
		$w->tags->setting('sortby',__('Order by:'),'meta_id_lower','combo',
			array(__('Tag name') => 'meta_id_lower', __('Entries count') => 'count')
		);
		$w->tags->setting('orderby',__('Sort:'),'asc','combo',
			array(__('Ascending') => 'asc', __('Descending') => 'desc')
		);
	}
	
	public static function initDefaultWidgets(&$w,&$d)
	{
		$d['nav']->append($w->tags);
	}
}
?>