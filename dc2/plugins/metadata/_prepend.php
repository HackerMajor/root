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

$GLOBALS['core']->url->register('tag','tag','^tag/(.+)$',array('urlMetadata','tag'));
$GLOBALS['core']->url->register('tags','tags','^tags$',array('urlMetadata','tags'));
$GLOBALS['core']->url->register('tag_feed','feed/tag','^feed/tag/(.+)$',array('urlMetadata','tagFeed'));

$GLOBALS['__autoload']['dcMeta'] = dirname(__FILE__).'/class.dc.meta.php';
?>