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

class dcMeta
{
	private $core;
	private $con;
	private $table;
	
	public function __construct(&$core)
	{
		$this->core =& $core;
		$this->con =& $this->core->con;
		$this->table = $this->core->prefix.'meta';
	}
	
	public function splitMetaValues($str)
	{
		$res = array();
		foreach (explode(',',$str) as $i => $tag)
		{
			$tag = trim($tag);
			$tag = self::sanitizeMetaID($tag);
			
			if ($tag) {
				$res[$i] = $tag;
			}
		}
		
		return array_unique($res);
	}
	
	public static function sanitizeMetaID($str)
	{
		return text::tidyURL($str,false,true);
	}
	
	public function getMetaArray($str)
	{
		$meta = @unserialize($str);
		
		if (!is_array($meta)) {
			return array();
		}
		
		return $meta;
	}
	
	public function getMetaStr($str,$type)
	{
		$meta = $this->getMetaArray($str);
		
		if (!isset($meta[$type])) {
			return '';
		}
		
		return implode(', ',$meta[$type]);
	}
	
	public function getMetaRecordset($str,$type)
	{
		$meta = $this->getMetaArray($str);
		$data = array();
		
		if (isset($meta[$type]))
		{
			foreach ($meta[$type] as $v)
			{
				$data[] = array(
					'meta_id' => $v,
					'meta_type' => $type,
					'meta_id_lower' => mb_strtolower($v),
					'count' => 0,
					'percent' => 0,
					'roundpercent' => 0
				);
			}
		}
		
		return staticRecord::newFromArray($data);
	}
	
	public static function getMetaRecord(&$core,$str,$type)
	{
		$meta = new self($core);
		return $meta->getMetaRecordset($str,$type);
	}
	
