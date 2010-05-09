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

class dcTemplate extends template
{
	private $core;
	private $current_tag;
	
	function __construct($cache_dir,$self_name,&$core)
	{
		parent::__construct($cache_dir,$self_name);
		
		$this->remove_php = !$core->blog->settings->tpl_allow_php;
		$this->use_cache = $core->blog->settings->tpl_use_cache;
		
		$this->tag_block = '<tpl:(%1$s)(?:(\s+.*?)>|>)(.*?)</tpl:%1$s>';
		$this->tag_value = '{{tpl:(%s)(\s(.*?))?}}';
		
		$this->core =& $core;
		
		# Transitional tags
		$this->addValue('EntryTrackbackCount',array($this,'EntryPingCount'));
		$this->addValue('EntryTrackbackData',array($this,'EntryPingData'));
		$this->addValue('EntryTrackbackLink',array($this,'EntryPingLink'));
		
		# l10n
		$this->addValue('lang',array($this,'l10n'));
		
		# Loops test tags
		$this->addBlock('LoopPosition',array($this,'LoopPosition'));
		
		# Archives
		$this->addBlock('Archives',array($this,'Archives'));
		$this->addBlock('ArchivesHeader',array($this,'ArchivesHeader'));
		$this->addBlock('ArchivesFooter',array($this,'ArchivesFooter'));
		$this->addBlock('ArchivesYearHeader',array($this,'ArchivesYearHeader'));
		$this->addBlock('ArchivesYearFooter',array($this,'ArchivesYearFooter'));
		$this->addValue('ArchiveDate',array($this,'ArchiveDate'));
		$this->addBlock('ArchiveNext',array($this,'ArchiveNext'));
		$this->addBlock('ArchivePrevious',array($this,'ArchivePrevious'));
		$this->addValue('ArchiveEntriesCount',array($this,'ArchiveEntriesCount'));
		$this->addValue('ArchiveURL',array($this,'ArchiveURL'));
		
		# Attachments
		$this->addBlock('Attachments',array($this,'Attachments'));
		$this->addBlock('AttachmentsHeader',array($this,'AttachmentsHeader'));
		$this->addBlock('AttachmentsFooter',array($this,'AttachmentsFooter'));
		$this->addValue('AttachmentMimeType',array($this,'AttachmentMimeType'));
		$this->addValue('AttachmentType',array($this,'AttachmentType'));
		$this->addValue('AttachmentFileName',array($this,'AttachmentFileName'));
		$this->addValue('AttachmentSize',array($this,'AttachmentSize'));
		$this->addValue('AttachmentTitle',array($this,'AttachmentTitle'));
		$this->addValue('AttachmentThumbnailURL',array($this,'AttachmentThumbnailURL'));
		$this->addValue('AttachmentURL',array($this,'AttachmentURL'));
		$this->addBlock('AttachmentIf',array($this,'AttachmentIf'));
		$this->addValue('MediaURL',array($this,'MediaURL'));
		
		# Blog
		$this->addValue('BlogArchiveURL',array($this,'BlogArchiveURL'));
		$this->addValue('BlogCopyrightNotice',array($this,'BlogCopyrightNotice'));
		$this->addValue('BlogDescription',array($this,'BlogDescription'));
		$this->addValue('BlogEditor',array($this,'BlogEditor'));
		$this->addValue('BlogFeedID',array($this,'BlogFeedID'));
		$this->addValue('BlogFeedURL',array($this,'BlogFeedURL'));
		$this->addValue('BlogRSDURL',array($this,'BlogRSDURL'));
		$this->addValue('BlogName',array($this,'BlogName'));
		$this->addValue('BlogLanguage',array($this,'BlogLanguage'));
		$this->addValue('BlogThemeURL',array($this,'BlogThemeURL'));
		$this->addValue('BlogUpdateDate',array($this,'BlogUpdateDate'));
		$this->addValue('BlogID',array($this,'BlogID'));
		$this->addValue('BlogURL',array($this,'BlogURL'));
		$this->addValue('BlogPublicURL',array($this,'BlogPublicURL'));
		$this->addValue('BlogQmarkURL',array($this,'BlogQmarkURL'));
		$this->addValue('BlogMetaRobots',array($this,'BlogMetaRobots'));
		
		# Categories
		$this->addBlock('Categories',array($this,'Categories'));
		$this->addBlock('CategoriesHeader',array($this,'CategoriesHeader'));
		$this->addBlock('CategoriesFooter',array($this,'CategoriesFooter'));
		$this->addBlock('CategoryIf',array($this,'CategoryIf'));
		$this->addBlock('CategoryFirstChildren',array($this,'CategoryFirstChildren'));
		$this->addBlock('CategoryParents',array($this,'CategoryParents'));
		$this->addValue('CategoryFeedURL',array($this,'CategoryFeedURL'));
		$this->addValue('CategoryURL',array($this,'CategoryURL'));
		$this->addValue('CategoryShortURL',array($this,'CategoryShortURL'));
		$this->addValue('CategoryDescription',array($this,'CategoryDescription'));
		$this->addValue('CategoryTitle',array($this,'CategoryTitle'));
		
		# Comments
		$this->addBlock('Comments',array($this,'Comments'));
		$this->addValue('CommentAuthor',array($this,'CommentAuthor'));
		$this->addValue('CommentAuthorDomain',array($this,'CommentAuthorDomain'));
		$this->addValue('CommentAuthorLink',array($this,'CommentAuthorLink'));
		$this->addValue('CommentAuthorMailMD5',array($this,'CommentAuthorMailMD5'));
		$this->addValue('CommentAuthorURL',array($this,'CommentAuthorURL'));
		$this->addValue('CommentContent',array($this,'CommentContent'));
		$this->addValue('CommentDate',array($this,'CommentDate'));
		$this->addValue('CommentTime',array($this,'CommentTime'));
		$this->addValue('CommentEmail',array($this,'CommentEmail'));
		$this->addValue('CommentEntryTitle',array($this,'CommentEntryTitle'));
		$this->addValue('CommentFeedID',array($this,'CommentFeedID'));
		$this->addValue('CommentID',array($this,'CommentID'));
		$this->addBlock('CommentIf',array($this,'CommentIf'));
		$this->addValue('CommentIfFirst',array($this,'CommentIfFirst'));
		$this->addValue('CommentIfMe',array($this,'CommentIfMe'));
		$this->addValue('CommentIfOdd',array($this,'CommentIfOdd'));
		$this->addValue('CommentIP',array($this,'CommentIP'));
		$this->addValue('CommentOrderNumber',array($this,'CommentOrderNumber'));
		$this->addBlock('CommentsFooter',array($this,'CommentsFooter'));
		$this->addBlock('CommentsHeader',array($this,'CommentsHeader'));
		$this->addValue('CommentPostURL',array($this,'CommentPostURL'));
		$this->addBlock('IfCommentAuthorEmail',array($this,'IfCommentAuthorEmail'));
		
		# Comment preview
		$this->addBlock('IfCommentPreview',array($this,'IfCommentPreview'));
		$this->addValue('CommentPreviewName',array($this,'CommentPreviewName'));
		$this->addValue('CommentPreviewEmail',array($this,'CommentPreviewEmail'));
		$this->addValue('CommentPreviewSite',array($this,'CommentPreviewSite'));
		$this->addValue('CommentPreviewContent',array($this,'CommentPreviewContent'));
		$this->addValue('CommentPreviewCheckRemember',array($this,'CommentPreviewCheckRemember'));
		
		# Entries
		$this->addBlock('DateFooter',array($this,'DateFooter'));
		$this->addBlock('DateHeader',array($this,'DateHeader'));
		$this->addBlock('Entries',array($this,'Entries'));
		$this->addBlock('EntriesFooter',array($this,'EntriesFooter'));
		$this->addBlock('EntriesHeader',array($this,'EntriesHeader'));
		$this->addValue('EntryExcerpt',array($this,'EntryExcerpt'));
		$this->addValue('EntryAttachmentCount',array($this,'EntryAttachmentCount'));
		$this->addValue('EntryAuthorCommonName',array($this,'EntryAuthorCommonName'));
		$this->addValue('EntryAuthorDisplayName',array($this,'EntryAuthorDisplayName'));
		$this->addValue('EntryAuthorEmail',array($this,'EntryAuthorEmail'));
		$this->addValue('EntryAuthorID',array($this,'EntryAuthorID'));
		$this->addValue('EntryAuthorLink',array($this,'EntryAuthorLink'));
		$this->addValue('EntryAuthorURL',array($this,'EntryAuthorURL'));
		$this->addValue('EntryBasename',array($this,'EntryBasename'));
		$this->addValue('EntryCategory',array($this,'EntryCategory'));
		$this->addBlock('EntryCategoriesBreadcrumb',array($this,'EntryCategoriesBreadcrumb'));
		$this->addValue('EntryCategoryID',array($this,'EntryCategoryID'));
		$this->addValue('EntryCategoryURL',array($this,'EntryCategoryURL'));
		$this->addValue('EntryCategoryShortURL',array($this,'EntryCategoryShortURL'));
		$this->addValue('EntryCommentCount',array($this,'EntryCommentCount'));
		$this->addValue('EntryContent',array($this,'EntryContent'));
		$this->addValue('EntryDate',array($this,'EntryDate'));
		$this->addValue('EntryFeedID',array($this,'EntryFeedID'));
		$this->addValue('EntryFirstImage',array($this,'EntryFirstImage'));
		$this->addValue('EntryID',array($this,'EntryID'));
		$this->addBlock('EntryIf',array($this,'EntryIf'));
		$this->addValue('EntryIfFirst',array($this,'EntryIfFirst'));
		$this->addValue('EntryIfOdd',array($this,'EntryIfOdd'));
		$this->addValue('EntryIfSelected',array($this,'EntryIfSelected'));
		$this->addValue('EntryLang',array($this,'EntryLang'));
		$this->addBlock('EntryNext',array($this,'EntryNext'));
		$this->addValue('EntryPingCount',array($this,'EntryPingCount'));
		$this->addValue('EntryPingData',array($this,'EntryPingData'));
		$this->addValue('EntryPingLink',array($this,'EntryPingLink'));
		$this->addBlock('EntryPrevious',array($this,'EntryPrevious'));
		$this->addValue('EntryTitle',array($this,'EntryTitle'));
		$this->addValue('EntryTime',array($this,'EntryTime'));
		$this->addValue('EntryURL',array($this,'EntryURL'));
		
		# Languages
		$this->addBlock('Languages',array($this,'Languages'));
		$this->addBlock('LanguagesHeader',array($this,'LanguagesHeader'));
		$this->addBlock('LanguagesFooter',array($this,'LanguagesFooter'));
		$this->addValue('LanguageCode',array($this,'LanguageCode'));
		$this->addBlock('LanguageIfCurrent',array($this,'LanguageIfCurrent'));
		$this->addValue('LanguageURL',array($this,'LanguageURL'));
		
		# Pagination
		$this->addBlock('Pagination',array($this,'Pagination'));
		$this->addValue('PaginationCounter',array($this,'PaginationCounter'));
		$this->addValue('PaginationCurrent',array($this,'PaginationCurrent'));
		$this->addBlock('PaginationIf',array($this,'PaginationIf'));
		$this->addValue('PaginationURL',array($this,'PaginationURL'));
		
		# Trackbacks
		$this->addValue('PingBlogName',array($this,'PingBlogName'));
		$this->addValue('PingContent',array($this,'PingContent'));
		$this->addValue('PingDate',array($this,'PingDate'));
		$this->addValue('PingEntryTitle',array($this,'PingEntryTitle'));
		$this->addValue('PingFeedID',array($this,'PingFeedID'));
		$this->addValue('PingID',array($this,'PingID'));
		$this->addValue('PingIfFirst',array($this,'PingIfFirst'));
		$this->addValue('PingIfOdd',array($this,'PingIfOdd'));
		$this->addValue('PingIP',array($this,'PingIP'));
		$this->addValue('PingNoFollow',array($this,'PingNoFollow'));
		$this->addValue('PingOrderNumber',array($this,'PingOrderNumber'));
		$this->addValue('PingPostURL',array($this,'PingPostURL'));
		$this->addBlock('Pings',array($this,'Pings'));
		$this->addBlock('PingsFooter',array($this,'PingsFooter'));
		$this->addBlock('PingsHeader',array($this,'PingsHeader'));
		$this->addValue('PingTime',array($this,'PingTime'));
		$this->addValue('PingTitle',array($this,'PingTitle'));
		$this->addValue('PingAuthorURL',array($this,'PingAuthorURL'));
		
		# System
		$this->addValue('SysBehavior',array($this,'SysBehavior'));
		$this->addBlock('SysIf',array($this,'SysIf'));
		$this->addBlock('SysIfCommentPublished',array($this,'SysIfCommentPublished'));
		$this->addBlock('SysIfCommentPending',array($this,'SysIfCommentPending'));
		$this->addBlock('SysIfFormError',array($this,'SysIfFormError'));
		$this->addValue('SysFeedSubtitle',array($this,'SysFeedSubtitle'));
		$this->addValue('SysFormError',array($this,'SysFormError'));
		$this->addValue('SysPoweredBy',array($this,'SysPoweredBy'));
		$this->addValue('SysSearchString',array($this,'SysSearchString'));
		$this->addValue('SysSelfURI',array($this,'SysSelfURI'));
	}
	
