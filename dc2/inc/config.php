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

// Database driver (mysql, pgsql, sqlite)
define('DC_DBDRIVER','mysql');

// Database hostname (usually "localhost")
define('DC_DBHOST','db1904.1and1.fr');

// Database user
define('DC_DBUSER','dbo285852732');

// Database password
define('DC_DBPASSWORD','9eUGArhY');

// Database name
define('DC_DBNAME','db285852732');

// Tables' prefix
define('DC_DBPREFIX','jes_');

// Persistent database connection
define('DC_DBPERSIST',false);

// Crypt key (password storage)
define('DC_MASTER_KEY','5b05d5e1487772575da37ecd0159c5e7');


// Admin URL. You need to set it for some features.
define('DC_ADMIN_URL','http://www.le-papillon.org/jes/dc2/admin/');

// Cookie's name
define('DC_SESSION_NAME','dcxd');

// Plugins root
define('DC_PLUGINS_ROOT',dirname(__FILE__).'/../plugins');

// Template cache directory
define('DC_TPL_CACHE',dirname(__FILE__).'/../cache');


// If you have PATH_INFO issue, uncomment following lines
//if (!isset($_SERVER['ORIG_PATH_INFO'])) {
//	$_SERVER['ORIG_PATH_INFO'] = '';
//}
//$_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];


// If you have mail problems, uncomment following lines and adapt it to your hosting configuration
// For more information about this setting, please refer to http://doc.dotclear.net/2.0/admin/install/custom-sendmail
//function _mail($to,$subject,$message,$headers)
//{
//	socketMail::$smtp_relay = 'my.smtp.relay.org';
//	socketMail::mail($to,$subject,$message,$headers);
//}

?>