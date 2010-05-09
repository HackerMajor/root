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

dcPage::checkSuper();

try
{
	# Create settings if they don't exist
	if ($core->blog->settings->pings_active === null)
	{
		$default_pings_uris = array(
			'Ping-o-Matic!' => 'http://rpc.pingomatic.com/',
			'Google Blog Search' => 'http://blogsearch.google.com/ping/RPC2'
		);
		
		$core->blog->settings->setNameSpace('pings');
		$core->blog->settings->put('pings_active',1,'boolean','Activate pings plugin',true,true);
		$core->blog->settings->put('pings_uris',serialize($default_pings_uris),'string','Pings services URIs',true,true);
		http::redirect($p_url);
	}
	
	$pings_uris = @unserialize($core->blog->settings->pings_uris);
	if (!$pings_uris) {
		$pings_uris = array();
	}
	
	if (isset($_POST['pings_srv_name']))
	{
		$pings_srv_name = is_array($_POST['pings_srv_name']) ? $_POST['pings_srv_name'] : array();
		$pings_srv_uri = is_array($_POST['pings_srv_uri']) ? $_POST['pings_srv_uri'] : array();
		$pings_uris = array();
		
		foreach ($pings_srv_name as $k => $v) {
			if (trim($v) && trim($pings_srv_uri[$k])) {
				$pings_uris[trim($v)] = trim($pings_srv_uri[$k]);
			}
		}
		
		$core->blog->settings->setNameSpace('pings');
		$core->blog->settings->put('pings_active',!empty($_POST['pings_active']),null,null,true,true);
		$core->blog->settings->put('pings_uris',serialize($pings_uris),null,null,true,true);
		http::redirect($p_url.'&up=1');
	}
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
?>
<html>
<head>
  <title><?php echo __('Pings'); ?></title>
</head>

<body>
<?php
echo '<h2>'.__('Pings configuration').'</h2>';

if (!empty($_GET['up'])) {
	echo '<p class="message">'.__('Settings have been successfully updated.').'</p>';
}

echo
'<form action="'.$p_url.'" method="post">'.
'<p><label class="classic">'.form::checkbox('pings_active',1,$core->blog->settings->pings_active).' '.
__('Activate pings extension').'</label></p>';

foreach ($pings_uris as $n => $u)
{
	echo
	'<p><label class="classic">'.__('Service name:').' '.
	form::field(array('pings_srv_name[]'),20,128,html::escapeHTML($n)).'</label> '.
	'<label class="classic">'.__('Service URI:').' '.
	form::field(array('pings_srv_uri[]'),40,255,html::escapeHTML($u)).'</label>';
	
	if (!empty($_GET['test']))
	{
		try {
			pingsAPI::doPings($u,'Example site','http://example.com');
			echo ' <img src="images/check-on.png" alt="ok" />';
		} catch (Exception $e) {
			echo ' <img src="images/check-off.png" alt="'.__('error').'" /> '.$e->getMessage();
		}
	}
	
	echo '</p>';
}

echo
'<p><label class="classic">'.__('Service name:').' '.
form::field(array('pings_srv_name[]'),20,128).'</label> '.
'<label class="classic">'.__('Service URI:').' '.
form::field(array('pings_srv_uri[]'),40,255).'</label>'.
'</p>'.

'<p><input type="submit" value="'.__('save').'" />'.
$core->formNonce().'</p>'.
'</form>';

echo '<p><a href="'.$p_url.'&amp;test=1">'.__('Test ping services').'</a></p>';
?>
</body>
</html>