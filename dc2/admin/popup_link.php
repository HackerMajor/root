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

$href = !empty($_GET['href']) ? $_GET['href'] : '';
$hreflang = !empty($_GET['hreflang']) ? $_GET['hreflang'] : '';

dcPage::openPopup(__('Add a link'),dcPage::jsLoad('js/jsToolBar/popup_link.js'));

echo '<h2>'.__('Add a link').'</h2>';

# Languages combo
$rs = $core->blog->getLangs(array('order'=>'asc'));
$all_langs = l10n::getISOcodes(0,1);
$lang_combo = array('' => '', __('Most used') => array(), __('Available') => l10n::getISOcodes(1,1));
while ($rs->fetch()) {
	if (isset($all_langs[$rs->post_lang])) {
		$lang_combo[__('Most used')][$all_langs[$rs->post_lang]] = $rs->post_lang;
		unset($lang_combo[__('Available')][$all_langs[$rs->post_lang]]);
	} else {
		$lang_combo[__('Most used')][$rs->post_lang] = $rs->post_lang;
	}
}
unset($all_langs);
unset($rs);

echo
'<form id="link-insert-form" action="#" method="get">'.
'<p><label class="required" title="'.__('Required field').'">'.__('Link URL:').' '.
form::field('href',35,512,html::escapeHTML($href)).'</label></p>'.
'<p><label>'.__('Link language:').' '.
form::combo('hreflang',$lang_combo,$hreflang).
'</label></p>'.
'</form>'.

'<p><a class="button" href="#" id="link-insert-cancel">'.__('cancel').'</a> - '.
'<strong><a class="button" href="#" id="link-insert-ok">'.__('insert').'</a></strong></p>'."\n".

'<script type="text/javascript">'."\n".
'//<![CDATA['."\n".
'$(\'input[name="href"]\').get(0).focus();'."\n".
'//]]>'."\n".
'</script>'."\n";

dcPage::closePopup();
?>