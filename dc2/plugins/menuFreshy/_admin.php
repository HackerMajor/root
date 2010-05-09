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

if (isset($__dashboard_icons) && $core->auth->check('menuFreshy',$core->blog->id)) {
	$__dashboard_icons[] = array(__('Menu Freshy'),'plugin.php?p=menuFreshy','index.php?pf=menuFreshy/icon.png');
}

$_menu['Plugins']->addItem('Menu Freshy','plugin.php?p=menuFreshy','index.php?pf=menuFreshy/icon-small.png',
                preg_match('/plugin.php\?p=menuFreshy(&.*)?$/',$_SERVER['REQUEST_URI']),
                $core->auth->check('usage,contentadmin',$core->blog->id));

$core->auth->setPermissionType('menuFreshy',__('manage menu'));

/* require dirname(__FILE__).'/_widgets.php'; */
?>