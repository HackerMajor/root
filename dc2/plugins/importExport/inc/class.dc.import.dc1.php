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

class dcImportDC1 extends dcIeModule
{
	protected $con;
	protected $prefix;
	protected $blog_id;
	
	protected $action = null;
	protected $step = 1;
	
	protected $post_offset = 0;
	protected $post_limit = 20;
	protected $post_count = 0;
	
	protected $has_table = array();
	
	protected $vars;
	protected $base_vars = array(
		'db_host' => '',
		'db_name' => '',
		'db_user' => '',
		'db_pwd' => '',
		'db_prefix' => 'dc_',
		'post_limit' => 20,
		'cat_ids' => array()
	);
	
	protected function setInfo()
	{
		$this->type = 'i';
		$this->name = __('Dotclear 1.2 import');
		$this->description = __('Import a Dotclear 1.2 installation into your current blog.');
	}
	
	public function init()
	{
		$this->con =& $this->core->con;
		$this->prefix = $this->core->prefix;
		$this->blog_id = $this->core->blog->id;
		
		if (!isset($_SESSION['dc1_import_vars'])) {
			$_SESSION['dc1_import_vars'] = $this->base_vars;
		}
		$this->vars =& $_SESSION['dc1_import_vars'];
		
		if ($this->vars['post_limit'] > 0) {
			$this->post_limit = $this->vars['post_limit'];
		}
	}
	
	public function resetVars()
	{
		$this->vars = $this->base_vars;;
		unset($_SESSION['dc1_import_vars']);
	}
	
	public function process($do)
	{
		$this->action = $do;
	}
	
	# We handle process in another way to always display something to
	# user
	protected function guiprocess($do)
	{
		switch ($do)
		{
			case 'step1':
				$this->vars['db_host'] = $_POST['db_host'];
				$this->vars['db_name'] = $_POST['db_name'];
				$this->vars['db_user'] = $_POST['db_user'];
				$this->vars['db_pwd'] = $_POST['db_pwd'];
				$this->vars['post_limit'] = abs((integer) $_POST['post_limit']) > 0 ? $_POST['post_limit'] : 0;
				$this->vars['db_prefix'] = $_POST['db_prefix'];
				$db = $this->db();
				$db->close();
				$this->step = 2;
				echo $this->progressBar(1);
				break;
			case 'step2':
				$this->step = 2;
				$this->importUsers();
				$this->step = 3;
				echo $this->progressBar(3);
				break;
			case 'step3':
				$this->step = 3;
				$this->importCategories();
				if ($this->core->plugins->moduleExists('blogroll')) {
					$this->step = 4;
					echo $this->progressBar(5);
				} else {
					$this->step = 5;
					echo $this->progressBar(7);
				}
				break;
			case 'step4':
				$this->step = 4;
				$this->importLinks();
				$this->step = 5;
				echo $this->progressBar(7);
				break;
			case 'step5':
				$this->step = 5;
				$this->post_offset = !empty($_REQUEST['offset']) ? abs((integer) $_REQUEST['offset']) : 0;
				if ($this->importPosts($percent) === -1) {
					http::redirect($this->getURL().'&do=ok');
				} else {
					echo $this->progressBar(ceil($percent*0.93)+7);
				}
				break;
			case 'ok':
				$this->resetVars();
				$this->core->blog->triggerBlog();
				$this->step = 6;
				echo $this->progressBar(100);
				break;
		}
	}
	
