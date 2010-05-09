<style type="text/css" media="screen">
@import url(<?php echo context::global_filter($core->blog->settings->themes_url."/".$core->blog->settings->theme,0,0,0,0,0,'BlogThemeURL'); ?>/style.css);
</style>
<style type="text/css" media="print">
@import url(<?php echo context::global_filter($core->blog->settings->themes_url."/".$core->blog->settings->theme,0,0,0,0,0,'BlogThemeURL'); ?>/../default/print.css);
</style>

<script type="text/javascript" src="<?php echo context::global_filter($core->blog->settings->themes_url."/".$core->blog->settings->theme,0,0,0,0,0,'BlogThemeURL'); ?>/../default/js/jquery.js"></script>
<script type="text/javascript" src="<?php echo context::global_filter($core->blog->settings->themes_url."/".$core->blog->settings->theme,0,0,0,0,0,'BlogThemeURL'); ?>/../default/js/jquery.cookie.js"></script>

<?php try { echo $core->tpl->getData('user_head.html'); } catch (Exception $e) {} ?>
<?php if ($core->hasBehavior('publicHeadContent')) { $core->callBehavior('publicHeadContent',$core,$_ctx);} ?>