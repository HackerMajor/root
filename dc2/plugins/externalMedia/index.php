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

$m_object = $m_title = $m_url = null;
$media_page = !empty($_POST['media_page']) ? $_POST['media_page'] : null;

$services_regs = array(
	'mp3' =>			'#^(http(s?)://.+\.mp3)$#',
	'flv' =>			'#^(http(s?)://.+\.flv)$#',
	'dailymotion' =>	'#^http://(www.)?dailymotion.com/(.+)#',
	'googlevideo' =>	'#^http://video.google.([a-z]{2,})/videoplay\?docid=(.+?)(&|$)#',
	'vimeo' =>		'#^http://www.vimeo.com/([0-9]+?)#',
	'youtube' =>		'#^http://([a-z]{2,}.)?youtube.com/(.+)#',
	'jamendo' =>		'#^http://(www.)?jamendo.com/[a-z]{2}/(playlist|album|track)/([0-9]+)#',
	'deezer' =>		'#^http://(www.)?deezer.com/track/(.+)#'
);

if ($media_page)
{
	try
	{
		$media_service = false;
		foreach ($services_regs as $k => $v) {
			if (preg_match($v,$media_page)) {
				$media_service = $k;
				break;
			}
		}
		
		if (!$media_service) {
			throw new Exception(__('Unsupported service'));
		}
		
		if ($media_service != 'mp3' && $media_service != 'flv' && $media_service != 'jamendo')
		{
			$http = netHttp::initClient($media_page,$media_path);
			$http->setTimeout(5);
			$http->setUserAgent($_SERVER['HTTP_USER_AGENT']);
			$http->get($media_path);
			
			if ($http->getStatus() != 200) {
				throw new Exception(__('Invalid page URL'));
			}
			
			$content = $http->getContent();
		}

		switch ($media_service)
		{
			case 'mp3':
				$m_url = $media_page;
				$m_title = basename($media_page);
				$m_object = dcMedia::mp3player($m_url,$core->blog->getQmarkURL().'pf=player_mp3.swf');
				break;
			case 'flv':
				$m_url = $media_page;
				$m_title = basename($media_page);
				$m_object = dcMedia::flvPlayer($m_url,$core->blog->getQmarkURL().'pf=player_flv.swf');
				break;
			case 'dailymotion':
				if (preg_match('#<input\stype="text"\sreadonly="readonly"(.+?)id="video_player_embed_code_text"\s/>#ms',$content,$m))
				{
					$cap = html::decodeEntities($m[1]);
					$movie;
					
					if (preg_match('#param\s+name="movie"\s+value="(.+?)"#s',$cap,$M)) {
						$movie = html::escapeHTML($M[1]);
					}
					
					if (preg_match('#<br /><b><a\s+href="(.+?)">(.+?)</a>#s',$cap,$M)) {
						$m_title = html::decodeEntities($M[2]);
						$m_url = $M[1];
					}
					
					if ($movie)
					{
						$m_object =
						'<object type="application/x-shockwave-flash" data="'.$movie.'" width="400" height="316">'."\n".
						'  <param name="movie" value="'.$movie.'" />'."\n".
						'  <param name="wmode" value="transparent" />'."\n".
						'  <param name="FlashVars" value="playerMode=embedded" />'."\n".
						'</object>';
					}
				}
				break;
			case 'googlevideo':
				if (preg_match('#docid=(.+?)(&|$)#',$media_path,$m))
				{
					$movie = 'http://video.google.com/googleplayer.swf?docid='.$m[1];
					
					if (preg_match('#<title>(.+?)</title>#si',$content,$M)) {
						$m_title = $M[1];
					}
					
					$m_object =
					'<object type="application/x-shockwave-flash" data="'.$movie.'" width="400" height="326">'."\n".
					'  <param name="movie" value="'.$movie.'" />'."\n".
					'  <param name="wmode" value="transparent" />'."\n".
					'</object>';
				}
				break;
			case 'vimeo':
				if (preg_match('#<link\s+rel="video_src"\s+href="(.+?)"#ms',$content,$m))
				{
					$w = 425;
					$h = 350;
					
					$movie = $m[1];
					if (preg_match('#<meta\s+name="title"\s+content="(.+?)"#ms',$content,$m)) {
						$m_title = $m[1];
					}
					
					if (preg_match('#<meta\s+name="video_height"\s+content="(\d+?)"#ms',$content,$m)) {
						$h = $m[1];
					}
					if (preg_match('#<meta\s+name="video_width"\s+content="(\d+?)"#ms',$content,$m)) {
						$w = $m[1];
					}
					
					$m_object =
					'<object type="application/x-shockwave-flash" data="'.$movie.'" width="'.$w.'" height="'.$h.'">'."\n".
					'  <param name="movie" value="'.$movie.'" />'."\n".
					'  <param name="wmode" value="transparent" />'."\n".
					'  <param name="FlashVars" value="autoplay=0&amp;fullscreen=1&amp;show_title=1&amp;show_byline=1" />'."\n".
					'</object>';
				}
				break;
			case 'youtube':
				if (preg_match('#<input.+?\s+name="embed_code".+?\s+value="(.+?)"#ms',$content,$m))
				{
					$cap = html::decodeEntities($m[1]);
					$movie = '';
					
					if (preg_match('#param\s+name="movie"\s+value="(.+?)"#s',$cap,$M)) {
						$movie = html::escapeURL($M[1]);
					}
					
					if (preg_match('#<title>Youtube\s+-\s+(.+?)</title>#si',$content,$M)) {
						$m_title = $M[1];
					}
					
					if ($movie)
					{
						$m_object =
						'<object type="application/x-shockwave-flash" data="'.$movie.'" width="425" height="350">'."\n".
						'  <param name="movie" value="'.$movie.'" />'."\n".
						'  <param name="wmode" value="transparent" />'."\n".
						'</object>';
					}
				}
				break;
			case 'jamendo':
				if (preg_match('#^http://(www.)?jamendo.com/[a-z]{2}/(playlist|album|track)/([0-9]+)#',$media_page,$m))
				{
					$type = $m[2];
					$id = $m[3];
					
					$req = 'name';
					if ($type == 'track') {
						$req .= '+stream';
					}

					$http = netHttp::initClient('http://api.jamendo.com/get2/'.$req.'/'.$type.'/plain/?streamencoding=mp31&id='.$id,$media_path);
					$http->setTimeout(5);
					$http->setUserAgent($_SERVER['HTTP_USER_AGENT']);
					$http->get($media_path);
					
					if ($http->getStatus() != 200) {
						throw new Exception(__('Invalid page URL'));
					}
					
					if ($type != 'track') {
						$m_title = $http->getContent();
						$m_object =
						'<object width="200" height="300" type="application/x-shockwave-flash"'."\n".
						'data="http://widgets.jamendo.com/fr/'.$type.'/?playertype=2008&amp;'.$type.'_id='.$id.'">'."\n".
						'</object>';
						$m_url = $media_page;
					} else {
						$t = explode("\t", $http->getContent());
						$m_title = $t[0];
						$url = $t[1];
						$m_object = dcMedia::mp3player($url,$core->blog->getQmarkURL().'pf=player_mp3.swf');
						$m_url = $media_page;
					}
				}
				break;
			case 'deezer':
				if (preg_match('#/track/(.+?)(&|$)#',$media_path,$m))
				{
					$idSong = $m[1];
					
					if (preg_match('#<title>(.+?) \| Deezer</title>#si',$content,$M)) {
						$m_title = $M[1];
					}
					
					$m_object =
					'<object width="220" height="55"  type="application/x-shockwave-flash"'."\n".
					'  data="http://www.deezer.com/embedded/small-widget-v2.swf?idSong='.$idSong.
					'&amp;colorBackground=0x555552&amp;textColor1=0xFFFFFF&amp;colorVolume=0x39D1FD&amp;autoplay=0">'."\n".
					'</object>';
				}
				break;
		}
	}
	catch (Exception $e)
	{
		$core->error->add($e->getMessage());
	}
}
?>
<html>
<head>
  <title><?php echo __('External media selector') ?></title>
  <script type="text/javascript" src="index.php?pf=externalMedia/popup.js"></script>
