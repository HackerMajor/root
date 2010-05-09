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

$_menu['Plugins']->addItem(__('Pings'),'plugin.php?p=pings','index.php?pf=pings/icon.png',
		preg_match('/plugin.php\?p=pings/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin());

$__autoload['pingsAPI'] = dirname(__FILE__).'/lib.pings.php';
$__autoload['pingsBehaviors'] = dirname(__FILE__).'/lib.pings.php';

$core->addBehavior('adminPostHeaders',array('pingsBehaviors','pingJS'));
$core->addBehavior('adminPostFormSidebar',array('pingsBehaviors','pingsForm'));
$core->addBehavior('adminAfterPostCreate',array('pingsBehaviors','doPings'));
$core->addBehavior('adminAfterPostUpdate',array('pingsBehaviors','doPings'));
?>