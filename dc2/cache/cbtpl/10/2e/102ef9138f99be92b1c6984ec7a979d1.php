<div id="header">
	<h1><a href="<?php echo context::global_filter($core->blog->url,0,0,0,0,0,'BlogURL'); ?>"><em><?php echo context::global_filter($core->blog->name,1,0,0,0,0,'BlogName'); ?></em> <?php echo context::global_filter($core->blog->settings->editor,1,0,0,0,0,'BlogEditor'); ?></a></span></h1>
  <p><?php echo context::global_filter($core->blog->desc,0,0,0,0,0,'BlogDescription'); ?></p>

  <?php if ($core->hasBehavior('publicTopAfterContent')) { $core->callBehavior('publicTopAfterContent',$core,$_ctx);} ?>
</div>