</head>

<body>
<?php
echo '<h2>'.__('External media selector').'</h2>';

if (!$m_object)
{
	echo
	'<form action="'.$p_url.'&amp;popup=1" method="post">'.
	'<h3>'.__('Supported media services').'</h3>'.
	'<ul id="supported_media"><li>'.implode('</li><li>',array_keys($services_regs)).'</li></ul>'.
	'<p>'.__('Please enter the URL of the page containing the video you want to include in your post.').'</p>'.
	'<p><label>'.__('Page URL:').' '.
	form::field('media_page',50,250,html::escapeHTML($media_page)).'</label></p>'.
	
	'<p><input type="submit" value="'.__('ok').'" />'.
	$core->formNonce().'</p>'.
	'</form>';
}
else
{
	echo
	'<div style="margin: 1em auto; text-align: center;">'.$m_object.'</div>'.
	'<form id="media-insert-form" action="" method="get">';
	
	$i_align = array(
		'none' => array(__('None'),0),
		'left' => array(__('Left'),0),
		'right' => array(__('Right'),0),
		'center' => array(__('Center'),1)
	);
	
	echo '<h3>'.__('Media alignment').'</h3>';
	echo '<p>';
	foreach ($i_align as $k => $v) {
		echo '<label class="classic">'.
		form::radio(array('alignment'),$k,$v[1]).' '.$v[0].'</label><br /> ';
	}
	echo '</p>';
	
	echo
	'<h3>'.__('Media title').'</h3>'.
	'<p><label>'.__('Title:').' '.
	form::field(array('m_title'),50,250,html::escapeHTML($m_title)).'</label></p>';
	
	echo
	'<p><a id="media-insert-cancel" class="button" href="#">'.__('Cancel').'</a> - '.
	'<strong><a id="media-insert-ok" class="button" href="#">'.__('Insert').'</a></strong>'.
	form::hidden(array('m_object'),html::escapeHTML($m_object)).
	form::hidden(array('m_url'),html::escapeHTML($m_url)).
	'</form>';
}

?>
</body>
</html>