	private function checkPermissionsOnPost($post_id)
	{
		$post_id = (integer) $post_id;
		
		if (!$this->core->auth->check('usage,contentadmin',$this->core->blog->id)) {
			throw new Exception(__('You are not allowed to change this entry status'));
		}
		
		#�If user can only publish, we need to check the post's owner
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id))
		{
			$strReq = 'SELECT post_id '.
					'FROM '.$this->core->prefix.'post '.
					'WHERE post_id = '.$post_id.' '.
					"AND user_id = '".$this->con->escape($this->core->auth->userID())."' ";
			
			$rs = $this->con->select($strReq);
			
			if ($rs->isEmpty()) {
				throw new Exception(__('You are not allowed to change this entry status'));
			}
		}
	}
	
	private function updatePostMeta($post_id)
	{
		$post_id = (integer) $post_id;
		
		$strReq = 'SELECT meta_id, meta_type '.
				'FROM '.$this->table.' '.
				'WHERE post_id = '.$post_id.' ';
		
		$rs = $this->con->select($strReq);
		
		$meta = array();
		while ($rs->fetch()) {
			$meta[$rs->meta_type][] = $rs->meta_id;
		}
		
		$post_meta = serialize($meta);
		
		$cur = $this->con->openCursor($this->core->prefix.'post');
		$cur->post_meta = $post_meta;
		
		$cur->update('WHERE post_id = '.$post_id);
		$this->core->blog->triggerBlog();
	}
	
	public function getPostsByMeta($params=array(),$count_only=false)
	{
		if (!isset($params['meta_id'])) {
			return null;
		}
		
		$params['from'] = ', '.$this->table.' META ';
		$params['sql'] = 'AND META.post_id = P.post_id ';
		
		$params['sql'] .= "AND META.meta_id = '".$this->con->escape($params['meta_id'])."' ";
		
		if (!empty($params['meta_type'])) {
			$params['sql'] .= "AND META.meta_type = '".$this->con->escape($params['meta_type'])."' ";
			unset($params['meta_type']);
		}
		
		unset($params['meta_id']);
		
		return $this->core->blog->getPosts($params,$count_only);
	}
	
	public function getCommentsByMeta($params=array(),$count_only=false)
	{
		if (!isset($params['meta_id'])) {
			return null;
		}
		
		$params['from'] = ', '.$this->table.' META ';
		$params['sql'] = 'AND META.post_id = P.post_id ';
		$params['sql'] .= "AND META.meta_id = '".$this->con->escape($params['meta_id'])."' ";
		
		if (!empty($params['meta_type'])) {
			$params['sql'] .= "AND META.meta_type = '".$this->con->escape($params['meta_type'])."' ";
			unset($params['meta_type']);
		}
		
		return $this->core->blog->getComments($params,$count_only);
	}
	
	public function getMeta($type=null,$limit=null,$meta_id=null,$post_id=null)
	{
		$strReq = 'SELECT meta_id, meta_type, COUNT(M.post_id) as count '.
		'FROM '.$this->table.' M LEFT JOIN '.$this->core->prefix.'post P '.
		'ON M.post_id = P.post_id '.
		"WHERE P.blog_id = '".$this->con->escape($this->core->blog->id)."' ";
		
		if ($type !== null) {
			$strReq .= " AND meta_type = '".$this->con->escape($type)."' ";
		}
		
		if ($meta_id !== null) {
			$strReq .= " AND meta_id = '".$this->con->escape($meta_id)."' ";
		}
		
		if ($post_id !== null) {
			$strReq .= ' AND P.post_id = '.(integer) $post_id.' ';
		}
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$strReq .= 'AND ((post_status = 1 ';
			
			if ($this->core->blog->without_password) {
				$strReq .= 'AND post_password IS NULL ';
			}
			$strReq .= ') ';
			
			if ($this->core->auth->userID()) {
				$strReq .= "OR P.user_id = '".$this->con->escape($this->core->auth->userID())."')";
			} else {
				$strReq .= ') ';
			}
		}
		
		$strReq .=
		'GROUP BY meta_id,meta_type,P.blog_id '.
		'ORDER BY count DESC';
		
		if ($limit) {
			$strReq .= $this->con->limit($limit);
		}
		
		$rs = $this->con->select($strReq);
		$rs = $rs->toStatic();
		
		$max = array();
		while ($rs->fetch())
		{
			$type = $rs->meta_type;
			if (!isset($max[$type])) {
				$max[$type] = $rs->count;
			} else {
				if ($rs->count > $max[$type]) {
					$max[$type] = $rs->count;
				}
			}
		}
		
		while ($rs->fetch())
		{
			$rs->set('meta_id_lower',mb_strtolower($rs->meta_id));
			
			$count = $rs->count;
			$percent = ((integer) $rs->count) * 100 / $max[$rs->meta_type];
			
			$rs->set('percent',(integer) round($percent));
			$rs->set('roundpercent',round($percent/10)*10);
		}
		
		return $rs;
	}
	
	public function setPostMeta($post_id,$type,$value)
	{
		$this->checkPermissionsOnPost($post_id);
		
		$value = trim($value);
		if (!$value) { return; }
		
		$cur = $this->con->openCursor($this->table);
		
		$cur->post_id = (integer) $post_id;
		$cur->meta_id = (string) $value;
		$cur->meta_type = (string) $type;
		
		$cur->insert();
		$this->updatePostMeta((integer) $post_id);
	}
	
	public function delPostMeta($post_id,$type=null,$meta_id=null)
	{
		$post_id = (integer) $post_id;
		
		$this->checkPermissionsOnPost($post_id);
		
		$strReq = 'DELETE FROM '.$this->table.' '.
				'WHERE post_id = '.$post_id;
		
		if ($type !== null) {
			$strReq .= " AND meta_type = '".$this->con->escape($type)."' ";
		}
		
		if ($meta_id !== null) {
			$strReq .= " AND meta_id = '".$this->con->escape($meta_id)."' ";
		}
		
		$this->con->execute($strReq);
		$this->updatePostMeta((integer) $post_id);
	}
	
	public function updateMeta($meta_id,$new_meta_id,$type=null,$post_type=null)
	{
		$new_meta_id = self::sanitizeMetaID($new_meta_id);
		
		if ($new_meta_id == $meta_id) {
			return true;
		}
		
		$getReq = 'SELECT M.post_id '.
				'FROM '.$this->table.' M, '.$this->core->prefix.'post P '.
				'WHERE P.post_id = M.post_id '.
				"AND P.blog_id = '".$this->con->escape($this->core->blog->id)."' ".
				"AND meta_id = '%s' ";
		
		if (!$this->core->auth->check('contentadmin',$this->core->blog->id)) {
			$getReq .= "AND P.user_id = '".$this->con->escape($this->core->auth->userID())."' ";
		}
		if ($post_type !== null) {
			$getReq .= "AND P.post_type = '".$this->con->escape($post_type)."' ";
		}
		
		$delReq = 'DELETE FROM '.$this->table.' '.
				'WHERE post_id IN (%s) '.
				"AND meta_id = '%s' ";
		
		$updReq = 'UPDATE '.$this->table.' '.
				"SET meta_id = '%s' ".
				'WHERE post_id IN (%s) '.
				"AND meta_id = '%s' ";
		
		if ($type !== null) {
			$plus = " AND meta_type = '%s' ";
			$getReq .= $plus;
			$delReq .= $plus;
			$updReq .= $plus;
		}
		
		$to_update = $to_remove = array();
		
		$rs = $this->con->select(sprintf($getReq,$this->con->escape($meta_id),
							$this->con->escape($type)));
		
		while ($rs->fetch()) {
			$to_update[] = $rs->post_id;
		}
		
		if (empty($to_update)) {
			return false;
		}
		
		$rs = $this->con->select(sprintf($getReq,$new_meta_id,$type));
		while ($rs->fetch()) {
			if (in_array($rs->post_id,$to_update)) {
				$to_remove[] = $rs->post_id;
				unset($to_update[array_search($rs->post_id,$to_update)]);
			}
		}
		
		# Delete duplicate meta
		if (!empty($to_remove))
		{
			$this->con->execute(sprintf($delReq,implode(',',$to_remove),
							$this->con->escape($meta_id),
							$this->con->escape($type)));
			
			foreach ($to_remove as $post_id) {
				$this->updatePostMeta($post_id);
			}
		}
		
		# Update meta
		if (!empty($to_update))
		{
			$this->con->execute(sprintf($updReq,$this->con->escape($new_meta_id),
							implode(',',$to_update),
							$this->con->escape($meta_id),
							$this->con->escape($type)));
			
			foreach ($to_update as $post_id) {
				$this->updatePostMeta($post_id);
			}
		}
		
		return true;
	}
	
	public function delMeta($meta_id,$type=null,$post_type=null)
	{
		$strReq = 'SELECT M.post_id '.
				'FROM '.$this->table.' M, '.$this->core->prefix.'post P '.
				'WHERE P.post_id = M.post_id '.
				"AND P.blog_id = '".$this->con->escape($this->core->blog->id)."' ".
				"AND meta_id = '".$this->con->escape($meta_id)."' ";
		
		if ($type !== null) {
			$strReq .= " AND meta_type = '".$this->con->escape($type)."' ";
		}

		if ($post_type !== null) {
			$strReq .= " AND P.post_type = '".$this->con->escape($post_type)."' ";
		}
		
		$rs = $this->con->select($strReq);
		
		$ids = array();
		while ($rs->fetch()) {
			$ids[] = $rs->post_id;
		}
		
		$strReq = 'DELETE FROM '.$this->table.' '.
				'WHERE post_id IN ('.implode(',',$ids).') '.
				"AND meta_id = '".$this->con->escape($meta_id)."' ";
		
		if ($type !== null) {
			$strReq .= " AND meta_type = '".$this->con->escape($type)."' ";
		}
		
		$rs = $this->con->execute($strReq);
		
		foreach ($ids as $post_id) {
			$this->updatePostMeta($post_id);
		}
		
		return $ids;
	}
}
?>
