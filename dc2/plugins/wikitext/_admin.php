<?php
$core->addBehavior('initWidgets',
	array('wikiTextWidget','initWidgets'));
	 
class wikiTextWidget
{
	public static function initWidgets(&$w)
	{
		$w->create('wikiTextWidget',__('Wiki Texte'),array('wikiTextWidget','text'));
		$w->wikiTextWidget->setting('title',__('Title:'),'Du texte','text');
		$w->wikiTextWidget->setting('class',__('Class:'),'','text');
		$w->wikiTextWidget->setting('content',__('Text:'),'','textarea');
		$w->wikiTextWidget->setting('homeonly',__('Home page only'),0,'check');

	}
}
?>