	public function gui()
	{
		try {
			$this->guiprocess($this->action);
		} catch (Exception $e) {
			$this->error($e);
		}
		
		switch ($this->step)
		{
			case 1:
				echo
				'<p>'.sprintf(__('This will import your Dotclear 1.2 content as new content in the current blog: %s.'),
				'<strong>'.html::escapeHTML($this->core->blog->name).'</strong>').'</p>'.
				'<p class="static-msg">'.__('Please note that this process '.
				'will empty your categories, blogroll, entries and comments on the current blog.').'</p>'.
				'<p>'.__('Depending on the size of your blog, it could take a few minutes.').'</p>';
				
				printf($this->imForm(1,__('General information'),__('Import my blog now')),
				'<h3>'.__('We first need some information about your old Dotclear 1.2 installation.').'</h3>'.
				'<p><label>'.__('Database Host Name:').' '.
				form::field('db_host',30,255,html::escapeHTML($this->vars['db_host'])).'</label></p>'.
				'<p><label>'.__('Database Name:',html::escapeHTML($this->vars['db_name'])).' '.
				form::field('db_name',30,255,html::escapeHTML($this->vars['db_name'])).'</label></p>'.
				'<p><label>'.__('Database User Name:').' '.
				form::field('db_user',30,255,html::escapeHTML($this->vars['db_user'])).'</label></p>'.
				'<p><label>'.__('Database Password:').' '.
				form::password('db_pwd',30,255).'</label></p>'.
				'<p><label>'.__('Database Tables Prefix:').' '.
				form::field('db_prefix',30,255,html::escapeHTML($this->vars['db_prefix'])).'</label></p>'.
				'<h3>'.__('Entries import options').'</h3>'.
				'<p><label>'.__('Number of entries to import at once:').' '.
				form::field('post_limit',3,3,html::escapeHTML($this->vars['post_limit'])).'</label></p>'
				);
				break;
			case 2:
				printf($this->imForm(2,__('Importing users')),
					$this->autoSubmit()
				);
				break;
			case 3:
				printf($this->imForm(3,__('Importing categories')),
					$this->autoSubmit()
				);
				break;
			case 4:
				printf($this->imForm(4,__('Importing blogroll')),
					$this->autoSubmit()
				);
				break;
			case 5:
				$t = sprintf(__('Importing entries from %d to %d / %d'),$this->post_offset,
					min(array($this->post_offset+$this->post_limit,$this->post_count)),$this->post_count);
				printf($this->imForm(5,$t),
					form::hidden(array('offset'),$this->post_offset).
					$this->autoSubmit()
				);
				break;
			case 6:
				echo 
				'<h3>'.__('Please read carefully').'</h3>'.
				'<ul>'.
				'<li>'.__('Every newly imported user has received a random password '.
				'and will need to ask for a new one by following the "I forgot my password" link on the login page '.
				'(Their registered email address has to be valid.)').'</li>'.
				
				'<li>'.sprintf(__('Please note that Dotclear 2 has a new URL layout. You can avoid broken '.
				'links by installing <a href="%s">DC1 redirect</a> plugin and activate it in your blog configuration.'),
				'http://www.dotclear.org/extensions').'</li>'.
				'</ul>'.
				
				$this->congratMessage();
				
				break;
		}
	}
	
	# Simple form for step by step process
	protected function imForm($step,$legend,$submit_value=null)
	{
		if (!$submit_value) {
			$submit_value = __('next step').' >';
		}
		
		return
		'<form action="'.$this->getURL(true).'" method="post">'.
		'<fieldset><legend>'.$legend.'</legend>'.
		$this->core->formNonce().
		form::hidden(array('do'),'step'.$step).
		'%s'.
		'<p><input type="submit" value="'.$submit_value.'" /></p>'.
		'</fieldset>'.
		'</form>';
	}
	
	# Error display
	protected function error($e)
	{
		echo '<div class="error"><strong>'.__('Errors:').'</strong>'.
		'<p>'.$e->getMessage().'</p></div>';
	}
	
	# Database init
	protected function db()
	{
		$db = dbLayer::init('mysql',$this->vars['db_host'],$this->vars['db_name'],$this->vars['db_user'],$this->vars['db_pwd']);
		
		$rs = $db->select("SHOW TABLES LIKE '".$this->vars['db_prefix']."%'");
		if ($rs->isEmpty()) {
			throw new Exception(__('Dotclear tables not found'));
		}
		
		while ($rs->fetch()) {
			$this->has_table[$rs->f(0)] = true;
		}
		
		# Set this to read data as they were written in Dotclear 1
		try {
			$db->execute('SET NAMES DEFAULT');
		} catch (Exception $e) {}
		
		$db->execute('SET CHARACTER SET DEFAULT');
		$db->execute("SET COLLATION_CONNECTION = DEFAULT");
		$db->execute("SET COLLATION_SERVER = DEFAULT");
		$db->execute("SET CHARACTER_SET_SERVER = DEFAULT");
		$db->execute("SET CHARACTER_SET_DATABASE = DEFAULT");
		
		$this->post_count = $db->select(
			'SELECT COUNT(ID) FROM '.$this->vars['db_prefix'].'posts '.
			'WHERE post_type = \'post\' OR post_type = \'page\''
		)->f(0);
		
		return $db;
	}
	
