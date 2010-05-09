<?php
# ***** BEGIN LICENSE BLOCK *****
# This file is part of DotClear.
# Copyright (c) 2005 Olivier Meunier and contributors. All rights
# reserved.
#
# DotClear is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
# 
# DotClear is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with DotClear; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
#
# ***** END LICENSE BLOCK *****

# Menu template functions

/* require dirname(__FILE__).'/_widgets.php'; */

$core->tpl->addValue('MenuFreshy',array('tplMenuFreshy','menu'));
$core->tpl->addValue('IfCurrentLinkMenu',array('tplMenuFreshy','IfCurrentLinkMenu'));


class tplMenuFreshy
{
	public static function IfCurrentLinkMenu($attr)
	{
		if ($_SERVER['REQUEST_URI']){}
	}
	public static function menu($attr)
	{
		$category = '<h3>%s</h3>';
		$block = '<ul class="menu">%s</ul>';
		$item = '<li>%s</li>';
		$open_ul = "";
		$close_ul = "";

		if (isset($attr['block'])) {
			$block = addslashes($attr['block']);
		}
		
		if (isset($attr['item'])) {
			$item = addslashes($attr['item']);
		}
		
		return
		$open_ul."\n".
		'<?php '.
		"echo tplMenuFreshy::getList('".$category."','".$block."','".$item."'); ".
		'?>'.
		$close_ul."\n";
	}

	
	public static function getList($category='<h3>%s</h3>',$block='<ul class="menu">%s</ul>',$item='<li>%s</li>')
	{
		require_once dirname(__FILE__).'/class.dc.blogmenu.php';
		$menu = new dcBlogMenu($GLOBALS['core']->blog);
		
		try {
			$links = $menu->getLinks();
		} catch (Exception $e) {
			return false;
		}
		
		$res = '';
		
		foreach ($menu->getLinksHierarchy($links) as $k => $v)
		{
			if ($k != '') {
				$res .= sprintf($category,html::escapeHTML($k))."\n";
			}
			
			$res .= self::getLinksList($v,$block,$item);
		}
		
		return $res;
	}
	
	private static function getLinksList($links,$block='<ul class="menu">%s</ul>',$item='<li>%s</li>')
	{
		global $core;  // Pour avoir accès a l'url du blog et aussi connaître le theme
		$letheme = $core->blog->settings->theme;
		$list = '';
		$url = $_SERVER['REQUEST_URI'];
		
		$first = true;
		
		// Nous switchons pour personnaliser en fonction du thème
		switch ($letheme) {
			case "welsh-2-0":
				foreach ($links as $v)
				{
					$title = $v['link_title'];
					$href  = $v['link_href'];
					$desc = $v['link_desc'];
					$lang  = $v['link_lang'];
					$xfn = $v['link_xfn'];

					$item = '<li>%s</li>';
					$active = "";
					// Si il faut tester aussi si page accueil
					if ($xfn=="accueil"){
						// Si nous sommes en accueil (il peut y avoir deux url pour un accueil)
						if ($core->url->type == 'default') {
							$active = 'id="active"';
						}
					} else {	
						if ($url == html::escapeHTML($href)) {
							$active = 'id="active"';
						}
					}
					
					$link = 
						'<a '.$active.' href="'.html::escapeHTML($href).'"'.
						((!$lang) ? '' : ' hreflang="'.html::escapeHTML($lang).'"').
						((!$desc) ? '' : ' title="'.html::escapeHTML($desc).'"').
						((!$xfn) ? '' : ' rel="'.html::escapeHTML($xfn).'"').
						'>'.
						html::escapeHTML($title).
						'</a>';
					$list .= sprintf($item,$link)."\n";
				} // End foreach theme welsh-2-0
				break;
				
			default:  // Les themes studiopress et freshy et les autres
				foreach ($links as $v)
				{
					$title = $v['link_title'];
					$href  = $v['link_href'];
					$desc = $v['link_desc'];
					$lang  = $v['link_lang'];
					$xfn = $v['link_xfn'];
					
		
					// Si c'est le premier on lui met une classlien
					if ($first==true){
						$classlien=" class=\"first_menu\" ";
						$first=false;
					} else {
						$classlien="";
					}	
					
					// Si ce doit être le dernier
					if ($xfn=="me"){
						$classlast=" last_menu";
						$classlienlast=" class=\"last_menu\""; 
						$classitem=$classlast;
					} else {
						$classlast="";
						$classlienlast="";
						$classitem="page_item";
					}	
					
					$link =
					'<a href="'.html::escapeHTML($href).'"'.
					((!$lang) ? '' : ' hreflang="'.html::escapeHTML($lang).'"').
					((!$desc) ? '' : ' title="'.html::escapeHTML($desc).'"').
					((!$xfn) ? '' : ' rel="'.html::escapeHTML($xfn).'"').
					$classlien.$classlienlast.
					'><span>'.
					html::escapeHTML($title).
					'</span></a>';
					
					// Si il faut tester aussi si page accuei
					if ($xfn=="accueil"){
						// Si nous sommes en accueil
						if ($core->url->type == 'default') {
							$item = '<li class="current_page_item '.$classitem.'">%s</li>';
						} else {
							$item = '<li class="'.$classitem.'">%s</li>';
						}
					} else {	
						if ($url == html::escapeHTML($href)) {
							$item = '<li class="current_page_item '.$classitem.'">%s</li>';
						} else {
							$item = '<li class="'.$classitem.'">%s</li>';			
						}
					}	
					$list .= sprintf($item,$link)."\n";
				}
		} // find de switch ($letheme)
		return sprintf($block,$list)."\n";
	}
	

}

?>