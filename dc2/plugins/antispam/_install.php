<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
#
# This file is part of Antispam, a plugin for Dotclear 2.
#
# Copyright (c) 2003-2009 Olivier Meunier and contributors
# Licensed under the GPL version 2.0 license.
# See LICENSE file or
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
#
# -- END LICENSE BLOCK ------------------------------------
if (!defined('DC_CONTEXT_ADMIN')) { return; }

$version = $core->plugins->moduleInfo('antispam','version');
if (version_compare($core->getVersion('antispam'),$version,'>=')) {
	return;
}

/* Database schema
-------------------------------------------------------- */
$s = new dbStruct($core->con,$core->prefix);

$s->spamrule
	->rule_id		('bigint',	0,	false)
	->blog_id		('varchar',	32,	true)
	->rule_type	('varchar',	16,	false,	"'word'")
	->rule_content	('varchar',	128,	false)
	
	->primary('pk_spamrule','rule_id')
	;

$s->spamrule->index('idx_spamrule_blog_id','btree','blog_id');
$s->spamrule->reference('fk_spamrule_blog','blog_id','blog','blog_id','cascade','cascade');

if ($s->driver() == 'pgsql') {
	$s->spamrule->index('idx_spamrule_blog_id_null','btree','(blog_id IS NULL)');
}

# Schema installation
$si = new dbStruct($core->con,$core->prefix);
$changes = $si->synchronize($s);

# Creating default wordslist
if ($core->getVersion('antispam') === null) {
	$_o = new dcFilterWords($core);
	$_o->defaultWordsList();
	unset($_o);
}

$core->setVersion('antispam',$version);
return true;
?>