	protected function cleanStr($str)
	{
		return text::cleanUTF8(@text::toUTF8($str));
	}
	
	# Users import
	protected function importUsers()
	{
		$db = $this->db();
		$prefix = $this->vars['db_prefix'];
		$rs = $db->select('SELECT * FROM '.$prefix.'user');
		
		try
		{
			$this->con->begin();
			
			while ($rs->fetch())
			{
				if (!$this->core->userExists($rs->user_id))
				{
					$cur = $this->con->openCursor($this->prefix.'user');
					$cur->user_id          = $rs->user_id;
					$cur->user_name        = $rs->user_nom;
					$cur->user_firstname   = $rs->user_prenom;
					$cur->user_displayname = $rs->user_pseudo;
					$cur->user_pwd         = crypt::createPassword();
					$cur->user_email       = $rs->user_email;
					$cur->user_lang        = $rs->user_lang;
					$cur->user_tz          = $this->core->blog->settings->blog_timezone;
					$cur->user_post_status = $rs->user_post_pub ? 1 : -2;
					$cur->user_options     = new ArrayObject(array(
						'edit_size' => (integer) $rs->user_edit_size,
						'post_format' => $rs->user_post_format
					));
					
					$permissions = array();
					switch ($rs->user_level)
					{
						case '0':
							$cur->user_status = 0;
							break;
						case '1': # editor
							$permissions['usage'] = true;
							break;
						case '5': # advanced editor
							$permissions['contentadmin'] = true;
							$permissions['categories'] = true;
							$permissions['media_admin'] = true;
							break;
						case '9': # admin
							$permissions['admin'] = true;
							break;
					}
					
					$this->core->addUser($cur);
					$this->core->setUserBlogPermissions(
						$rs->user_id,
						$this->blog_id,
						$permissions
					);
				}
			}
			
			$this->con->commit();
			$db->close();
		}
		catch (Exception $e)
		{
			$this->con->rollback();
			$db->close();
			throw $e;
		}
	}
	
	# Categories import
	protected function importCategories()
	{
		$db = $this->db();
		$prefix = $this->vars['db_prefix'];
		$rs = $db->select('SELECT * FROM '.$prefix.'categorie ORDER BY cat_ord ASC');
		
		try
		{
			$this->con->execute(
				'DELETE FROM '.$this->prefix.'category '.
				"WHERE blog_id = '".$this->con->escape($this->blog_id)."' "
			);
			
			$ord = 2;
			while ($rs->fetch())
			{
				$cur = $this->con->openCursor($this->prefix.'category');
				$cur->blog_id      = $this->blog_id;
				$cur->cat_title    = $this->cleanStr($rs->cat_libelle);
				$cur->cat_desc     = $this->cleanStr($rs->cat_desc);
				$cur->cat_url      = $this->cleanStr($rs->cat_libelle_url);
				$cur->cat_lft      = $ord++;
				$cur->cat_rgt      = $ord++;
				
				$cur->cat_id = $this->con->select(
					'SELECT MAX(cat_id) FROM '.$this->prefix.'category'
					)->f(0) + 1;
				$this->vars['cat_ids'][$rs->cat_id] = $cur->cat_id;
				$cur->insert();
			}
			
			$db->close();
		}
		catch (Exception $e)
		{
			$db->close();
			throw $e;
		}
	}
	
