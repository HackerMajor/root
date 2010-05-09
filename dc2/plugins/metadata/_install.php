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

$version = $core->plugins->moduleInfo('metadata','version');

if (version_compare($core->getVersion('metadata'),$version,'>=')) {
	return;
}

/* Database schema
-------------------------------------------------------- */
$s = new dbStruct($core->con,$core->prefix);

$s->meta
	->meta_id		('varchar',	255,	false)
	->meta_type	('varchar',	64,	false)
	->post_id		('bigint',	0,	false)
	
	->primary('pk_meta','meta_id','meta_type','post_id')
	;

$s->post
	->post_meta	('text',		0,	true,	null)
	;

$s->meta->index('idx_meta_post_id','btree','post_id');
$s->meta->index('idx_meta_meta_type','btree','meta_type');
$s->meta->reference('fk_meta_post','post_id','post','post_id','cascade','cascade');


# Schema installation
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

$core->setVersion('metadata',$version);
return true;
?>