	public function getData($________)
	{
		# --BEHAVIOR-- tplBeforeData
		if ($this->core->hasBehavior('tplBeforeData'))
		{
			self::$_r = $this->core->callBehavior('tplBeforeData',$this->core);
			if (self::$_r) {
				return self::$_r;
			}
		}
		
		parent::getData($________);
		
		# --BEHAVIOR-- tplAfterData
		if ($this->core->hasBehavior('tplAfterData')) {
			$this->core->callBehavior('tplAfterData',$this->core,self::$_r);
		}
		
		return self::$_r;
	}
	
	protected function compileBlock($match)
	{
		$this->current_tag = $match[1];
		$content = $match[3];
		$attr = new ArrayObject($this->getAttrs($match[2]));
		
		# --BEHAVIOR-- templateBeforeBlock
		$res = $this->core->callBehavior('templateBeforeBlock',$this->core,$this->current_tag,$attr);
		
		$res .= call_user_func($this->blocks[$this->current_tag],$attr,$content);
		
		# --BEHAVIOR-- templateAfterBlock
		$res .= $this->core->callBehavior('templateAfterBlock',$this->core,$this->current_tag,$attr);
		
		return $res;
	}
	
	protected function compileValue($match)
	{
		$this->current_tag = $match[1];
		
		$str_attr = '';
		$attr = new ArrayObject();
		if (isset($match[2])) {
			$str_attr = $match[2];
			$attr = new ArrayObject($this->getAttrs($match[2]));
		}
		
		# --BEHAVIOR-- templateBeforeValue
		$res = $this->core->callBehavior('templateBeforeValue',$this->core,$this->current_tag,$attr);
		
		$res .= call_user_func($this->values[$this->current_tag],$attr,ltrim($str_attr));
		
		# --BEHAVIOR-- templateAfterValue
		$res .= $this->core->callBehavior('templateAfterValue',$this->core,$this->current_tag,$attr);
		
		return $res;
	}
	
	public function getFilters($attr)
	{
		$p[0] = '0';	# encode_xml
		$p[1] = '0';	# remove_html
		$p[2] = '0';	# cut_string
		$p[3] = '0';	# lower_case
		$p[4] = '0';	# upper_case
		
		$p[0] = (integer) (!empty($attr['encode_xml']) || !empty($attr['encode_html']));
		$p[1] = (integer) !empty($attr['remove_html']);
		
		if (!empty($attr['cut_string']) && (integer) $attr['cut_string'] > 0) {
			$p[2] = (integer) $attr['cut_string'];
		}
		
		$p[3] = (integer) !empty($attr['lower_case']);
		$p[4] = (integer) !empty($attr['upper_case']);
		
		return "context::global_filter(%s,".implode(",",$p).",'".addslashes($this->current_tag)."')";
	}
	
	protected function getOperator($op)
	{
		switch (strtolower($op))
		{
			case 'or':
			case '||':
				return '||';
			case 'and':
			case '&&':
			default:
				return '&&';
		}
	}
	
	/* TEMPLATE FUNCTIONS
	------------------------------------------------------- */
	
	public function l10n($attr,$str_attr)
	{
		# Normalize content
		$str_attr = preg_replace('/\s+/x',' ',$str_attr);
		
		return "<?php echo __('".str_replace("'","\\'",$str_attr)."'); ?>";
	}
	
	public function LoopPosition($attr,$content)
	{
		$start = isset($attr['start']) ? (integer) $attr['start'] : '0';
		$length = isset($attr['length']) ? (integer) $attr['length'] : 'null';
		$even = isset($attr['even']) ? (integer) (boolean) $attr['even'] : 'null';
		
		if ($start > 0) {
			$start--;
		}
		
		return
		'<?php if ($_ctx->loopPosition('.$start.','.$length.','.$even.')) : ?>'.
		$content.
		"<?php endif; ?>";
	}
	
	
	/* Archives ------------------------------------------- */
	/*dtd
	<!ELEMENT tpl:Archives - - -- Archives dates loop -->
	<!ATTLIST tpl:Archives
	type		(day|month|year)	#IMPLIED	-- Get days, months or years, default to month --
	category	CDATA			#IMPLIED  -- Get dates of given category --
	no_context (1|0)			#IMPLIED  -- Override context information
	order	(asc|desc)		#IMPLIED  -- Sort asc or desc --
	post_type	CDATA			#IMPLIED  -- Get dates of given type of entries, default to post --
	post_lang	CDATA		#IMPLIED  -- Filter on the given language
	>
	*/
	public function Archives($attr,$content)
	{
		$p = '$params = array();';
		$p .= "\$params['type'] = 'month';\n";
		if (isset($attr['type'])) {
			$p .= "\$params['type'] = '".addslashes($attr['type'])."';\n";
		}
		
		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
		}

		$p .= "\$params['post_type'] = 'post';\n";
		if (isset($attr['post_type'])) {
			$p .= "\$params['post_type'] = '".addslashes($attr['post_type'])."';\n";
		}

