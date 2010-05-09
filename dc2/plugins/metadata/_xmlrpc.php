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
if (!defined('DC_RC_PATH')) { return; }

$core->addBehavior('xmlrpcGetPostInfo',array('metaXMLRPCbehaviors','getPostInfo'));
$core->addBehavior('xmlrpcAfterNewPost',array('metaXMLRPCbehaviors','editPost'));
$core->addBehavior('xmlrpcAfterEditPost',array('metaXMLRPCbehaviors','editPost'));

class metaXMLRPCbehaviors
{
	public static function getPostInfo(&$x,$type,&$res)
	{
		$res =& $res[0];
		
		$meta = new dcMeta($x->core);
		$rs = $meta->getMeta('tag',null,null,$res['postid']);
		
		$m = array();
		while($rs->fetch()) {
			$m[] = $rs->meta_id;
		}
		
		$res['mt_keywords'] = implode(', ',$m);
	}
	
	# Same function for newPost and editPost
	public static function editPost($x,$post_id,&$cur,$content,$struct,$publish)
	{
		# Check if we have mt_keywords in struct
		if (isset($struct['mt_keywords']))
		{
			$meta = new dcMeta($x->core);
			
			$meta->delPostMeta($post_id,'tag');
			
			foreach ($meta->splitMetaValues($struct['mt_keywords']) as $m) {
				$meta->setPostMeta($post_id,'tag',$m);
			}
		}
	}
}
?>