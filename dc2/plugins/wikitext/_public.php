<?php
$core->addBehavior('initWidgets',
	array('wikiTextWidget','initWidgets'));
	 
class wikiTextWidget
{
	public static function text(&$w)
	{
                global $core;

                if ($w->homeonly && $core->url->type != 'default') {
                        return;
                }
	 	
		$res =
                '<div class="wikitext '.$w->class.'">'.
                ($w->title ? '<h1>'.html::escapeHTML($w->title).'</h1>' : '').
                $core->wikiTransform($w->content).
                '</div>';
		return $res;
	}
}
?>