		if (isset($attr['post_lang'])) {
			$p .= "\$params['post_lang'] = '".addslashes($attr['post_lang'])."';\n";
		}
		
		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";
		}
		
		$order = 'desc';
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$p .= "\$params['order'] = '".$attr['order']."';\n ";
		}
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->archives = $core->blog->getDates($params); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->archives->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->archives = null; ?>';
		
		return $res;
	}
	
	/*dtd
	<!ELEMENT tpl:ArchivesHeader - - -- First archives result container -->
	*/
	public function ArchivesHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->archives->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:ArchivesFooter - - -- Last archives result container -->
	*/
	public function ArchivesFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->archives->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:ArchivesYearHeader - - -- First result of year in archives container -->
	*/
	public function ArchivesYearHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->archives->yearHeader()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:ArchivesYearFooter - - -- Last result of year in archives container -->
	*/
	public function ArchivesYearFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->archives->yearFooter()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:ArchiveDate - O -- Archive result date -->
	<!ATTLIST tpl:ArchiveDate
	format	CDATA	#IMPLIED  -- Date format (Default %B %Y) --
	>
	*/
	public function ArchiveDate($attr)
	{
		$format = '%B %Y';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"dt::dt2str('".$format."',\$_ctx->archives->dt)").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:ArchiveEntriesCount - O -- Current archive result number of entries -->
	*/
	public function ArchiveEntriesCount($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->archives->nb_post').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:ArchiveNext - - -- Next archive result container -->
	<!ATTLIST tpl:ArchiveNext
	type		(day|month|year)	#IMPLIED	-- Get days, months or years, default to month --
	post_type	CDATA			#IMPLIED  -- Get dates of given type of entries, default to post --
	post_lang	CDATA		#IMPLIED  -- Filter on the given language
	>
	*/
	public function ArchiveNext($attr,$content)
	{
		$p = '$params = array();';
		$p .= "\$params['type'] = 'month';\n";
		if (isset($attr['type'])) {
			$p .= "\$params['type'] = '".addslashes($attr['type'])."';\n";
		}
		
		$p .= "\$params['post_type'] = 'post';\n";
		if (isset($attr['post_type'])) {
			$p .= "\$params['post_type'] = '".addslashes($attr['post_type'])."';\n";
		}
		
		if (isset($attr['post_lang'])) {
			$p .= "\$params['post_lang'] = '".addslashes($attr['post_lang'])."';\n";
		}

		$p .= "\$params['next'] = \$_ctx->archives->dt;";
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->archives = $core->blog->getDates($params); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->archives->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->archives = null; ?>';
		
		return $res;
	}
	
	/*dtd
	<!ELEMENT tpl:ArchivePrevious - - -- Previous archive result container -->
	<!ATTLIST tpl:ArchivePrevious
	type		(day|month|year)	#IMPLIED	-- Get days, months or years, default to month --
	post_type	CDATA			#IMPLIED  -- Get dates of given type of entries, default to post --
	post_lang	CDATA		#IMPLIED  -- Filter on the given language
	>
	*/
	public function ArchivePrevious($attr,$content)
	{
		$p = '$params = array();';
		$p .= "\$params['type'] = 'month';\n";
		if (isset($attr['type'])) {
			$p .= "\$params['type'] = '".addslashes($attr['type'])."';\n";
		}
		
		$p .= "\$params['post_type'] = 'post';\n";
		if (isset($attr['post_type'])) {
			$p .= "\$params['post_type'] = '".addslashes($attr['post_type'])."';\n";
		}

		if (isset($attr['post_lang'])) {
			$p .= "\$params['post_lang'] = '".addslashes($attr['post_lang'])."';\n";
		}

		$p .= "\$params['previous'] = \$_ctx->archives->dt;";
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->archives = $core->blog->getDates($params); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->archives->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->archives = null; ?>';
		
		return $res;
	}
	
	/*dtd
	<!ELEMENT tpl:ArchiveURL - O -- Current archive result URL -->
	*/
	public function ArchiveURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->archives->url($core)').'; ?>';
	}
	
	/* Attachments ---------------------------------------- */
	/*dtd
	<!ELEMENT tpl:Attachments - - -- Post Attachments loop -->
	*/
	public function Attachments($attr,$content)
	{
		$res =
		"<?php\n".
		'if ($_ctx->posts !== null && $core->media) {'."\n".
			'$_ctx->attachments = new ArrayObject($core->media->getPostMedia($_ctx->posts->post_id));'."\n".
		"?>\n".
		
		'<?php foreach ($_ctx->attachments as $attach_i => $attach_f) : '.
		'$GLOBALS[\'attach_i\'] = $attach_i; $GLOBALS[\'attach_f\'] = $attach_f;'.
		'$_ctx->file_url = $attach_f->file_url; ?>'.
		$content.
		'<?php endforeach; $_ctx->attachments = null; unset($attach_i,$attach_f,$_ctx->file_url); ?>'.
		
		"<?php } ?>\n";
		
		return $res;
	}
	
	/*dtd
	<!ELEMENT tpl:AttachmentsHeader - - -- First attachments result container -->
	*/
	public function AttachmentsHeader($attr,$content)
	{
		return
		"<?php if (\$attach_i == 0) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:AttachmentsFooter - - -- Last attachments result container -->
	*/
	public function AttachmentsFooter($attr,$content)
	{
		return
		"<?php if (\$attach_i+1 == count(\$_ctx->attachments)) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:AttachmentsIf - - -- Test on attachment fields -->
	<!ATTLIST tpl:AttachmentIf
	is_image	(0|1)	#IMPLIED	-- test if attachment is an image (value : 1) or not (value : 0)
	has_thumb	(0|1)	#IMPLIED	-- test if attachment has a square thumnail (value : 1) or not (value : 0)
	is_mp3	(0|1)	#IMPLIED	-- test if attachment is a mp3 file (value : 1) or not (value : 0)
	is_flv	(0|1)	#IMPLIED	-- test if attachment is a flv file (value : 1) or not (value : 0)
	>
	*/
	public function AttachmentIf($attr,$content)
	{
		$if = array();
		
		$operator = isset($attr['operator']) ? $this->getOperator($attr['operator']) : '&&';
		
		if (isset($attr['is_image'])) {
			$sign = (boolean) $attr['is_image'] ? '' : '!';
			$if[] = $sign.'$attach_f->media_image';
		}
		
		if (isset($attr['has_thumb'])) {
			$sign = (boolean) $attr['has_thumb'] ? '' : '!';
			$if[] = $sign.'isset($attach_f->media_thumb[\'sq\'])';
		}
		
		if (isset($attr['is_mp3'])) {
			$sign = (boolean) $attr['is_mp3'] ? '==' : '!=';
			$if[] = '$attach_f->type '.$sign.' "audio/mpeg3"';
		}
		
		if (isset($attr['is_flv'])) {
			$sign = (boolean) $attr['is_flv'] ? '' : '!';
			$if[] = $sign.
				'($attach_f->type == "video/x-flv" || '.
				'$attach_f->type == "video/mp4" || '.
				'$attach_f->type == "video/x-m4v")';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/*dtd
	<!ELEMENT tpl:AttachmentMimeType - O -- Attachment MIME Type -->
	*/
	public function AttachmentMimeType($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$attach_f->type').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:AttachmentType - O -- Attachment type -->
	*/
	public function AttachmentType($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$attach_f->media_type').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:AttachmentFileName - O -- Attachment file name -->
	*/
	public function AttachmentFileName($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$attach_f->basename').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:AttachmentSize - O -- Attachment size -->
	<!ATTLIST tpl:AttachmentSize
	full	CDATA	#IMPLIED	-- if set, size is rounded to a human-readable value (in KB, MB, GB, TB)
	>
	*/
	public function AttachmentSize($attr)
	{
		$f = $this->getFilters($attr);
		if (!empty($attr['full'])) {
			return '<?php echo '.sprintf($f,'$attach_f->size').'; ?>';
		}
		return '<?php echo '.sprintf($f,'files::size($attach_f->size)').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:AttachmentTitle - O -- Attachment title -->
	*/
	public function AttachmentTitle($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$attach_f->media_title').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:AttachmentThumbnailURL - O -- Attachment square thumbnail URL -->
	*/
	public function AttachmentThumbnailURL($attr)
	{
		$f = $this->getFilters($attr);
		return
		'<?php '.
		'if (isset($attach_f->media_thumb[\'sq\'])) {'.
			'echo '.sprintf($f,'$attach_f->media_thumb[\'sq\']').';'.
		'}'.
		'?>';
	}
	
	/*dtd
	<!ELEMENT tpl:AttachmentURL - O -- Attachment URL -->
	*/
	public function AttachmentURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$attach_f->file_url').'; ?>';
	}
	
	public function MediaURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->file_url').'; ?>';
	}
	
	/* Blog ----------------------------------------------- */
	/*dtd
	<!ELEMENT tpl:BlogArchiveURL - O -- Blog Archives URL -->
	*/
	public function BlogArchiveURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("archive")').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:BlogCopyrightNotice - O -- Blog copyrght notices -->
	*/
	public function BlogCopyrightNotice($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->settings->copyright_notice').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:BlogDescription - O -- Blog Description -->
	*/
	public function BlogDescription($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->desc').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:BlogEditor - O -- Blog Editor -->
	*/
	public function BlogEditor($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->settings->editor').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:BlogFeedID - O -- Blog Feed ID -->
	*/
	public function BlogFeedID($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'"urn:md5:".$core->blog->uid').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:BlogFeedURL - O -- Blog Feed URL -->
	<!ATTLIST tpl:BlogFeedURL
	type	(rss2|atom)	#IMPLIED	-- feed type (default : rss2)
	>
	*/
	public function BlogFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'atom';
		
		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'atom';
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("feed")."/'.$type.'"').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:BlogName - O -- Blog Name -->
	*/
	public function BlogName($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->name').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:BlogLanguage - O -- Blog Language -->
	*/
	public function BlogLanguage($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->settings->lang').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:BlogThemeURL - O -- Blog's current Themei URL -->
	*/
	public function BlogThemeURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->settings->themes_url."/".$core->blog->settings->theme').'; ?>';
	}

	/*dtd
	<!ELEMENT tpl:BlogPublicURL - O -- Blog Public directory URL -->
	*/
	public function BlogPublicURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->settings->public_url').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:BlogUpdateDate - O -- Blog last update date -->
	<!ATTLIST tpl:BlogUpdateDate
	format	CDATA	#IMPLIED	-- date format (encoded in dc:str by default if iso8601 or rfc822 not specified)
	iso8601	CDATA	#IMPLIED	-- if set, tells that date format is ISO 8601
	rfc822	CDATA	#IMPLIED	-- if set, tells that date format is RFC 822
	>
	*/
	public function BlogUpdateDate($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		} else {
			$format = '%Y-%m-%d %H:%M:%S';
		}
		
		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);
		
		$f = $this->getFilters($attr);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"dt::rfc822(\$core->blog->upddt,\$core->blog->settings->blog_timezone)").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"dt::iso8601(\$core->blog->upddt,\$core->blog->settings->blog_timezone)").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"dt::str('".$format."',\$core->blog->upddt)").'; ?>';
		}
	}
	
	/*dtd
	<!ELEMENT tpl:BlogID - 0 -- Blog ID -->
	*/
	public function BlogID($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->id').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:BlogRSDURL - O -- Blog RSD URL -->
	*/
	public function BlogRSDURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase(\'rsd\')').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:BlogURL - O -- Blog URL -->
	*/
	public function BlogURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:BlogQmarkURL - O -- Blog URL, ending with a question mark -->
	*/
	public function BlogQmarkURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->getQmarkURL()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:BlogMetaRobots - O -- Blog meta robots tag definition, overrides robots_policy setting -->
	<!ATTLIST tpl:BlogMetaRobots
	robots	CDATA	#IMPLIED	-- can be INDEX,FOLLOW,NOINDEX,NOFOLLOW,ARCHIVE,NOARCHIVE
	>
	*/
	public function BlogMetaRobots($attr)
	{
		$robots = isset($attr['robots']) ? addslashes($attr['robots']) : '';
		return "<?php echo context::robotsPolicy(\$core->blog->settings->robots_policy,'".$robots."'); ?>";
	}
	
	/* Categories ----------------------------------------- */
	
	/*dtd
	<!ELEMENT tpl:Categories - - -- Categories loop -->
	*/
	public function Categories($attr,$content)
	{
		$p = "\$params = array();\n";
		
		if (isset($attr['url'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['url'])."';\n";
		}
		
		if (!empty($attr['post_type'])) {
			$p .= "\$params['post_type'] = '".addslashes($attr['post_type'])."';\n";
		}
		
		if (!empty($attr['level'])) {
			$p .= "\$params['level'] = ".(integer) $attr['level'].";\n";
		}
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->categories = $core->blog->getCategories($params);'."\n";
		$res .= "?>\n";
		$res .= '<?php while ($_ctx->categories->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->categories = null; unset($params); ?>';
		
		return $res;
	}
	
	/*dtd
	<!ELEMENT tpl:CategoriesHeader - - -- First Categories result container -->
	*/
	public function CategoriesHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->categories->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:CategoriesFooter - - -- Last Categories result container -->
	*/
	public function CategoriesFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->categories->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:CategoryIf - - -- tests on current entry -->
	<!ATTLIST tpl:CategoryIf
	url		CDATA	#IMPLIED	-- category has given url
	has_entries	(0|1)	#IMPLIED	-- post is the first post from list (value : 1) or not (value : 0)
	>
	*/
	public function CategoryIf($attr,$content)
	{
		$if = array();
		$operator = isset($attr['operator']) ? $this->getOperator($attr['operator']) : '&&';
		
		if (isset($attr['url'])) {
			$url = addslashes(trim($attr['url']));
			if (substr($url,0,1) == '!') {
				$url = substr($url,1);
				$if[] = '($_ctx->categories->cat_url != "'.$url.'")';
			} else {
				$if[] = '($_ctx->categories->cat_url == "'.$url.'")';
			}
		}
		
		if (isset($attr['has_entries'])) {
			$sign = (boolean) $attr['has_entries'] ? '>' : '==';
			$if[] = '$_ctx->categories->nb_post '.$sign.' 0';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/*dtd
	<!ELEMENT tpl:CategoryFirstChildren - - -- Current category first children loop -->
	*/
	public function CategoryFirstChildren($attr,$content)
	{
		return
		"<?php\n".
		'$_ctx->categories = $core->blog->getCategoryFirstChildren($_ctx->categories->cat_id);'."\n".
		'while ($_ctx->categories->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->categories = null; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CategoryParents - - -- Current category parents loop -->
	*/
	public function CategoryParents($attr,$content)
	{
		return
		"<?php\n".
		'$_ctx->categories = $core->blog->getCategoryParents($_ctx->categories->cat_id);'."\n".
		'while ($_ctx->categories->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->categories = null; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CategoryFeedURL - O -- Category feed URL -->
	<!ATTLIST tpl:CategoryFeedURL
	type	(rss2|atom)	#IMPLIED	-- feed type (default : rss2)
	>
	*/
	public function CategoryFeedURL($attr)
	{
		$type = !empty($attr['type']) ? $attr['type'] : 'atom';
		
		if (!preg_match('#^(rss2|atom)$#',$type)) {
			$type = 'atom';
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("feed")."/category/".'.
		'$_ctx->categories->cat_url."/'.$type.'"').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CategoryURL - O -- Category URL (complete iabsolute URL, including blog URL) -->
	*/
	public function CategoryURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("category")."/".$_ctx->categories->cat_url').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CategoryShortURL - O -- Category short URL (relative URL, from /category/) -->
	*/
	public function CategoryShortURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->categories->cat_url').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CategoryDescription - O -- Category description -->
	*/
	public function CategoryDescription($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->categories->cat_desc').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CategoryTitle - O -- Category title -->
	*/
	public function CategoryTitle($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->categories->cat_title').'; ?>';
	}
	
	/* Entries -------------------------------------------- */
	/*dtd
	<!ELEMENT tpl:Entries - - -- Blog Entries loop -->
	<!ATTLIST tpl:Entries
	lastn	CDATA	#IMPLIED	-- limit number of results to specified value
	author	CDATA	#IMPLIED	-- get entries for a given user id
	category	CDATA	#IMPLIED	-- get entries for specific categories only (multiple comma-separated categories can be specified. Use "!" as prefix to exclude a category)
	no_category	CDATA	#IMPLIED	-- get entries without category
	no_context (1|0)	#IMPLIED  -- Override context information
	sortby	(title|selected|author|date|id)	#IMPLIED	-- specify entries sort criteria (default : date)
	order	(desc|asc)	#IMPLIED	-- specify entries order (default : desc)
	no_content	(0|1)	#IMPLIED	-- do not retrieve entries content
	selected	(0|1)	#IMPLIED	-- retrieve posts marked as selected only (value: 1) or not selected only (value: 0)
	url		CDATA	#IMPLIED	-- retrieve post by its url
	type		CDATA	#IMPLIED	-- retrieve post with given post_type (there can be many ones separated by comma)
	ignore_pagination	(0|1)	#IMPLIED	-- ignore page number provided in URL (useful when using multiple tpl:Entries on the same page)
	>
	*/
	public function Entries($attr,$content)
	{
		$lastn = -1;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		$p = 'if (!isset($_page_number)) { $_page_number = 1; }'."\n";
		
		if ($lastn != 0) {
			if ($lastn > 0) {
				$p .= "\$params['limit'] = ".$lastn.";\n";
			} else {
				$p .= "\$params['limit'] = \$_ctx->nb_entry_per_page;\n";
			}
			
			if (!isset($attr['ignore_pagination']) || $attr['ignore_pagination'] == "0") {
				$p .= "\$params['limit'] = array(((\$_page_number-1)*\$params['limit']),\$params['limit']);\n";
			} else {
				$p .= "\$params['limit'] = array(0, \$params['limit']);\n";
			}
		}
		
		if (isset($attr['author'])) {
			$p .= "\$params['user_id'] = '".addslashes($attr['author'])."';\n";
		}
		
		if (isset($attr['category'])) {
			$p .= "\$params['cat_url'] = '".addslashes($attr['category'])."';\n";
			$p .= "context::categoryPostParam(\$params);\n";
		}
		
		if (isset($attr['no_category'])) {
			$p .= "@\$params['sql'] .= ' AND P.cat_id IS NULL ';\n";
			$p .= "unset(\$params['cat_url']);\n";
		}
		
		if (!empty($attr['type'])) {
			$p .= "\$params['post_type'] = preg_split('/\s*,\s*/','".addslashes($attr['type'])."',-1,PREG_SPLIT_NO_EMPTY);\n";
		}
		
		if (!empty($attr['url'])) {
			$p .= "\$params['post_url'] = '".addslashes($attr['url'])."';\n";
		}
		
		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("users")) { '.
				"\$params['user_id'] = \$_ctx->users->user_id; ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("archives")) { '.
				"\$params['post_year'] = \$_ctx->archives->year(); ".
				"\$params['post_month'] = \$_ctx->archives->month(); ".
				"unset(\$params['limit']); ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("langs")) { '.
				"\$params['post_lang'] = \$_ctx->langs->post_lang; ".
			"}\n";
			
			$p .=
			'if (isset($_search)) { '.
				"\$params['search'] = \$_search; ".
			"}\n";
		}
		
		
		
		$sortby = 'post_dt';
		$order = 'desc';
		if (isset($attr['sortby'])) {
			switch ($attr['sortby']) {
				case 'title': $sortby = 'post_title'; break;
				case 'selected' : $sortby = 'post_selected'; break;
				case 'author' : $sortby = 'user_id'; break;
				case 'date' : $sortby = 'post_dt'; break;
				case 'id' : $sortby = 'post_id'; break;
			}
		}
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}
		
		$p .= "\$params['order'] = '".$sortby." ".$order."';\n";
		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
		
		if (isset($attr['selected'])) {
			$p .= "\$params['post_selected'] = ".(integer) (boolean) $attr['selected'].";";
		}
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->post_params = $params;'."\n";
		$res .= '$_ctx->posts = $core->blog->getPosts($params); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->posts->fetch()) : ?>'.$content.'<?php endwhile; '.
		'$_ctx->posts = null; $_ctx->post_params = null; ?>';
		
		return $res;
	}
	
	/*dtd
	<!ELEMENT tpl:DateHeader - O -- Displays date, if post is the first post of the given day -->
	*/
	public function DateHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->posts->firstPostOfDay()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:DateFooter - O -- Displays date,  if post is the last post of the given day -->
	*/
	public function DateFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->posts->lastPostOfDay()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryIf - - -- tests on current entry -->
	<!ATTLIST tpl:EntryIf
	type	CDATA	#IMPLIED	-- post has a given type (default: "post")
	category	CDATA	#IMPLIED	-- post has a given category
	first	(0|1)	#IMPLIED	-- post is the first post from list (value : 1) or not (value : 0)
	odd	(0|1)	#IMPLIED	-- post is in an odd position (value : 1) or not (value : 0)
	even	(0|1)	#IMPLIED	-- post is in an even position (value : 1) or not (value : 0)
	extended	(0|1)	#IMPLIED	-- post has an excerpt (value : 1) or not (value : 0)
	selected	(0|1)	#IMPLIED	-- post is selected (value : 1) or not (value : 0)
	has_category	(0|1)	#IMPLIED	-- post has a category (value : 1) or not (value : 0)
	has_attachment	(0|1)	#IMPLIED	-- post has attachments (value : 1) or not (value : 0)
	comments_active	(0|1)	#IMPLIED	-- comments are active for this post (value : 1) or not (value : 0)
	pings_active	(0|1)	#IMPLIED	-- trackbacks are active for this post (value : 1) or not (value : 0)
	show_comments	(0|1)	#IMPLIED	-- there are comments for this post (value : 1) or not (value : 0)
	show_pings	(0|1)	#IMPLIED	-- there are trackbacks for this post (value : 1) or not (value : 0)
	operator	(and|or)	#IMPLIED	-- combination of conditions, if more than 1 specifiec (default: and)
	url		CDATA	#IMPLIED	-- post has given url
	>
	*/
	public function EntryIf($attr,$content)
	{
		$if = array();
		$extended = null;
		$hascategory = null;
		
		$operator = isset($attr['operator']) ? $this->getOperator($attr['operator']) : '&&';

		if (isset($attr['type'])) {
			$type = trim($attr['type']);
			$type = !empty($type)?$type:'post';
			$if[] = '$_ctx->posts->post_type == "'.addslashes($type).'"';
		}
		
		if (isset($attr['url'])) {
			$url = trim($attr['url']);
			if (substr($url,0,1) == '!') {
				$url = substr($url,1);
				$if[] = '$_ctx->posts->post_url != "'.addslashes($url).'"';
			} else {
				$if[] = '$_ctx->posts->post_url == "'.addslashes($url).'"';
			}
		}
		
		if (isset($attr['category'])) {
			$category = addslashes(trim($attr['category']));
			if (substr($category,0,1) == '!') {
				$category = substr($category,1);
				$if[] = '($_ctx->posts->cat_url != "'.$category.'")';
			} else {
				$if[] = '($_ctx->posts->cat_url == "'.$category.'")';
			}
		}
		
		if (isset($attr['first'])) {
			$sign = (boolean) $attr['first'] ? '=' : '!';
			$if[] = '$_ctx->posts->index() '.$sign.'= 0';
		}
		
		if (isset($attr['odd'])) {
			$sign = (boolean) $attr['odd'] ? '=' : '!';
			$if[] = '($_ctx->posts->index()+1)%2 '.$sign.'= 1';
		}
		
		if (isset($attr['extended'])) {
			$sign = (boolean) $attr['extended'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->isExtended()';
		}
		
		if (isset($attr['selected'])) {
			$sign = (boolean) $attr['selected'] ? '' : '!';
			$if[] = $sign.'(boolean)$_ctx->posts->post_selected';
		}
		
		if (isset($attr['has_category'])) {
			$sign = (boolean) $attr['has_category'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->cat_id';
		}
		
		if (isset($attr['has_attachment'])) {
			$sign = (boolean) $attr['has_attachment'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->countMedia()';
		}
		
		if (isset($attr['comments_active'])) {
			$sign = (boolean) $attr['comments_active'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->commentsActive()';
		}
		
		if (isset($attr['pings_active'])) {
			$sign = (boolean) $attr['pings_active'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->trackbacksActive()';
		}
		
		if (isset($attr['has_comment'])) {
			$sign = (boolean) $attr['has_comment'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->hasComments()';
		}
		
		if (isset($attr['has_ping'])) {
			$sign = (boolean) $attr['has_ping'] ? '' : '!';
			$if[] = $sign.'$_ctx->posts->hasTrackbacks()';
		}
		
		if (isset($attr['show_comments'])) {
			if ((boolean) $attr['show_comments']) {
				$if[] = '($_ctx->posts->hasComments() || $_ctx->posts->commentsActive())';
			} else {
				$if[] = '(!$_ctx->posts->hasComments() && !$_ctx->posts->commentsActive())';
			}
		}
		
		if (isset($attr['show_pings'])) {
			if ((boolean) $attr['show_pings']) {
				$if[] = '($_ctx->posts->hasTrackbacks() || $_ctx->posts->trackbacksActive())';
			} else {
				$if[] = '(!$_ctx->posts->hasTrackbacks() && !$_ctx->posts->trackbacksActive())';
			}
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/*dtd
	<!ELEMENT tpl:EntryIfFirst - O -- displays value if entry is the first one -->
	<!ATTLIST tpl:EntryIfFirst
	return	CDATA	#IMPLIED	-- value to display in case of success (default: first)
	>
	*/
	public function EntryIfFirst($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'first';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->posts->index() == 0) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryIfOdd - O -- displays value if entry is in an odd position -->
	<!ATTLIST tpl:EntryIfOdd
	return	CDATA	#IMPLIED	-- value to display in case of success (default: odd)
	>
	*/
	public function EntryIfOdd($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->posts->index()+1)%2 == 1) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryIfSelected - O -- displays value if entry is selected -->
	<!ATTLIST tpl:EntryIfSelected
	return	CDATA	#IMPLIED	-- value to display in case of success (default: selected)
	>
	*/
	public function EntryIfSelected($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'selected';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->posts->post_selected) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryContent - O -- Entry content -->
	<!ATTLIST tpl:EntryContent
	absolute_urls	CDATA	#IMPLIED -- transforms local URLs to absolute one
	full			(1|0)	#IMPLIED -- returns full content with excerpt
	>
	*/
	public function EntryContent($attr)
	{
		$urls = '0';
		if (!empty($attr['absolute_urls'])) {
			$urls = '1';
		}
		
		$f = $this->getFilters($attr);
		
		if (!empty($attr['full'])) {
			return '<?php echo '.sprintf($f,
				'$_ctx->posts->getExcerpt('.$urls.')." ".$_ctx->posts->getContent('.$urls.')').'; ?>';
		} else {
			return '<?php echo '.sprintf($f,'$_ctx->posts->getContent('.$urls.')').'; ?>';
		}
	}
	
	/*dtd
	<!ELEMENT tpl:EntryExcerpt - O -- Entry excerpt -->
	<!ATTLIST tpl:EntryExcerpt
	absolute_urls	CDATA	#IMPLIED -- transforms local URLs to absolute one
	>
	*/
	public function EntryExcerpt($attr)
	{
		$urls = '0';
		if (!empty($attr['absolute_urls'])) {
			$urls = '1';
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getExcerpt('.$urls.')').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAttachmentCount - O -- Number of attachments for entry -->
	<!ATTLIST tpl:EntryAttachmentCount
	none	CDATA	#IMPLIED	-- text to display for "no attachment" (default: no attachment)
	one	CDATA	#IMPLIED	-- text to display for "one attachment" (default: one attachment)
	more	CDATA	#IMPLIED	-- text to display for "more attachment" (default: %s attachment, %s is replaced by the number of attachments)
	>
	*/
	public function EntryAttachmentCount($attr)
	{
		$none = 'no attachment';
		$one = 'one attachment';
		$more = '%d attachments';
		
		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}
		
		return
		"<?php if (\$_ctx->posts->countMedia() == 0) {\n".
		"  printf(__('".$none."'),(integer) \$_ctx->posts->countMedia());\n".
		"} elseif (\$_ctx->posts->countMedia() == 1) {\n".
		"  printf(__('".$one."'),(integer) \$_ctx->posts->countMedia());\n".
		"} else {\n".
		"  printf(__('".$more."'),(integer) \$_ctx->posts->countMedia());\n".
		"} ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAuthorCommonName - O -- Entry author common name -->
	*/
	public function EntryAuthorCommonName($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getAuthorCN()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAuthorDisplayName - O -- Entry author display name -->
	*/
	public function EntryAuthorDisplayName($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->user_displayname').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAuthorID - O -- Entry author ID -->
	*/
	public function EntryAuthorID($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->user_id').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAuthorEmail - O -- Entry author email -->
	<!ATTLIST tpl:EntryAuthorEmail
	spam_protected	(0|1)	#IMPLIED	-- protect email from spam (default: 1)
	>
	*/
	public function EntryAuthorEmail($attr)
	{
		$p = 'true';
		if (isset($attr['spam_protected']) && !$attr['spam_protected']) {
			$p = 'false';
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->posts->getAuthorEmail(".$p.")").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAuthorLink - O -- Entry author link -->
	*/
	public function EntryAuthorLink($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getAuthorLink()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryAuthorURL - O -- Entry author URL -->
	*/
	public function EntryAuthorURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->user_url').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryBasename - O -- Entry short URL (relative to /post) -->
	*/
	public function EntryBasename($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->post_url').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryCategory - O -- Entry category (full name) -->
	*/
	public function EntryCategory($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->cat_title').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryCategoriesBreadcrumb - - -- Current entry parents loop (without last one) -->
	*/
	public function EntryCategoriesBreadcrumb($attr,$content)
	{
		return
		"<?php\n".
		'$_ctx->categories = $core->blog->getCategoryParents($_ctx->posts->cat_id);'."\n".
		'while ($_ctx->categories->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->categories = null; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryCategoryID - O -- Entry category ID -->
	*/
	public function EntryCategoryID($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->cat_id').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryCategoryURL - O -- Entry category URL -->
	*/
	public function EntryCategoryURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getCategoryURL()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryCategoryShortURL - O -- Entry category short URL (relative URL, from /category/) -->
	*/
	public function EntryCategoryShortURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->cat_url').'; ?>';
	}
	
	
	/*dtd
	<!ELEMENT tpl:EntryFeedID - O -- Entry feed ID -->
	*/
	public function EntryFeedID($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getFeedID()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryFirstImage - O -- Extracts entry first image if exists -->
	<!ATTLIST tpl:EntryAuthorEmail
	size			(sq|t|s|m|o)	#IMPLIED	-- Image size to extract
	class		CDATA		#IMPLIED	-- Class to add on image tag
	with_category	(1|0)		#IMPLIED	-- Search in entry category description if present (default 0)
	>
	*/
	public function EntryFirstImage($attr)
	{
		$size = !empty($attr['size']) ? $attr['size'] : '';
		$class = !empty($attr['class']) ? $attr['class'] : '';
		$with_category = !empty($attr['with_category']) ? 'true' : 'false';
		
		return "<?php echo context::EntryFirstImageHelper('".addslashes($size)."',".$with_category.",'".addslashes($class)."'); ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryID - O -- Entry ID -->
	*/
	public function EntryID($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->post_id').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryLang - O --  Entry language or blog lang if not defined -->
	*/
	public function EntryLang($attr)
	{
		$f = $this->getFilters($attr);
		return
		'<?php if ($_ctx->posts->post_lang) { '.
			'echo '.sprintf($f,'$_ctx->posts->post_lang').'; '.
		'} else {'.
			'echo '.sprintf($f,'$core->blog->settings->lang').'; '.
		'} ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryNext - - -- Next entry block -->
	<!ATTLIST tpl:EntryNext
	restrict_to_category	(0|1)	#IMPLIED	-- find next post in the same category (default: 0)
	restrict_to_lang		(0|1)	#IMPLIED	-- find next post in the same language (default: 0)
	>
	*/
	public function EntryNext($attr,$content)
	{
		$restrict_to_category = !empty($attr['restrict_to_category']) ? '1' : '0';
		$restrict_to_lang = !empty($attr['restrict_to_lang']) ? '1' : '0';
		
		return
		'<?php $next_post = $core->blog->getNextPost($_ctx->posts,1,'.$restrict_to_category.','.$restrict_to_lang.'); ?>'."\n".
		'<?php if ($next_post !== null) : ?>'.
			
			'<?php $_ctx->posts = $next_post; unset($next_post);'."\n".
			'while ($_ctx->posts->fetch()) : ?>'.
			$content.
			'<?php endwhile; $_ctx->posts = null; ?>'.
		"<?php endif; ?>\n";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryPrevious - - -- Previous entry block -->
	<!ATTLIST tpl:EntryPrevious
	restrict_to_category	(0|1)	#IMPLIED	-- find previous post in the same category (default: 0)
	restrict_to_lang		(0|1)	#IMPLIED	-- find next post in the same language (default: 0)
	>
	*/
	public function EntryPrevious($attr,$content)
	{
		$restrict_to_category = !empty($attr['restrict_to_category']) ? '1' : '0';
		$restrict_to_lang = !empty($attr['restrict_to_lang']) ? '1' : '0';
		
		return
		'<?php $prev_post = $core->blog->getNextPost($_ctx->posts,-1,'.$restrict_to_category.','.$restrict_to_lang.'); ?>'."\n".
		'<?php if ($prev_post !== null) : ?>'.
			
			'<?php $_ctx->posts = $prev_post; unset($prev_post);'."\n".
			'while ($_ctx->posts->fetch()) : ?>'.
			$content.
			'<?php endwhile; $_ctx->posts = null; ?>'.
		"<?php endif; ?>\n";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryTitle - O -- Entry title -->
	*/
	public function EntryTitle($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->post_title').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryURL - O -- Entry URL -->
	*/
	public function EntryURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->posts->getURL()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntryDate - O -- Entry date -->
	<!ATTLIST tpl:EntryDate
	format	CDATA	#IMPLIED	-- date format (encoded in dc:str by default if iso8601 or rfc822 not specified)
	iso8601	CDATA	#IMPLIED	-- if set, tells that date format is ISO 8601
	rfc822	CDATA	#IMPLIED	-- if set, tells that date format is RFC 822
	>
	*/
	public function EntryDate($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);
		
		$f = $this->getFilters($attr);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"\$_ctx->posts->getRFC822Date()").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"\$_ctx->posts->getISO8601Date()").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"\$_ctx->posts->getDate('".$format."')").'; ?>';
		}
	}
	
	/*dtd
	<!ELEMENT tpl:EntryTime - O -- Entry date -->
	<!ATTLIST tpl:EntryTime
	format	CDATA	#IMPLIED	-- time format 
	>
	*/
	public function EntryTime($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->posts->getTime('".$format."')").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:EntriesHeader - - -- First entries result container -->
	*/
	public function EntriesHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->posts->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntriesFooter - - -- Last entries result container -->
	*/
	public function EntriesFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->posts->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryCommentCount - O -- Number of comments for entry -->
	<!ATTLIST tpl:EntryCommentCount
	none		CDATA	#IMPLIED	-- text to display for "no comment" (default: no comment)
	one		CDATA	#IMPLIED	-- text to display for "one comment" (default: one comment)
	more		CDATA	#IMPLIED	-- text to display for "more comments" (default: %s comments, %s is replaced by the number of comment)
	count_all	CDATA	#IMPLIED	-- count comments and trackbacks
	>
	*/
	public function EntryCommentCount($attr)
	{
		$none = 'no comment';
		$one = 'one comment';
		$more = '%d comments';
		
		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}
		
		if (empty($attr['count_all'])) {
			$operation = '$_ctx->posts->nb_comment';
		} else {
			$operation = '($_ctx->posts->nb_comment + $_ctx->posts->nb_trackback)';
		}
		
		return
		"<?php if (".$operation." == 0) {\n".
		"  printf(__('".$none."'),".$operation.");\n".
		"} elseif (".$operation." == 1) {\n".
		"  printf(__('".$one."'),".$operation.");\n".
		"} else {\n".
		"  printf(__('".$more."'),".$operation.");\n".
		"} ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryPingCount - O -- Number of trackbacks for entry -->
	<!ATTLIST tpl:EntryPingCount
	none	CDATA	#IMPLIED	-- text to display for "no ping" (default: no ping)
	one	CDATA	#IMPLIED	-- text to display for "one ping" (default: one ping)
	more	CDATA	#IMPLIED	-- text to display for "more pings" (default: %s trackbacks, %s is replaced by the number of pings)
	>
	*/
	public function EntryPingCount($attr)
	{
		$none = 'no trackback';
		$one = 'one trackback';
		$more = '%d trackbacks';
		
		if (isset($attr['none'])) {
			$none = addslashes($attr['none']);
		}
		if (isset($attr['one'])) {
			$one = addslashes($attr['one']);
		}
		if (isset($attr['more'])) {
			$more = addslashes($attr['more']);
		}
		
		return
		"<?php if (\$_ctx->posts->nb_trackback == 0) {\n".
		"  printf(__('".$none."'),(integer) \$_ctx->posts->nb_trackback);\n".
		"} elseif (\$_ctx->posts->nb_trackback == 1) {\n".
		"  printf(__('".$one."'),(integer) \$_ctx->posts->nb_trackback);\n".
		"} else {\n".
		"  printf(__('".$more."'),(integer) \$_ctx->posts->nb_trackback);\n".
		"} ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryPingData - O -- Display trackback RDF information -->
	*/
	public function EntryPingData($attr)
	{
		return "<?php if (\$_ctx->posts->trackbacksActive()) { echo \$_ctx->posts->getTrackbackData(); } ?>\n";
	}
	
	/*dtd
	<!ELEMENT tpl:EntryPingLink - O -- Entry trackback link -->
	*/
	public function EntryPingLink($attr)
	{
		return "<?php if (\$_ctx->posts->trackbacksActive()) { echo \$_ctx->posts->getTrackbackLink(); } ?>\n";
	}
	
	/* Languages -------------------------------------- */
	/*dtd
	<!ELEMENT tpl:Languages - - -- Languages loop -->
	<!ATTLIST tpl:Languages
	lang	CDATA	#IMPLIED	-- restrict loop on given lang
	order	(desc|asc)	#IMPLIED	-- languages ordering (default: desc)
	>
	*/
	public function Languages($attr,$content)
	{
		$p = '$params = array();';
		
		if (isset($attr['lang'])) {
			$p = "\$params['lang'] = '".addslashes($attr['lang'])."';\n";
		}
		
		$order = 'desc';
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$p .= "\$params['order'] = '".$attr['order']."';\n ";
		}
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->langs = $core->blog->getLangs($params); unset($params);'."\n";
		$res .= "?>\n";
		
		$res .=
		'<?php if ($_ctx->langs->count() > 1) : '.
		'while ($_ctx->langs->fetch()) : ?>'.$content.
		'<?php endwhile; $_ctx->langs = null; endif; ?>';
		
		return $res;
	}
	
	/*dtd
	<!ELEMENT tpl:LanguagesHeader - - -- First languages result container -->
	*/
	public function LanguagesHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->langs->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:LanguagesFooter - - -- Last languages result container -->
	*/
	public function LanguagesFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->langs->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:LanguageCode - O -- Language code -->
	*/
	public function LanguageCode($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->langs->post_lang').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:LanguageIfCurrent - - -- tests if post language is current language -->
	*/
	public function LanguageIfCurrent($attr,$content)
	{
		return
		"<?php if (\$_ctx->cur_lang == \$_ctx->langs->post_lang) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:LanguageURL - O -- Language URL -->
	*/
	public function LanguageURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$core->blog->url.$core->url->getBase("lang").$_ctx->langs->post_lang').'; ?>';
	}
	
	/* Pagination ------------------------------------- */
	/*dtd
	<!ELEMENT tpl:Pagination - - -- Pagination container -->
	<!ATTLIST tpl:Pagination
	no_context	(0|1)	#IMPLIED	-- override test on posts count vs number of posts per page
	>
	*/
	public function Pagination($attr,$content)
	{
		$p = "<?php\n";
		$p .= '$params = $_ctx->post_params;'."\n";
		$p .= '$_ctx->pagination = $core->blog->getPosts($params,true); unset($params);'."\n";
		$p .= "?>\n";
		
		if (isset($attr['no_context'])) {
			return $p.$content;
		}

		return
		$p.
		'<?php if ($_ctx->pagination->f(0) > $_ctx->posts->count()) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:PaginationCounter - O -- Number of pages -->
	*/
	public function PaginationCounter($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"context::PaginationNbPages()").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:PaginationCurrent - O -- current page -->
	*/
	public function PaginationCurrent($attr)
	{
		$offset = 0;
		if (isset($attr['offset'])) {
			$offset = (integer) $attr['offset'];
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"context::PaginationPosition(".$offset.")").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:PaginationIf - - -- pages tests -->
	<!ATTLIST tpl:PaginationIf
	start	(0|1)	#IMPLIED	-- test if we are at first page (value : 1) or not (value : 0)
	end	(0|1)	#IMPLIED	-- test if we are at last page (value : 1) or not (value : 0)
	>
	*/
	public function PaginationIf($attr,$content)
	{
		$if = array();
		
		if (isset($attr['start'])) {
			$sign = (boolean) $attr['start'] ? '' : '!';
			$if[] = $sign.'context::PaginationStart()';
		}
		
		if (isset($attr['end'])) {
			$sign = (boolean) $attr['end'] ? '' : '!';
			$if[] = $sign.'context::PaginationEnd()';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' && ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/*dtd
	<!ELEMENT tpl:PaginationURL - O -- link to previoux/next page -->
	<!ATTLIST tpl:PaginationURL
	offset	CDATA	#IMPLIED	-- page offset (negative for previous pages), default: 0
	>
	*/
	public function PaginationURL($attr)
	{
		$offset = 0;
		if (isset($attr['offset'])) {
			$offset = (integer) $attr['offset'];
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"context::PaginationURL(".$offset.")").'; ?>';
	}
	
	/* Comments --------------------------------------- */
	/*dtd
	<!ELEMENT tpl:Comments - - -- Comments container -->
	<!ATTLIST tpl:Comments
	with_pings	(0|1)	#IMPLIED	-- include trackbacks in request
	lastn	CDATA	#IMPLIED	-- restrict the number of entries 
	no_context (1|0)		#IMPLIED  -- Override context information
	order	(desc|asc)	#IMPLIED	-- result ordering (default: asc)
	>
	*/
	public function Comments($attr,$content)
	{
		$p =
		"if (\$_ctx->posts !== null) { ".
			"\$params['post_id'] = \$_ctx->posts->post_id; ".
			"\$core->blog->withoutPassword(false);\n".
		"}\n";
		
		if (empty($attr['with_pings'])) {
			$p .= "\$params['comment_trackback'] = false;\n";
		}
		
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "if (\$_ctx->nb_comment_per_page !== null) { \$params['limit'] = \$_ctx->nb_comment_per_page; }\n";
		}
		
		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("langs")) { '.
				"\$params['sql'] = \"AND P.post_lang = '\".\$core->blog->con->escape(\$_ctx->langs->post_lang).\"' \"; ".
			"}\n";
		}
		
		$order = 'asc';
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}
		
		$p .= "\$params['order'] = 'comment_dt ".$order."';\n";
		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->comments = $core->blog->getComments($params); unset($params);'."\n";
		$res .= "if (\$_ctx->posts !== null) { \$core->blog->withoutPassword(true);}\n";
		
		if (!empty($attr['with_pings'])) {
			$res .= '$_ctx->pings = $_ctx->comments;'."\n";
		}
		
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->comments->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->comments = null; ?>';
		
		return $res;
	}
	
	/*dtd
	<!ELEMENT tpl:CommentAuthor - O -- Comment author -->
	*/
	public function CommentAuthor($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->comments->comment_author").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentAuthorDomain - O -- Comment author website domain -->
	*/
	public function CommentAuthorDomain($attr)
	{
		return '<?php echo preg_replace("#^http(?:s?)://(.+?)/.*$#msu",\'$1\',$_ctx->comments->comment_site); ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentAuthorLink - O -- Comment author link -->
	*/
	public function CommentAuthorLink($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comments->getAuthorLink()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentAuthorMailMD5 - O -- Comment author email MD5 sum -->
	*/
	public function CommentAuthorMailMD5($attr)
	{
		return '<?php echo md5($_ctx->comments->comment_email) ; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentAuthorURL - O -- Comment author URL -->
	*/
	public function CommentAuthorURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comments->getAuthorURL()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentContent - O --  Comment content -->
	<!ATTLIST tpl:CommentContent
	absolute_urls	(0|1)	#IMPLIED	-- convert URLS to absolute urls
	>
	*/
	public function CommentContent($attr)
	{
		$urls = '0';
		if (!empty($attr['absolute_urls'])) {
			$urls = '1';
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comments->getContent('.$urls.')').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentDate - O -- Comment date -->
	<!ATTLIST tpl:CommentDate
	format	CDATA	#IMPLIED	-- date format (encoded in dc:str by default if iso8601 or rfc822 not specified)
	iso8601	CDATA	#IMPLIED	-- if set, tells that date format is ISO 8601
	rfc822	CDATA	#IMPLIED	-- if set, tells that date format is RFC 822
	>
	*/
	public function CommentDate($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);
		
		$f = $this->getFilters($attr);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"\$_ctx->comments->getRFC822Date()").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"\$_ctx->comments->getISO8601Date()").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"\$_ctx->comments->getDate('".$format."')").'; ?>';
		}
	}
	
	/*dtd
	<!ELEMENT tpl:CommentTime - O -- Comment date -->
	<!ATTLIST tpl:CommentTime
	format	CDATA	#IMPLIED	-- time format 
	>
	*/
	public function CommentTime($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->comments->getTime('".$format."')").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentEmail - O -- Comment author email -->
	<!ATTLIST tpl:CommentEmail
	spam_protected	(0|1)	#IMPLIED	-- protect email from spam (default: 1)
	>
	*/
	public function CommentEmail($attr)
	{
		$p = 'true';
		if (isset($attr['spam_protected']) && !$attr['spam_protected']) {
			$p = 'false';
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->comments->getEmail(".$p.")").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentEntryTitle - O -- Title of the comment entry -->
	*/
	public function CommentEntryTitle($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comments->post_title').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentFeedID - O -- Comment feed ID -->
	*/
	public function CommentFeedID($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comments->getFeedID()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentID - O -- Comment ID -->
	*/
	public function CommentID($attr)
	{
		return '<?php echo $_ctx->comments->comment_id; ?>';
	}

	/*dtd
	<!ELEMENT tpl:CommentIf - - -- test container for comments -->
	<!ATTLIST tpl:CommentIf
	is_ping	(0|1)	#IMPLIED	-- test if comment is a trackback (value : 1) or not (value : 0)
	>
	*/
	public function CommentIf($attr,$content)
	{
		$if = array();
		$is_ping = null;
		
		if (isset($attr['is_ping'])) {
			$sign = (boolean) $attr['is_ping'] ? '' : '!';
			$if[] = $sign.'$_ctx->comments->comment_trackback';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' && ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/*dtd
	<!ELEMENT tpl:CommentIfFirst - O -- displays value if comment is the first one -->
	<!ATTLIST tpl:CommentIfFirst
	return	CDATA	#IMPLIED	-- value to display in case of success (default: first)
	>
	*/
	public function CommentIfFirst($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'first';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->comments->index() == 0) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:CommentIfMe - O -- displays value if comment is the from the entry author -->
	<!ATTLIST tpl:CommentIfMe
	return	CDATA	#IMPLIED	-- value to display in case of success (default: me)
	>
	*/
	public function CommentIfMe($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'me';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->comments->isMe()) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:CommentIfOdd - O -- displays value if comment is  at an odd position -->
	<!ATTLIST tpl:CommentIfOdd
	return	CDATA	#IMPLIED	-- value to display in case of success (default: odd)
	>
	*/
	public function CommentIfOdd($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->comments->index()+1)%2) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:CommentIP - O -- Comment author IP -->
	*/
	public function CommentIP($attr)
	{
		return '<?php echo $_ctx->comments->comment_ip; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentOrderNumber - O -- Comment order in page -->
	*/
	public function CommentOrderNumber($attr)
	{
		return '<?php echo $_ctx->comments->index()+1; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentsFooter - - -- Last comments result container -->
	*/
	public function CommentsFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->comments->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:CommentsHeader - - -- First comments result container -->
	*/
	public function CommentsHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->comments->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:CommentPostURL - O -- Comment Entry URL -->
	*/
	public function CommentPostURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comments->getPostURL()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:IfCommentAuthorEmail - - -- Container displayed if comment author email is set -->
	*/
	public function IfCommentAuthorEmail($attr,$content)
	{
		return
		"<?php if (\$_ctx->comments->comment_email) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/* Comment preview -------------------------------- */
	/*dtd
	<!ELEMENT tpl:IfCommentPreview - - -- Container displayed if comment is being previewed -->
	*/
	public function IfCommentPreview($attr,$content)
	{
		return
		'<?php if ($_ctx->comment_preview !== null && $_ctx->comment_preview["preview"]) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentPreviewName - O -- Author name for the previewed comment -->
	*/
	public function CommentPreviewName($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comment_preview["name"]').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentPreviewEmail - O -- Author email for the previewed comment -->
	*/
	public function CommentPreviewEmail($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comment_preview["mail"]').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentPreviewSite - O -- Author site for the previewed comment -->
	*/
	public function CommentPreviewSite($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->comment_preview["site"]').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentPreviewContent - O -- Content of the previewed comment -->
	<!ATTLIST tpl:CommentPreviewContent
	raw	(0|1)	#IMPLIED	-- display comment in raw content
	>
	*/
	public function CommentPreviewContent($attr)
	{
		$f = $this->getFilters($attr);
		
		if (!empty($attr['raw'])) {
			$co = '$_ctx->comment_preview["rawcontent"]';
		} else {
			$co = '$_ctx->comment_preview["content"]';
		}
		
		return '<?php echo '.sprintf($f,$co).'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:CommentPreviewCheckRemember - O -- checkbox attribute for "remember me" (same value as before preview) -->
	*/
	public function CommentPreviewCheckRemember($attr)
	{
		return
		"<?php if (\$_ctx->comment_preview['remember']) { echo ' checked=\"checked\"'; } ?>";
	}
	
	/* Trackbacks ------------------------------------- */
	/*dtd
	<!ELEMENT tpl:PingBlogName - O -- Trackback blog name -->
	*/
	public function PingBlogName($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->pings->comment_author').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:PingContent - O -- Trackback content -->
	*/
	public function PingContent($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->pings->getTrackbackContent()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:PingDate - O -- Trackback date -->
	<!ATTLIST tpl:PingDate
	format	CDATA	#IMPLIED	-- date format (encoded in dc:str by default if iso8601 or rfc822 not specified)
	iso8601	CDATA	#IMPLIED	-- if set, tells that date format is ISO 8601
	rfc822	CDATA	#IMPLIED	-- if set, tells that date format is RFC 822
	>
	*/
	public function PingDate($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$iso8601 = !empty($attr['iso8601']);
		$rfc822 = !empty($attr['rfc822']);
		
		$f = $this->getFilters($attr);
		
		if ($rfc822) {
			return '<?php echo '.sprintf($f,"\$_ctx->pings->getRFC822Date()").'; ?>';
		} elseif ($iso8601) {
			return '<?php echo '.sprintf($f,"\$_ctx->pings->getISO8601Date()").'; ?>';
		} else {
			return '<?php echo '.sprintf($f,"\$_ctx->pings->getDate('".$format."')").'; ?>';
		}
	}
	
	/*dtd
	<!ELEMENT tpl:PingTime - O -- Trackback date -->
	<!ATTLIST tpl:PingTime
	format	CDATA	#IMPLIED	-- time format 
	>
	*/
	public function PingTime($attr)
	{
		$format = '';
		if (!empty($attr['format'])) {
			$format = addslashes($attr['format']);
		}
		
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,"\$_ctx->pings->getTime('".$format."')").'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:PingEntryTitle - O -- Trackback entry title -->
	*/
	public function PingEntryTitle($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->pings->post_title').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:PingFeedID - O -- Trackback feed ID -->
	*/
	public function PingFeedID($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->pings->getFeedID()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:PingID - O -- Trackback ID -->
	*/
	public function PingID($attr)
	{
		return '<?php echo $_ctx->pings->comment_id; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:PingIfFirst - O -- displays value if trackback is the first one -->
	<!ATTLIST tpl:PingIfFirst
	return	CDATA	#IMPLIED	-- value to display in case of success (default: first)
	>
	*/
	public function PingIfFirst($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'first';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if ($_ctx->pings->index() == 0) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:PingIfOdd - O -- displays value if trackback is  at an odd position -->
	<!ATTLIST tpl:PingIfOdd
	return	CDATA	#IMPLIED	-- value to display in case of success (default: odd)
	>
	*/
	public function PingIfOdd($attr)
	{
		$ret = isset($attr['return']) ? $attr['return'] : 'odd';
		$ret = html::escapeHTML($ret);
		
		return
		'<?php if (($_ctx->pings->index()+1)%2) { '.
		"echo '".addslashes($ret)."'; } ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:PingIP - O -- Trackback author IP -->
	*/
	public function PingIP($attr)
	{
		return '<?php echo $_ctx->pings->comment_ip; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:PingNoFollow - O -- displays 'rel="nofollow"' if set in blog -->
	*/
	public function PingNoFollow($attr)
	{
		return
		'<?php if($core->blog->settings->comments_nofollow) { '.
		'echo \' rel="nofollow"\';'.
		'} ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:PingOrderNumber - O -- Trackback order in page -->
	*/
	public function PingOrderNumber($attr)
	{
		return '<?php echo $_ctx->pings->index()+1; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:PingPostURL - O -- Trackback Entry URL -->
	*/
	public function PingPostURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->pings->getPostURL()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:Pings - - -- Trackbacks container -->
	<!ATTLIST tpl:Pings
	with_pings	(0|1)	#IMPLIED	-- include trackbacks in request
	lastn	CDATA		#IMPLIED	-- restrict the number of entries 
	no_context (1|0)		#IMPLIED  -- Override context information
	order	(desc|asc)	#IMPLIED	-- result ordering (default: asc)
	>
	*/
	public function Pings($attr,$content)
	{
		$p =
		"if (\$_ctx->posts !== null) { ".
			"\$params['post_id'] = \$_ctx->posts->post_id; ".
			"\$core->blog->withoutPassword(false);\n".
		"}\n";
		
		$p .= "\$params['comment_trackback'] = true;\n";
		
		$lastn = 0;
		if (isset($attr['lastn'])) {
			$lastn = abs((integer) $attr['lastn'])+0;
		}
		
		if ($lastn > 0) {
			$p .= "\$params['limit'] = ".$lastn.";\n";
		} else {
			$p .= "if (\$_ctx->nb_comment_per_page !== null) { \$params['limit'] = \$_ctx->nb_comment_per_page; }\n";
		}
		
		if (empty($attr['no_context']))
		{
			$p .=
			'if ($_ctx->exists("categories")) { '.
				"\$params['cat_id'] = \$_ctx->categories->cat_id; ".
			"}\n";
			
			$p .=
			'if ($_ctx->exists("langs")) { '.
				"\$params['sql'] = \"AND P.post_lang = '\".\$core->blog->con->escape(\$_ctx->langs->post_lang).\"' \"; ".
			"}\n";
		}
		
		$order = 'asc';
		if (isset($attr['order']) && preg_match('/^(desc|asc)$/i',$attr['order'])) {
			$order = $attr['order'];
		}
		
		$p .= "\$params['order'] = 'comment_dt ".$order."';\n";
		
		if (isset($attr['no_content']) && $attr['no_content']) {
			$p .= "\$params['no_content'] = true;\n";
		}
		
		$res = "<?php\n";
		$res .= $p;
		$res .= '$_ctx->pings = $core->blog->getComments($params); unset($params);'."\n";
		$res .= "if (\$_ctx->posts !== null) { \$core->blog->withoutPassword(true);}\n";
		$res .= "?>\n";
		
		$res .=
		'<?php while ($_ctx->pings->fetch()) : ?>'.$content.'<?php endwhile; $_ctx->pings = null; ?>';
		
		return $res;
	}
	
	/*dtd
	<!ELEMENT tpl:PingsFooter - - -- Last trackbacks result container -->
	*/
	public function PingsFooter($attr,$content)
	{
		return
		"<?php if (\$_ctx->pings->isEnd()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:PingsHeader - - -- First trackbacks result container -->
	*/
	public function PingsHeader($attr,$content)
	{
		return
		"<?php if (\$_ctx->pings->isStart()) : ?>".
		$content.
		"<?php endif; ?>";
	}
	
	/*dtd
	<!ELEMENT tpl:PingTitle - O -- Trackback title -->
	*/
	public function PingTitle($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->pings->getTrackbackTitle()').'; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:PingAuthorURL - O -- Trackback author URL -->
	*/
	public function PingAuthorURL($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'$_ctx->pings->getAuthorURL()').'; ?>';
	}
	
	# System
	/*dtd
	<!ELEMENT tpl:SysBehavior - O -- Call a given behavior -->
	<!ATTLIST tpl:SysBehavior
	behavior	CDATA	#IMPLIED	-- behavior to call
	>
	*/
	public function SysBehavior($attr,$raw)
	{
		if (!isset($attr['behavior'])) {
			return;
		}
		
		$b = addslashes($attr['behavior']);
		return
		'<?php if ($core->hasBehavior(\''.$b.'\')) { '.
			'$core->callBehavior(\''.$b.'\',$core,$_ctx);'.
		'} ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:SysIf - - -- System settings tester container -->
	<!ATTLIST tpl:SysIf
	categories		(0|1)	#IMPLIED	-- test if categories are set in current context (value : 1) or not (value : 0)
	posts			(0|1)	#IMPLIED	-- test if posts are set in current context (value : 1) or not (value : 0)
	blog_lang			CDATA	#IMPLIED	-- tests if blog language is the one given in parameter
	current_tpl		CDATA	#IMPLIED	-- tests if current template is the one given in paramater
	current_mode		CDATA	#IMPLIED	-- tests if current URL mode is the one given in parameter
	has_tpl			CDATA     #IMPLIED  -- tests if a named template exists
	has_tag			CDATA     #IMPLIED  -- tests if a named template tag exists
	comments_active	(0|1)	#IMPLIED	-- test if comments are enabled blog-wide 
	pings_active		(0|1)	#IMPLIED	-- test if trackbacks are enabled blog-wide 
	wiki_comments		(0|1)	#IMPLIED	-- test if wiki syntax is enabled for comments
	operator			(and|or)	#IMPLIED	-- combination of conditions, if more than 1 specifiec (default: and)
	>
	*/
	public function SysIf($attr,$content)
	{
		$if = array();
		$is_ping = null;
		
		$operator = isset($attr['operator']) ? $this->getOperator($attr['operator']) : '&&';
		
		if (isset($attr['categories'])) {
			$sign = (boolean) $attr['categories'] ? '!' : '=';
			$if[] = '$_ctx->categories '.$sign.'== null';
		}
		
		if (isset($attr['posts'])) {
			$sign = (boolean) $attr['posts'] ? '!' : '=';
			$if[] = '$_ctx->posts '.$sign.'== null';
		}
		
		if (isset($attr['blog_lang'])) {
			$if[] = "\$core->blog->settings->lang == '".addslashes($attr['blog_lang'])."'";
		}
		
		if (isset($attr['current_tpl'])) {
			$sign = '=';
			if (substr($attr['current_tpl'],0,1) == '!') {
				$sign = '!';
				$attr['current_tpl'] = substr($attr['current_tpl'],1);
			}
			$if[] = "\$_ctx->current_tpl ".$sign."= '".addslashes($attr['current_tpl'])."'";
		}
		
		if (isset($attr['current_mode'])) {
			$sign = '=';
			if (substr($attr['current_mode'],0,1) == '!') {
				$sign = '!';
				$attr['current_mode'] = substr($attr['current_mode'],1);
			}
			$if[] = "\$core->url->type ".$sign."= '".addslashes($attr['current_mode'])."'";
		}
		
		if (isset($attr['has_tpl'])) {
			$sign = '';
			if (substr($attr['has_tpl'],0,1) == '!') {
				$sign = '!';
				$attr['has_tpl'] = substr($attr['has_tpl'],1);
			}
			$if[] = $sign."\$core->tpl->getFilePath('".addslashes($attr['has_tpl'])."') !== false";
		}
		
		if (isset($attr['has_tag'])) {
			$sign = '';
			if (substr($attr['has_tag'],0,1) == '!') {
				$sign = '!';
				$attr['has_tag'] = substr($attr['has_tag'],1);
			}
			$if[] =  $sign."(\$core->tpl->tagExists('".addslashes($attr['has_tag'])."') )";
		}
		
		if (isset($attr['comments_active'])) {
			$sign = (boolean) $attr['comments_active'] ? '' : '!';
			$if[] = $sign.'$core->blog->settings->allow_comments';
		}
		
		if (isset($attr['pings_active'])) {
			$sign = (boolean) $attr['pings_active'] ? '' : '!';
			$if[] = $sign.'$core->blog->settings->allow_trackbacks';
		}

		if (isset($attr['wiki_comments'])) {
			$sign = (boolean) $attr['wiki_comments'] ? '' : '!';
			$if[] = $sign.'$core->blog->settings->wiki_comments';
		}
		
		if (isset($attr['search_count']) &&
			preg_match('/^((=|!|&gt;|&lt;)=|(&gt;|&lt;))\s*[0-9]+$/',trim($attr['search_count']))) {
			$if[] = '(isset($_search_count) && $_search_count '.html::decodeEntities($attr['search_count']).')';
		}
		
		if (!empty($if)) {
			return '<?php if('.implode(' '.$operator.' ',$if).') : ?>'.$content.'<?php endif; ?>';
		} else {
			return $content;
		}
	}
	
	/*dtd
	<!ELEMENT tpl:SysIfCommentPublished - - -- Container displayed if comment has been published -->
	*/
	public function SysIfCommentPublished($attr,$content)
	{
		return
		'<?php if (!empty($_GET[\'pub\'])) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:SysIfCommentPending - - -- Container displayed if comment is pending after submission -->
	*/
	public function SysIfCommentPending($attr,$content)
	{
		return
		'<?php if (isset($_GET[\'pub\']) && $_GET[\'pub\'] == 0) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:SysFeedSubtitle - O -- Feed subtitle -->
	*/
	public function SysFeedSubtitle($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php if ($_ctx->feed_subtitle !== null) { echo '.sprintf($f,'$_ctx->feed_subtitle').';} ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:SysIfFormError - O -- Container displayed if an error has been detected after form submission -->
	*/
	public function SysIfFormError($attr,$content)
	{
		return
		'<?php if ($_ctx->form_error !== null) : ?>'.
		$content.
		'<?php endif; ?>';
	}
	
	/*dtd
	<!ELEMENT tpl:SysIfFormError - O -- Form error -->
	*/
	public function SysFormError($attr)
	{
		return
		'<?php if ($_ctx->form_error !== null) { echo $_ctx->form_error; } ?>';
	}
	
	public function SysPoweredBy($attr)
	{
		return
		'<?php printf(__("Powered by %s"),"<a href=\"http://dotclear.org/\">Dotclear</a>"); ?>';
	}
	
	public function SysSearchString($attr)
	{
		$s = isset($attr['string']) ? $attr['string'] : '%1$s';
		
		$f = $this->getFilters($attr);
		return '<?php if (isset($_search)) { echo sprintf(__(\''.$s.'\'),'.sprintf($f,'$_search').',$_search_count);} ?>';
	}
	
	public function SysSelfURI($attr)
	{
		$f = $this->getFilters($attr);
		return '<?php echo '.sprintf($f,'http::getSelfURI()').'; ?>';
	}
}
?>
