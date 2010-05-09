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
if (!defined('DC_CONTEXT_ADMIN')) { exit; }

$id = $_REQUEST['id'];

try {
	$rs = $menu->getLink($id);
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

if (!$core->error->flag() && $rs->isEmpty()) {
	$core->error->add(__('No such link or title'));
} else {
	$link_title = $rs->link_title;
	$link_href = $rs->link_href;
	$link_desc = $rs->link_desc;
	$link_lang = $rs->link_lang;
	$link_xfn = $rs->link_xfn;
}

# Update a link
if (isset($rs) && !$rs->is_cat && !empty($_POST['edit_link']))
{
	$link_title = $_POST['link_title'];
	$link_href = $_POST['link_href'];
	$link_desc = $_POST['link_desc'];
	$link_lang = $_POST['link_lang'];
	
	$link_xfn = '';
		
	if (!empty($_POST['lastposition']))
	{
		$link_xfn .= $_POST['lastposition'];
	}
	if (!empty($_POST['accueil']))
	{
		$link_xfn .= $_POST['accueil'];
	}	
	
	try {
		$menu->updateLink($id,$link_title,$link_href,$link_desc,$link_lang,trim($link_xfn));
		http::redirect($p_url.'&edit=1&id='.$id.'&upd=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}


# Update a category
if (isset($rs) && $rs->is_cat && !empty($_POST['edit_cat']))
{
	$link_desc = $_POST['link_desc'];
	
	try {
		$munu->updateCategory($id,$link_desc);
		http::redirect($p_url.'&edit=1&id='.$id.'&upd=1');
	} catch (Exception $e) {
		$core->error->add($e->getMessage());
	}
}

?>
<html>
<head>
  <title>Menu Freshy</title>
</head>

<body>
<?php echo '<p><a href="'.$p_url.'">'.__('Return to menu').'</a></p>'; ?>

<?php
if (isset($rs) && $rs->is_cat)
{
	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Category has been successfully updated').'</p>';
	}
	
	echo
	'<form action="'.$p_url.'" method="post">'.
	'<fieldset><legend>'.__('Edit category').'</legend>'.
	
	'<p><label class="required classic" title="'.__('Required field').'">'.__('Title:').' '.
	form::field('link_desc',30,255,html::escapeHTML($link_desc)).'</label> '.
	
	form::hidden('edit',1).
	form::hidden('id',$id).
	$core->formNonce().
	'<input type="submit" name="edit_cat" class="submit" value="'.__('save').'"/></p>'.
	'</fieldset>'.
	'</form>';
}
if (isset($rs) && !$rs->is_cat)
{
	if (!empty($_GET['upd'])) {
		echo '<p class="message">'.__('Link has been successfully updated').'</p>';
	}
	
	echo
	'<form action="plugin.php" method="post">'.
	'<fieldset class="two-cols"><legend>'.__('Edit link').'</legend>'.
	
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('Title:').' '.
	form::field('link_title',30,255,html::escapeHTML($link_title)).'</label></p>'.
	
	'<p class="col"><label class="required" title="'.__('Required field').'">'.__('URL:').' '.
	form::field('link_href',30,255,html::escapeHTML($link_href)).'</label></p>'.
	
	'<p class="col"><label>'.__('Description:').' '.
	form::field('link_desc',30,255,html::escapeHTML($link_desc)).'</label></p>'.
	
	'<p class="col"><label>'.__('Language:').' '.
	form::field('link_lang',5,5,html::escapeHTML($link_lang)).'</label></p>'.
	
	'<p>'.form::hidden('p','menuFreshy').
	form::hidden('edit',1).
	form::hidden('id',$id).
	$core->formNonce().
	'<input type="submit" name="edit_link" class="submit" value="'.__('save').'"/></p>'.
	'</fieldset>'.
	
	
	# XFN nightmare
	'<fieldset><legend>'.__('COMPLEMENT').'</legend>'.
	'<table class="noborder">'.
	
	'<tr>'.
	'<th>'.__('Accueil').'</th>'.
	'<td><p>'.'<label class="classic">'.
	form::checkbox(array('accueil'), 'accueil', ($link_xfn == 'accueil')).' '.
	__('Provoquera un double test (sur le contenu du champ URL ou sur le repertoire)').'</label></p></td>'.
	'</tr>'.
		
	'<tr>'.
	'<th>'.__('Dernier').'</th>'.
	'<td><p>'.'<label class="classic">'.
	form::checkbox(array('lastposition'), 'me', ($link_xfn == 'me')).' '.
	__('Dernier liens (une classe de style partculière s\'appliquera)').'</label></p></td>'.
	'</tr>'.
	
	'</table>'.
	
	'</fieldset>'.

	'</form>';
}
?>
</body>
</html>