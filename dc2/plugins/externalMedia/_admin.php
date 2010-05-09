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

$core->addBehavior('adminPostHeaders',array('externalMediaBehaviors','jsLoad'));
$core->addBehavior('adminPageHeaders',array('externalMediaBehaviors','jsLoad'));
$core->addBehavior('adminRelatedHeaders',array('externalMediaBehaviors','jsLoad'));
$core->addBehavior('adminDashboardHeaders',array('externalMediaBehaviors','jsLoad'));

class externalMediaBehaviors
{
	public static function jsLoad()
	{
		return
		'<script type="text/javascript" src="index.php?pf=externalMedia/post.js"></script>'.
		'<script type="text/javascript">'."\n".
		"//<![CDATA[\n".
		dcPage::jsVar('jsToolBar.prototype.elements.extmedia.title',__('External media')).
		"\n//]]>\n".
		"</script>\n";
	}
}
?>