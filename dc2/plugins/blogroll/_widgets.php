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

$core->addBehavior('initWidgets',array('blogrollWidgets','initWidgets'));
$core->addBehavior('initDefaultWidgets',array('blogrollWidgets','initDefaultWidgets'));

class blogrollWidgets
{
	public static function initWidgets(&$w)
	{
		$w->create('links',__('Blogroll'),array('tplBlogroll','linksWidget'));
		$w->links->setting('title',__('Title:'),__('Links'));
		
		$br = new dcBlogroll($GLOBALS['core']->blog);
		$h = $br->getLinksHierarchy($br->getLinks());
		$h = array_keys($h);
		$categories = array(__('All categories') => '');
		foreach ($h as $v) {
			if ($v) {
				$categories[$v] = $v;
			}
		}
		unset($br,$h);
		$w->links->setting('category',__('Category'),'','combo',$categories);
		
		$w->links->setting('homeonly',__('Home page only'),1,'check');
	}
	
	public static function initDefaultWidgets(&$w,&$d)
	{
		$d['extra']->append($w->links);
	}
}
?>