	# Blogroll import
	protected function importLinks()
	{
		$db = $this->db();
		$prefix = $this->vars['db_prefix'];
		$rs = $db->select('SELECT * FROM '.$prefix.'link ORDER BY link_id ASC');
		
		try
		{
			$this->con->execute(
				'DELETE FROM '.$this->prefix.'link '.
				"WHERE blog_id = '".$this->con->escape($this->blog_id)."' "
			);
			
			while ($rs->fetch())
			{
				$cur = $this->con->openCursor($this->prefix.'link');
				$cur->blog_id       = $this->blog_id;
				$cur->link_href     = $this->cleanStr($rs->href);
				$cur->link_title    = $this->cleanStr($rs->label);
				$cur->link_desc     = $this->cleanStr($rs->title);
				$cur->link_lang     = $this->cleanStr($rs->lang);
				$cur->link_xfn      = $this->cleanStr($rs->rel);
				$cur->link_position = (integer) $rs->position;
				
				$cur->link_id = $this->con->select(
					'SELECT MAX(link_id) FROM '.$this->prefix.'link'
					)->f(0) + 1;
				$cur->insert();
			}
			
			$db->close();
		}
		catch (Exception $e)
		{
			$db->close();
			throw $e;
		}
	}
	
	# Entries import
	protected function importPosts(&$percent)
	{
		$db = $this->db();
		$prefix = $this->vars['db_prefix'];
		
		$count = $db->select('SELECT COUNT(post_id) FROM '.$prefix.'post')->f(0);
		
		$rs = $db->select(
			'SELECT * FROM '.$prefix.'post ORDER BY post_id ASC '.
			$db->limit($this->post_offset,$this->post_limit)
		);
		
		try
		{
			if ($this->post_offset == 0)
			{
				$this->con->execute(
					'DELETE FROM '.$this->prefix.'post '.
					"WHERE blog_id = '".$this->con->escape($this->blog_id)."' "
				);
			}
			
			while ($rs->fetch()) {
				$this->importPost($rs,$db);
			}
			
			$db->close();
		}
		catch (Exception $e)
		{
			$db->close();
			throw $e;
		}
		
		if ($rs->count() < $this->post_limit) {
			return -1;
		} else {
			$this->post_offset += $this->post_limit;
		}
		
		if ($this->post_offset > $this->post_count) {
			$percent = 100;
		} else {
			$percent = $this->post_offset * 100 / $this->post_count;
		}
	}
	
	protected function importPost(&$rs,&$db)
	{
		$cur = $this->con->openCursor($this->prefix.'post');
		$cur->blog_id     = $this->blog_id;
		$cur->user_id     = $rs->user_id;
		$cur->cat_id      = (integer) $this->vars['cat_ids'][$rs->cat_id];
		$cur->post_dt     = $rs->post_dt;
		$cur->post_creadt = $rs->post_creadt;
		$cur->post_upddt  = $rs->post_upddt;
		$cur->post_title  = html::decodeEntities($this->cleanStr($rs->post_titre));
		
		$cur->post_url = date('Y/m/d/',strtotime($cur->post_dt)).$rs->post_id.'-'.$rs->post_titre_url;
		$cur->post_url = substr($cur->post_url,0,255);
		
		$cur->post_format        = $rs->post_content_wiki == '' ? 'xhtml' : 'wiki';
		$cur->post_content_xhtml = $this->cleanStr($rs->post_content);
		$cur->post_excerpt_xhtml = $this->cleanStr($rs->post_chapo);
		
		if ($cur->post_format == 'wiki') {
			$cur->post_content = $this->cleanStr($rs->post_content_wiki);
			$cur->post_excerpt = $this->cleanStr($rs->post_chapo_wiki);
		} else {
			$cur->post_content = $this->cleanStr($rs->post_content);
			$cur->post_excerpt = $this->cleanStr($rs->post_chapo);
		}
		
		$cur->post_notes        = $this->cleanStr($rs->post_notes);
		$cur->post_status       = (integer) $rs->post_pub;
		$cur->post_selected     = (integer) $rs->post_selected;
		$cur->post_open_comment = (integer) $rs->post_open_comment;
		$cur->post_open_tb      = (integer) $rs->post_open_tb;
		$cur->post_lang         = $rs->post_lang;
		
		$cur->post_words = implode(' ',text::splitWords(
			$cur->post_title.' '.
			$cur->post_excerpt_xhtml.' '.
			$cur->post_content_xhtml
		));
		
		$cur->post_id = $this->con->select(
			'SELECT MAX(post_id) FROM '.$this->prefix.'post'
			)->f(0) + 1;
		
		$cur->insert();
		$this->importComments($rs->post_id,$cur->post_id,$db);
		$this->importPings($rs->post_id,$cur->post_id,$db);
		
		# Load meta if we have some in DC1 and metadata plugin in DC2
		if (isset($this->has_table[$this->prefix.'post_meta']) && class_exists('dcMeta')) {
			$this->importMeta($rs->post_id,$cur->post_id,$db);
		}
	}
	
