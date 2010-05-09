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

l10n::set(dirname(__FILE__).'/locales/'.$_lang.'/main');
$css_file = path::real($core->blog->public_path).'/custom_style.css';

if (!is_file($css_file) && !is_writable(dirname($css_file))) {
	throw new Exception(
		sprintf(__('File %s does not exist and directory %s is not writable.'),
		$css_file,dirname($css_file))
	);
}

if (isset($_POST['css']))
{
	@$fp = fopen($css_file,'wb');
	fwrite($fp,$_POST['css']);
	fclose($fp);
	
	echo
	'<div class="message"><p>'.
	__('Style sheet upgraded.').
	'</p></div>';
}

$css_content = is_file($css_file) ? file_get_contents($css_file) : '';

echo
'<p class="area"><label>'.__('Style sheet:').' '.
form::textarea('css',60,20,html::escapeHTML($css_content)).'</label></p>';
?>