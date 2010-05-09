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

class backupFile
{
	protected $fp;
	private $line_cols = array();
	private $line_name;
	
	private $replacement = array(
		'\n' => "\n",
		'\r' => "\r",
		'\"' => '"'
	);
	
	public function __construct($file)
	{
		if (file_exists($file) && is_readable($file)) {
			$this->fp = fopen($file,'rb');
		} else {
			throw new Exception(__('No file to read.'));
		}
	}
	
	public function __destruct()
	{
		if ($this->fp) {
			fclose($this->fp);
		}
	}
	
	public function getLine()
	{
		if (feof($this->fp)) {
			return false;
		}
		
		$line = trim(fgets($this->fp));
		
		if (substr($line,0,1) == '[')
		{
			$this->line_name = substr($line,1,strpos($line,' ')-1);
			
			$line = substr($line,strpos($line,' ')+1,-1);
			$this->line_cols = explode(',',$line);
			
			return $this->getLine();
		}
		elseif (substr($line,0,1) == '"')
		{
			$line = preg_replace('/^"|"$/','',$line);
			$line = preg_split('/(^"|","|"$)/m',$line);
			
			if (count($this->line_cols) != count($line)) {
				throw new Exception('Invalid row count');
			}
			
			$res = array();
			
			for ($i=0; $i<count($line); $i++) {
				$res[$this->line_cols[$i]] =
				str_replace(array_keys($this->replacement),array_values($this->replacement),$line[$i]);
			}
			
			return new backupFileItem($this->line_name,$res);
		}
		else
		{
			return $this->getLine();
		}
	}
}

class backupFileItem
{
	public $__name;
	private $__data = array();
	
	public function __construct($name,$data)
	{
		$this->__name = $name;
		$this->__data = $data;
	}
	
	public function f($name)
	{
		return iconv('UTF-8','UTF-8//IGNORE',$this->__data[$name]);
	}
	
	public function __get($name)
	{
		return $this->f($name);
	}
	
	public function __set($n,$v)
	{
		$this->__data[$n] = $v;
	}
	
	public function exists($n)
	{
		return isset($this->__data[$n]);
	}
	
	public function drop()
	{
		foreach (func_get_args() as $n) {
			if (isset($this->__data[$n])) {
				unset($this->__data[$n]);
			}
		}
	}
	
	public function substitute($old,$new)
	{
		if (isset($this->__data[$old])) {
			$this->__data[$new] = $this->__data[$old];
			unset($this->__data[$old]);
		}
	}
}
?>