	# Comments import
	protected function importComments($post_id,$new_post_id,&$db)
	{
		$count_c = $count_t = 0;
		
		$rs = $db->select(
			'SELECT * FROM '.$this->vars['db_prefix'].'comment '.
			'WHERE post_id = '.(integer) $post_id.' '
			);
		
		while ($rs->fetch())
		{
			$cur = $this->con->openCursor($this->prefix.'comment');
			$cur->post_id           = (integer) $new_post_id;
			$cur->comment_author    = $this->cleanStr($rs->comment_auteur);
			$cur->comment_status    = (integer) $rs->comment_pub;
			$cur->comment_dt        = $rs->comment_dt;
			$cur->comment_upddt     = $rs->comment_upddt;
			$cur->comment_email     = $this->cleanStr($rs->comment_email);
			$cur->comment_content   = $this->cleanStr($rs->comment_content);
			$cur->comment_ip        = $rs->comment_ip;
			$cur->comment_trackback = (integer) $rs->comment_trackback;
			
			$cur->comment_site = $this->cleanStr($rs->comment_site);
			if ($cur->comment_site != '' && !preg_match('!^http://.*$!',$cur->comment_site)) {
				$cur->comment_site = substr('http://'.$cur->comment_site,0,255);
			}
			
			if ($rs->exists('spam') && $rs->spam && $rs->comment_status = 0) {
				$cur->comment_status = -2;
			}
			
			$cur->comment_words = implode(' ',text::splitWords($cur->comment_content));
			
			$cur->comment_id = $this->con->select(
				'SELECT MAX(comment_id) FROM '.$this->prefix.'comment'
			)->f(0) + 1;
			
			$cur->insert();
			
			if ($cur->comment_trackback && $cur->comment_status == 1) {
				$count_t++;
			} elseif ($cur->comment_status == 1) {
				$count_c++;
			}
		}
		
		if ($count_t > 0 || $count_c > 0)
		{
			$this->con->execute(
				'UPDATE '.$this->prefix.'post SET '.
				'nb_comment = '.$count_c.', '.
				'nb_trackback = '.$count_t.' '.
				'WHERE post_id = '.(integer) $new_post_id.' '
			);
		}
	}
	
	# Pings import
	protected function importPings($post_id,$new_post_id,&$db)
	{
		$urls = array();
		
		$rs = $db->select(
			'SELECT * FROM '.$this->vars['db_prefix'].'ping '.
			'WHERE post_id = '.(integer) $post_id
			);
		
		while ($rs->fetch())
		{
			$url = $this->cleanStr($rs->ping_url);
			if (isset($urls[$url])) {
				continue;
			}
			
			$cur = $this->con->openCursor($this->prefix.'ping');
			$cur->post_id = (integer) $new_post_id;
			$cur->ping_url = $url;
			$cur->ping_dt = $rs->ping_dt;
			$cur->insert();
			
			$urls[$url] = true;
		}
	}
	
	# Meta import
	protected function importMeta($post_id,$new_post_id,&$db)
	{
		$rs = $db->select(
			'SELECT * FROM '.$this->vars['db_prefix'].'post_meta '.
			'WHERE post_id = '.(integer) $post_id.' '.
			"AND meta_key = 'tag' "
			);
		
		if ($rs->isEmpty()) {
			return;
		}
		
		$meta = new dcMeta($this->core);
		while ($rs->fetch()) {
			$meta->setPostMeta($new_post_id,'tag',$this->cleanStr($rs->meta_value));
		}
	}
}
?>