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

$_menu['Plugins']->addItem(__('Maintenance'),'plugin.php?p=maintenance','index.php?pf=maintenance/icon.png',
		preg_match('/plugin.php\?p=maintenance(&.*)?$/',$_SERVER['REQUEST_URI']),
		$core->auth->isSuperAdmin());
?>